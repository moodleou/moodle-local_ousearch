<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Class that deals with the per-year tables.
 *
 * @package local_ousearch
 * @copyright 2015 The Open University
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_ousearch;

/**
 * Class that deals with the per-year tables.
 */
abstract class year_tables {
    /** @var string Name of config setting for multi-table mode enabled or not */
    const CONFIG_ENABLED = 'yearsenabled';
    /** @var string Name of config setting for course id currently transferring */
    const CONFIG_TRANSFERRING_COURSE = 'transferringcourse';
    /** @var string Name of config setting for date done within current course */
    const CONFIG_TRANSFERRING_DONEUPTO = 'transferringdoneupto';
    /** @var string Name of config setting for time limit to split data per cron */
    const CONFIG_SPLIT_TIME_LIMIT = 'splittimelimit';
    /** @var string Name of config setting for time limit to re-date data per cron */
    const CONFIG_DATE_TIME_LIMIT = 'datetimelimit';
    /** @var string Constant for enabled setting: off (single table) */
    const ENABLED_OFF = 'off';
    /** @var string Constant for enabled setting: transferring in progress */
    const ENABLED_TRANSFERRING = 'transferring';
    /** @var string Constant for enabled setting: on (year tables) */
    const ENABLED_ON = 'on';

    /** @var int Number of documents to move in one chunk when splitting tables */
    const SPLIT_TABLES_CHUNK_SIZE = 1000;

    /** @var int Number of documents to move in one chunk when changing dates */
    const CHANGE_DATES_CHUNK_SIZE = 1000;

    /** @var int Year table to use for non-course search data. */
    const NON_COURSE_YEAR = 2011;

    /** @var int Minimum year table */
    const MIN_YEAR = 2011;

    /** @var int Maximum year table */
    const MAX_YEAR = 2020;

    /**
     * Static function works out which year tables to use for the specified
     * course, taking into account the various admin settings.
     *
     * If you specify null for the course we return the table used for searchable
     * data that isn't associated with a course.
     *
     * Prints a debugging message if it's getting dangerously close to the last
     * available year, and throws exception if it's after that.
     *
     * @param stdClass $course Moodle course object containing at least id, startdate
     * @return int|bool Year number or false if none
     * @throws MoodleException If after the last supported year
     */
    public static function get_year_for_tables($course = null) {
        $enabled = get_config('local_ousearch', self::CONFIG_ENABLED);
        if (!$enabled || $enabled === self::ENABLED_OFF) {
            return false;
        }
        if ($enabled === self::ENABLED_TRANSFERRING) {
            $transferringid = get_config('local_ousearch',
                    self::CONFIG_TRANSFERRING_COURSE);
            // Non-course stuff is transferred first so if no course specified,
            // it is OK if there is any id set.
            if (is_null($course)) {
                if ($transferringid) {
                    return self::NON_COURSE_YEAR;
                } else {
                    return false;
                }
            }
            if ($course->id >= $transferringid) {
                return false;
            }
        }
        if (is_null($course)) {
            return self::NON_COURSE_YEAR;
        }

        // We get the current year from the course start date.
        return self::get_year_for_course($course);
    }

    /**
     * Get year for course start date. This always returns the year even if
     * the system is currently off.
     *
     * @param stdClass $course Course object (only needs ->startdate)
     * @throws \moodle_exception If year is out of bounds
     * @return int Year
     */
    public static function get_year_for_course($course) {
        $year = (int)date('Y', $course->startdate);
        if ($year < self::MIN_YEAR) {
            // Any year before 2011 goes in the 2011 table.
            return self::MIN_YEAR;
        }
        if ($year >= self::MAX_YEAR) {
            if ($year > self::MAX_YEAR) {
                // When the course is past the max table, throw an exception.
                throw new \moodle_exception('error_futureyear', 'local_ousearch');
            } else {
                // On the last table, show a debugging message.
                debugging(get_string('warning_lastyear', 'local_ousearch'));
            }
        }
        return $year;
    }

    /**
     * Called whenever a new course is added. If the system is enabled, records
     * year data in the table.
     *
     * @param int $courseid Course id
     */
    public static function handle_new_course($courseid) {
        global $DB;

        // Check current state.
        $enabled = get_config('local_ousearch', self::CONFIG_ENABLED);
        if (!$enabled || $enabled === self::ENABLED_OFF ||
                $enabled === self::ENABLED_TRANSFERRING) {
            // Do nothing if the system is turned off, or if it's still
            // transferring. When transferring, courses are added to this table
            // only after they are transferred. Because new courses have larger
            // IDs, it will be added later for sure.
            return;
        }

        // OK, this is actually a new course, so record its year in the table.
        $course = get_course($courseid);
        $year = self::get_year_for_tables($course);
        $DB->insert_record('local_ousearch_courseyears', array(
                'courseid' => $courseid, 'year' => $year));
    }

    /**
     * Called whenever a course is updated. Checks if it changed year and, if
     * so, updates the course years table. (This will cause cron to start moving
     * its data.)
     *
     * @param int $courseid Course id
     */
    public static function handle_updated_course($courseid) {
        global $DB;

        // Get course and corresponding year. Note: You cannot use the get_course
        // function here, because when this is called as a result of an event
        // after saving changes to the course settings, old course data is
        // present in the cache.
        $course = $DB->get_record('course', array('id' => $courseid));
        $year = self::get_year_for_tables($course);

        // When the system is disabled or this course hasn't been transferred
        // yet, don't do anything else.
        if (!$year) {
            return;
        }

        // Get existing data, which now should exist.
        $current = $DB->get_record('local_ousearch_courseyears',
                array('courseid' => $courseid), '*', MUST_EXIST);
        if ((int)$current->year === $year) {
            // No change in year so do nothing.
            return;
        }

        // There is a change in year. We need to decide the old years to copy.
        // First, the new year should not be included in the old years list.
        $oldyears = ',' . $current->oldyears . ',';
        $oldyears = str_replace(',' . $year . ',', ',', $oldyears);

        // Next, add the actual old year if it isn't there already.
        if (strpos($oldyears, ',' . $current->year . ',') === false) {
            $oldyears .= $current->year;
        }
        $oldyears = trim($oldyears, ',');

        // Update the table.
        $DB->update_record('local_ousearch_courseyears', array('id' => $current->id,
                'year' => $year, 'oldyears' => $oldyears));
    }

    /**
     * Called by cron task to split old data into per-year tables.
     */
    public static function task_split_tables() {
        // Only do anything if transferring data.
        $enabled = get_config('local_ousearch', self::CONFIG_ENABLED);
        if ($enabled != self::ENABLED_TRANSFERRING) {
            return;
        }

        mtrace('Splitting search data into new tables:');

        // Get the time limit.
        $endafter = time() + get_config('local_ousearch',
                self::CONFIG_SPLIT_TIME_LIMIT);
        while (time() < $endafter) {
            if (self::split_tables_chunk()) {
                break;
            }
        }
    }

    /**
     * Does a chunk of work toward splitting the data into tables.
     *
     * @param bool $output If true, calls mtrace to display output
     * @param array $results Output array (number of documents moved per course)
     * @param int $chunksize Number of items to include in chunk (roughly)
     * @return bool True to stop processing for this cron, false to continue
     * @throws coding_exception If not currently doing the transfer
     */
    public static function split_tables_chunk(
            $output = true, $chunksize = self::SPLIT_TABLES_CHUNK_SIZE) {
        global $DB, $CFG;
        require_once($CFG->dirroot . '/local/ousearch/searchlib.php');

        $enabled = get_config('local_ousearch', self::CONFIG_ENABLED);
        if ($enabled != self::ENABLED_TRANSFERRING) {
            throw new \coding_exception('Cannot call this except during transfer');
        }

        // Get course being transferred and set up initial data.
        $transferringid = get_config('local_ousearch', self::CONFIG_TRANSFERRING_COURSE);
        $params = array();
        if (!$transferringid) {
            // Do non-course stuff first.
            $where = 'courseid IS NULL';
            $course = null;
            $out = '  [Non-course]';
            $targetyear = self::NON_COURSE_YEAR;
        } else {
            $where = 'courseid = ?';
            $params[] = $transferringid;
            $course = get_course($transferringid);
            $out = '  ' . $course->shortname;
            $targetyear = self::get_year_for_course($course);
        }

        // Work out table names.
        $targetdocs = 'local_ousearch_docs_' . $targetyear;
        $targetoccurs = 'local_ousearch_occurs_' . $targetyear;

        // Only get the records we haven't done yet.
        $doneupto = get_config('local_ousearch',
                self::CONFIG_TRANSFERRING_DONEUPTO);
        if ($doneupto) {
            $where .= ' AND timemodified > ?';
            $params[] = $doneupto;
        }

        // Find all records that were added or modified since the last processed
        // time. Note we do not need to limit these results because it's a
        // recordset.
        $before = microtime(true);
        $transaction = $DB->start_delegated_transaction();
        $rs = $DB->get_recordset_select('local_ousearch_documents', $where, $params,
                'timemodified', '*');
        $out .= ' Select: ' . round(microtime(true) - $before, 1) . 's.';
        $count = 0;
        $lasttime = 0;
        $complete = true;
        $updates = 0;
        $creates = 0;
        $before = microtime(true);
        foreach ($rs as $document) {
            // If we have already processed the requested amount, stop processing
            // as soon as the last time changes. (The reason to wait until then
            // is that we can record that we've done everything up until that
            // time, without missing anything.)
            if ($count >= $chunksize) {
                if ($document->timemodified != $lasttime) {
                    $complete = false;
                    break;
                }
            }

            // See if this document already exists. If so, delete and remake.
            $docobject = new \local_ousearch_document();
            $docobject->init_from_record($document);
            unset($docobject->id);
            if ($docobject->find($targetdocs)) {
                $DB->delete_records($targetoccurs, array('documentid' => $docobject->id));
                $DB->delete_records($targetdocs, array('id' => $docobject->id));
                $updates++;
            } else {
                $creates++;
            }

            // Insert the document into the target table.
            $oldid = $document->id;
            unset($document->id);
            $newid = $DB->insert_record($targetdocs, $document);

            // Copy all the SQL occurrences from source table.
            $DB->execute('INSERT INTO {' . $targetoccurs . '} (wordid, documentid, score)
                SELECT wordid, ?, score FROM {local_ousearch_occurrences} WHERE documentid = ?',
                array($newid, $oldid));

            $lasttime = $document->timemodified;
            $count++;
        }
        $rs->close();

        $out .= ' Copy: ' . $creates . ' creates, ' . $updates . ' updates, ' .
                round(microtime(true) - $before, 1) . 's.';

        if ($complete) {
            // After completing a course, add it to the course years list.
            if ($course) {
                $DB->insert_record('local_ousearch_courseyears', array(
                        'courseid' => $course->id, 'year' => $targetyear));
            }

            // Look for the next course.
            $nextparams = array();
            if (!$transferringid) {
                $where = '';
            } else {
                $where = 'WHERE id > ?';
                $nextparams[] = $transferringid;
            }
            $nextcourseid = $DB->get_field_sql("SELECT MIN(id) FROM {course} $where", $nextparams, IGNORE_MISSING);
            if ($nextcourseid) {
                // Move to next course.
                unset_config(self::CONFIG_TRANSFERRING_DONEUPTO, 'local_ousearch');
                set_config(self::CONFIG_TRANSFERRING_COURSE, $nextcourseid, 'local_ousearch');
                $transaction->allow_commit();
                $out .= ' Complete, moving to next course.';
                if ($output) {
                    mtrace($out);
                }
                return false;
            } else {
                // Finished all courses!
                unset_config(self::CONFIG_TRANSFERRING_DONEUPTO, 'local_ousearch');
                unset_config(self::CONFIG_TRANSFERRING_COURSE, 'local_ousearch');
                set_config(self::CONFIG_ENABLED, self::ENABLED_ON, 'local_ousearch');
                $transaction->allow_commit();

                // Delete all records from old tables. Doing this outside the
                // transaction allows it to use TRUNCATE to speed this up.
                $before = microtime(true);
                $DB->delete_records('local_ousearch_documents');
                $DB->delete_records('local_ousearch_occurrences');
                $out .= ' All courses complete, tables deleted: ' . round(microtime(true) - $before, 1) . 's.';
                if ($output) {
                    mtrace($out);
                }
                return true;
            }
        } else {
            // If not complete, update the time processed up to.
            set_config(self::CONFIG_TRANSFERRING_DONEUPTO, $lasttime, 'local_ousearch');
            $transaction->allow_commit();
            $out .= ' Continuing...';
            if ($output) {
                mtrace($out);
            }
            return false;
        }
    }

    /**
     * Called by cron task to check if any data needs to be moved due to changed
     * dates. (Note: During this period, search results will not be reliable.
     * Staff should avoid changing the date for a website to a different year
     * after it has gone live.)
     */
    public static function task_change_dates() {
        global $DB;

        // Only do anything if system is on.
        $enabled = get_config('local_ousearch', self::CONFIG_ENABLED);
        if ($enabled == self::ENABLED_OFF) {
            return;
        }

        if (!$DB->record_exists_select('local_ousearch_courseyears',
                'oldyears != ?', array(''))) {
            mtrace ('No courses changed year recently.');
            return;
        }

        mtrace('Moving search data for courses that changed year:');

        // Get the time limit.
        $endafter = time() + get_config('local_ousearch',
                self::CONFIG_DATE_TIME_LIMIT);
        while (time() < $endafter) {
            if (self::change_dates_chunk()) {
                break;
            }
        }
    }

    /**
     * Moves a chunk of data from one year to another.
     *
     * @param bool $output If true, calls mtrace to display progress
     * @param int $chunksize If set, uses specific chunk size instead of default
     * @return boolean True to stop processing, false if there is more to do
     */
    public static function change_dates_chunk(
            $output = true, $chunksize = self::CHANGE_DATES_CHUNK_SIZE) {
        global $DB;

        // Select the first course (by courseid) that needs updating.
        $changed = $DB->get_records_select('local_ousearch_courseyears',
                'oldyears != ?', array(''), 'courseid', '*', 0, 1);
        $changedrec = reset($changed);

        // Get course data.
        $course = get_course($changedrec->courseid);
        $newyear = self::get_year_for_tables($course);
        $targetdocs = self::get_docs_table($newyear);
        $targetoccurs = self::get_occurs_table($newyear);
        $oldyear = preg_replace('~,.*$~', '', $changedrec->oldyears);
        $sourcedocs = self::get_docs_table($oldyear);
        $sourceoccurs = self::get_occurs_table($oldyear);
        $out = '  ' . $course->shortname . ' (' . $oldyear . ' -> ' . $newyear . ') ';

        // Move chunk of documents.
        $before = microtime(true);
        $transaction = $DB->start_delegated_transaction();
        $rs = $DB->get_recordset_select($sourcedocs, 'courseid = ?', array($course->id), 'id');
        $out .= ' Select: ' . round(microtime(true) - $before, 1) . 's.';
        $count = 0;
        $complete = true;
        $before = microtime(true);
        foreach ($rs as $document) {
            // Stop if we finished.
            if ($count >= $chunksize) {
                $complete = false;
                break;
            }

            // Insert the document into the target table.
            $oldid = $document->id;
            unset($document->id);
            $newid = $DB->insert_record($targetdocs, $document);

            // Copy all the SQL occurrences from source table.
            $DB->execute('INSERT INTO {' . $targetoccurs . '} (wordid, documentid, score)
                SELECT wordid, ?, score FROM {' . $sourceoccurs . '} WHERE documentid = ?',
                array($newid, $oldid));

            // Delete the occurrences and document from source table.
            $DB->delete_records($sourceoccurs, array('documentid' => $oldid));
            $DB->delete_records($sourcedocs, array('id' => $oldid));

            // Stop processing if we've already done the chunk.
            $count++;
        }
        $rs->close();

        $out .= ' Move ' . $count . ' documents: ' .
                round(microtime(true) - $before, 1) . 's.';

        // If complete, update the course years table.
        $stop = false;
        if ($complete) {
            $newoldyears = trim(str_replace(',' . $oldyear . ',', ',',
                    ',' . $changedrec->oldyears . ','), ',');
            $DB->set_field('local_ousearch_courseyears', 'oldyears', $newoldyears,
                    array('id' => $changedrec->id));
            $out .= ' Complete.';

            // If there aren't any entries in the table any more then return stop.
            if (!$DB->record_exists_select('local_ousearch_courseyears',
                    'oldyears != ?', array(''))) {
                $stop = true;
            }
        } else {
            $out .= ' More to do.';
        }

        if ($output) {
            mtrace($out);
        }

        $transaction->allow_commit();

        return $stop;
    }

    /**
     * Checks if we are currently transferring data for the given course.
     *
     * @param int $courseid Course id
     * @return bool True if currently transferring data from this course
     */
    public static function currently_transferring_course($courseid) {
        return get_config('local_ousearch', self::CONFIG_TRANSFERRING_COURSE) == $courseid;
    }

    /**
     * Gets the name of the docs table for given year.
     *
     * @param int|bool $year Year number or false for default table
     * @return string Table name
     */
    public static function get_docs_table($year) {
        if ($year) {
            return 'local_ousearch_docs_' . $year;
        } else {
            return 'local_ousearch_documents';
        }
    }

    /**
     * Gets the name of the occurrences table for given year.
     *
     * @param int|bool $year Year number or false for default table
     * @return string Table name
     */
    public static function get_occurs_table($year) {
        if ($year) {
            return 'local_ousearch_occurs_' . $year;
        } else {
            return 'local_ousearch_occurrences';
        }
    }

    /**
     * Checks if the year tables system is enabled, either partially (turned
     * on but in process of transferring) or fully (transfer completed).
     *
     * @return boolean True if enabled
     */
    public static function is_partially_or_fully_enabled() {
        $enabled = get_config('local_ousearch', self::CONFIG_ENABLED);
        return $enabled != self::ENABLED_OFF;
    }
}

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
 * Unit tests for the 'per-year tables' feature.
 *
 * @package local_ousearch
 * @copyright 2015 The Open University
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use \local_ousearch\year_tables;

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot . '/local/ousearch/searchlib.php');

class local_ousearch_year_tables_test extends advanced_testcase {
    /** @var array Array of document data used in the test */
    public static $testdocuments;

    /**
     * Tests the get_year_for_tables function.
     */
    public function test_get_year_for_tables() {
        $this->resetAfterTest();

        // Create a couple of courses for testing.
        $course1 = $this->getDataGenerator()->create_course(array(
                'startdate' => strtotime('2013-01-04 10:00')));
        $course2 = $this->getDataGenerator()->create_course(array(
                'startdate' => strtotime('2020-01-04 10:00')));
        $course3 = $this->getDataGenerator()->create_course(array(
                'startdate' => strtotime('2021-01-04 10:00')));

        // System not turned on.
        $this->assertFalse(year_tables::get_year_for_tables());

        // System turned on, initially transferring. Test with course and no course.
        set_config(year_tables::CONFIG_ENABLED, year_tables::ENABLED_TRANSFERRING, 'local_ousearch');
        $this->assertFalse(year_tables::get_year_for_tables());
        $this->assertFalse(year_tables::get_year_for_tables($course1));

        // If any course is transferring, the non-course ones will return default.
        set_config(year_tables::CONFIG_TRANSFERRING_COURSE, $course1->id, 'local_ousearch');
        $this->assertEquals(year_tables::NON_COURSE_YEAR, year_tables::get_year_for_tables());
        $this->assertFalse(year_tables::get_year_for_tables($course1));

        // Once course 1 is finished, course 2 will still return false and course 1
        // should return its year.
        set_config(year_tables::CONFIG_TRANSFERRING_COURSE, $course2->id, 'local_ousearch');
        $this->assertEquals(2013, year_tables::get_year_for_tables($course1));
        $this->assertFalse(year_tables::get_year_for_tables($course2));

        // Now we'll set it to mark that everything was transferred.
        set_config(year_tables::CONFIG_ENABLED, year_tables::ENABLED_ON, 'local_ousearch');
        unset_config(year_tables::CONFIG_TRANSFERRING_COURSE, 'local_ousearch');

        // All courses 2020 onwards should return 2020.
        $this->assertEquals(2020, year_tables::get_year_for_tables($course2));
        $this->assertEquals(2020, year_tables::get_year_for_tables($course3));
    }

    /**
     * Tests the handle_new_course function.
     */
    public function test_handle_new_course() {
        global $DB;
        $this->resetAfterTest();

        $course = $this->getDataGenerator()->create_course(array(
                'startdate' => strtotime('2013-01-04 10:00')));

        // When the system is turned off, it doesn't do anything.
        year_tables::handle_new_course($course->id);
        $this->assertEquals(0, $DB->count_records('local_ousearch_courseyears'));

        // When transferring it still doesn't do anything.
        set_config(year_tables::CONFIG_ENABLED, year_tables::ENABLED_TRANSFERRING, 'local_ousearch');
        year_tables::handle_new_course($course->id);
        $this->assertEquals(0, $DB->count_records('local_ousearch_courseyears'));

        // When actually on it will do something.
        set_config(year_tables::CONFIG_ENABLED, year_tables::ENABLED_ON, 'local_ousearch');
        year_tables::handle_new_course($course->id);
        $records = $DB->get_records('local_ousearch_courseyears');
        $this->assertCount(1, $records);
        $records = array_values($records);
        $record = $records[0];

        // Check the added row is correct.
        $this->assertEquals($course->id, $record->courseid);
        $this->assertEquals(2013, $record->year);
        $this->assertEquals('', $record->oldyears);
    }

    /**
     * Tests the handle_updated_course function.
     */
    public function test_handle_updated_course() {
        global $DB;
        $this->resetAfterTest();

        // Create a couple of courses.
        $course1 = $this->getDataGenerator()->create_course(array(
                'startdate' => strtotime('2013-01-04 10:00')));
        $course2 = $this->getDataGenerator()->create_course(array(
                'startdate' => strtotime('2015-01-04 10:00')));

        // Don't do anything when the system's off.
        year_tables::handle_updated_course($course1->id);

        // Or when it's transferring but hasn't done that course.
        set_config(year_tables::CONFIG_ENABLED, year_tables::ENABLED_TRANSFERRING, 'local_ousearch');
        set_config(year_tables::CONFIG_TRANSFERRING_COURSE, $course2->id, 'local_ousearch');
        year_tables::handle_updated_course($course2->id);

        // When it's on, it requires for local_ousearch_courseyears to be set up.
        try {
            year_tables::handle_updated_course($course1->id);
            $this->fail();
        } catch (moodle_exception $e) {
            $this->assertStringContainsString('table local_ousearch_courseyears', $e->getMessage());
        }

        // Initialise both courses.
        set_config(year_tables::CONFIG_ENABLED, year_tables::ENABLED_ON, 'local_ousearch');
        unset_config(year_tables::CONFIG_TRANSFERRING_COURSE, 'local_ousearch');
        year_tables::handle_new_course($course1->id);
        year_tables::handle_new_course($course2->id);

        // Get the data by course id.
        $records = $DB->get_records('local_ousearch_courseyears', null, '',
                'courseid, year, oldyears');

        // Check initial values (not really part of this test).
        $this->assertEquals(2013, $records[$course1->id]->year);
        $this->assertEquals('', $records[$course1->id]->oldyears);
        $this->assertEquals(2015, $records[$course2->id]->year);
        $this->assertEquals('', $records[$course2->id]->oldyears);

        // Do update without changing the courses and check nothing changes.
        year_tables::handle_updated_course($course1->id);
        year_tables::handle_updated_course($course2->id);
        $newrecords = $DB->get_records('local_ousearch_courseyears', null, '',
                'courseid, year, oldyears');
        $this->assertEquals($records, $newrecords);

        // Change the first course to 2017.
        $DB->set_field('course', 'startdate', strtotime('2017-01-04 00:00'),
                array('id' => $course1->id));
        year_tables::handle_updated_course($course1->id);
        $record1 = $DB->get_record('local_ousearch_courseyears',
                array('courseid' => $course1->id));
        $this->assertEquals(2017, $record1->year);
        $this->assertEquals('2013', $record1->oldyears);

        // Change it again to 2016.
        $DB->set_field('course', 'startdate', strtotime('2016-01-04 00:00'),
                array('id' => $course1->id));
        year_tables::handle_updated_course($course1->id);
        $record1 = $DB->get_record('local_ousearch_courseyears',
                array('courseid' => $course1->id));
        $this->assertEquals(2016, $record1->year);
        $this->assertEquals('2013,2017', $record1->oldyears);

        // Now change it back to 2017 (note that this removes 2017 from old years).
        $DB->set_field('course', 'startdate', strtotime('2017-01-04 00:00'),
                array('id' => $course1->id));
        year_tables::handle_updated_course($course1->id);
        $record1 = $DB->get_record('local_ousearch_courseyears',
                array('courseid' => $course1->id));
        $this->assertEquals(2017, $record1->year);
        $this->assertEquals('2013,2016', $record1->oldyears);
    }

    /**
     * Tests the split_tables_chunk function with no actual search data.
     */
    public function test_split_tables_chunk_empty() {
        global $DB;
        $this->resetAfterTest();

        // There are 2 courses, plus site course..
        $course1 = $this->getDataGenerator()->create_course(array(
                'startdate' => strtotime('2013-01-04 10:00')));
        $course2 = $this->getDataGenerator()->create_course(array(
                'startdate' => strtotime('2015-01-04 10:00')));

        // Set config initial value.
        set_config(year_tables::CONFIG_ENABLED, year_tables::ENABLED_TRANSFERRING, 'local_ousearch');

        // First run should do the non-course data.
        $this->assertFalse(year_tables::split_tables_chunk(false));
        $this->assertEquals(SITEID,
                get_config('local_ousearch', year_tables::CONFIG_TRANSFERRING_COURSE));
        $this->assertFalse(
                get_config('local_ousearch', year_tables::CONFIG_TRANSFERRING_DONEUPTO));
        $this->assertEquals(0, $DB->count_records('local_ousearch_courseyears'));

        // Next, site course data.
        $this->assertFalse(year_tables::split_tables_chunk(false));
        $this->assertEquals($course1->id,
                get_config('local_ousearch', year_tables::CONFIG_TRANSFERRING_COURSE));
        $this->assertFalse(
                get_config('local_ousearch', year_tables::CONFIG_TRANSFERRING_DONEUPTO));
        $this->assertEquals(year_tables::MIN_YEAR,
                $DB->get_field('local_ousearch_courseyears', 'year', array('courseid' => SITEID)));

        // Next, first course.
        $this->assertFalse(year_tables::split_tables_chunk(false));
                $this->assertEquals($course2->id,
                get_config('local_ousearch', year_tables::CONFIG_TRANSFERRING_COURSE));
        $this->assertFalse(
                get_config('local_ousearch', year_tables::CONFIG_TRANSFERRING_DONEUPTO));
        $this->assertEquals(2013,
                $DB->get_field('local_ousearch_courseyears', 'year', array('courseid' => $course1->id)));

        // Finally, last course.
        $this->assertTrue(year_tables::split_tables_chunk(false));
        $this->assertEquals(year_tables::ENABLED_ON,
                get_config('local_ousearch', year_tables::CONFIG_ENABLED));
        $this->assertFalse(
                get_config('local_ousearch', year_tables::CONFIG_TRANSFERRING_COURSE));
        $this->assertFalse(
                get_config('local_ousearch', year_tables::CONFIG_TRANSFERRING_DONEUPTO));
        $this->assertEquals(2013,
                $DB->get_field('local_ousearch_courseyears', 'year', array('courseid' => $course1->id)));

        // If we call it again, throws exception because not transferring.
        try {
            year_tables::split_tables_chunk(false);
            $this->fail();
        } catch (coding_exception $e) {
            $this->assertStringContainsString('except during transfer', $e->getMessage());
        }
    }

    /**
     * Tests the split_tables_chunk function with some actual search data.
     */
    public function test_split_tables_chunk_data() {
        global $DB;
        $this->resetAfterTest();

        // Create a test course for 2013.
        $course = $this->getDataGenerator()->create_course(array(
                'startdate' => strtotime('2013-01-04 10:00')));

        // Create 3 search documents in it.
        $doc = new local_ousearch_document();
        $cm = (object)array('id' => 14, 'course' => $course->id);
        $doc->init_module_instance('frog', $cm);
        $doc->update('Document title', 'Document one');

        $doc = new local_ousearch_document();
        $cm = (object)array('id' => 15, 'course' => $course->id);
        $doc->init_module_instance('frog', $cm);
        $doc->update('Document title', 'Document two');

        $doc = new local_ousearch_document();
        $cm = (object)array('id' => 16, 'course' => $course->id);
        $doc->init_module_instance('frog', $cm);
        $doc->update('Document title', 'Document three');

        $this->assertEquals(3, $DB->count_records('local_ousearch_documents'));
        $this->assertEquals(9, $DB->count_records('local_ousearch_occurrences'));

        // Now do the split tables process.
        self::run_split_tables_chunk($course->id, 1000);

        // There should be nothing in the original table.
        $this->assertEquals(0, $DB->count_records('local_ousearch_documents'));
        $this->assertEquals(0, $DB->count_records('local_ousearch_occurrences'));

        // And everything in the 2013 table.
        $this->assertEquals(3, $DB->count_records('local_ousearch_docs_2013'));
        $this->assertEquals(9, $DB->count_records('local_ousearch_occurs_2013'));
    }

    /**
     * Tests the split_tables_chunk function where it only has time to complete
     * some of the data.
     */
    public function test_split_tables_chunk_partial() {
        global $DB;
        $this->resetAfterTest();

        // Create a test course for 2013.
        $course = $this->getDataGenerator()->create_course(array(
                'startdate' => strtotime('2013-01-04 10:00')));

        // Create 3 search documents in it (different times).
        $doc1 = new local_ousearch_document();
        $cm = (object)array('id' => 14, 'course' => $course->id);
        $doc1->init_module_instance('frog', $cm);
        $doc1->update('Document title', 'Document one', 100);

        $doc2 = new local_ousearch_document();
        $cm = (object)array('id' => 15, 'course' => $course->id);
        $doc2->init_module_instance('frog', $cm);
        $doc2->update('Document title', 'Document two', 200);

        $doc3 = new local_ousearch_document();
        $cm = (object)array('id' => 16, 'course' => $course->id);
        $doc3->init_module_instance('frog', $cm);
        $doc3->update('Document title', 'Document three', 300);

        // Now do the split tables process limited to 2 documents.
        $this->assertFalse(self::run_split_tables_chunk($course->id, 2));

        // Original data still contains all info.
        $this->assertEquals(3, $DB->count_records('local_ousearch_documents'));
        $this->assertEquals(9, $DB->count_records('local_ousearch_occurrences'));

        // New tables contain some of it.
        $this->assertEquals(2, $DB->count_records('local_ousearch_docs_2013'));
        $this->assertEquals(6, $DB->count_records('local_ousearch_occurs_2013'));

        // Check progress is recorded correctly.
        $this->assertEquals($course->id,
                get_config('local_ousearch', year_tables::CONFIG_TRANSFERRING_COURSE));
        $this->assertEquals(200,
                get_config('local_ousearch', year_tables::CONFIG_TRANSFERRING_DONEUPTO));

        // Before second update, document 1 is modified.
        $doc1->update('Document title', 'Long document one', 400);
        $this->assertEquals(3, $DB->count_records('local_ousearch_documents'));
        $this->assertEquals(10, $DB->count_records('local_ousearch_occurrences'));

        // Only update one this time (it should be doc 3 because of dates) .
        $this->assertFalse(self::run_split_tables_chunk($course->id, 1));
        $this->assertEquals(3, $DB->count_records('local_ousearch_docs_2013'));
        $this->assertEquals(9, $DB->count_records('local_ousearch_occurs_2013'));

        // Run final update.
        $this->assertTrue(self::run_split_tables_chunk($course->id, 1));
        $this->assertEquals(3, $DB->count_records('local_ousearch_docs_2013'));
        $this->assertEquals(10, $DB->count_records('local_ousearch_occurs_2013'));
    }

    /**
     * Tests the split_tables_chunk function where it only has time to complete
     * some of the data, and some of that data is at same time.
     */
    public function test_split_tables_chunk_timing() {
        global $DB;
        $this->resetAfterTest();

        // Create a test course for 2013.
        $course = $this->getDataGenerator()->create_course(array(
                'startdate' => strtotime('2013-01-04 10:00')));

        // Create 3 search documents in it (first two have same time).
        $doc1 = new local_ousearch_document();
        $cm = (object)array('id' => 14, 'course' => $course->id);
        $doc1->init_module_instance('frog', $cm);
        $doc1->update('Document title', 'Document one', 100);

        $doc2 = new local_ousearch_document();
        $cm = (object)array('id' => 15, 'course' => $course->id);
        $doc2->init_module_instance('frog', $cm);
        $doc2->update('Document title', 'Document two', 100);

        $doc3 = new local_ousearch_document();
        $cm = (object)array('id' => 16, 'course' => $course->id);
        $doc3->init_module_instance('frog', $cm);
        $doc3->update('Document title', 'Document three', 300);

        // Now do the split tables process limited to 1 documents.
        $this->assertFalse(self::run_split_tables_chunk($course->id, 1));

        // It actually converts 2 documents.
        $this->assertEquals(2, $DB->count_records('local_ousearch_docs_2013'));
        $this->assertEquals(6, $DB->count_records('local_ousearch_occurs_2013'));

        // Because it was converting everything up to time 100.
        $this->assertEquals(100,
                get_config('local_ousearch', year_tables::CONFIG_TRANSFERRING_DONEUPTO));
    }

    /**
     * Shortcut to set up the variables and then run it for one course.
     *
     * @param int $courseid Course id to run for
     */
    protected static function run_split_tables_chunk($courseid, $chunksize) {
        set_config(year_tables::CONFIG_ENABLED, year_tables::ENABLED_TRANSFERRING, 'local_ousearch');
        set_config(year_tables::CONFIG_TRANSFERRING_COURSE, $courseid, 'local_ousearch');
        return year_tables::split_tables_chunk(false, $chunksize);
    }

    /**
     * Test the actual search function with upgrade.
     */
    public function test_search_around_upgrade() {
        global $DB;
        $this->resetAfterTest();

        // Create two test courses.
        $course1 = $this->getDataGenerator()->create_course(array(
                'startdate' => strtotime('2013-01-04 10:00')));
        $course2 = $this->getDataGenerator()->create_course(array(
                'startdate' => strtotime('2015-01-04 10:00')));

        // Add two documents to each course.
        self::add_test_document(1, $course1, 'One');
        self::add_test_document(2, $course1, 'Two');
        self::add_test_document(3, $course2, 'Three');
        self::add_test_document(4, $course2, 'Four');

        // Check database is as expected.
        $this->assertEquals(8, $DB->count_records('local_ousearch_occurrences'));

        // Do initial searches on each course..
        $this->check_search($course1, 'two', array(2));
        $this->check_search($course2, 'title', array(3, 4));

        // Turn on the system and transfer main course and site course but
        // nothing else yet.
        set_config(year_tables::CONFIG_ENABLED, year_tables::ENABLED_TRANSFERRING, 'local_ousearch');
        $this->assertFalse(year_tables::split_tables_chunk(false));
        $this->assertFalse(year_tables::split_tables_chunk(false));

        // Still searching okay?
        $this->check_search($course1, 'two', array(2));
        $this->check_search($course2, 'title', array(3, 4));

        // Transfer half the first course.
        $this->assertFalse(year_tables::split_tables_chunk(false, 1));
        $this->assertEquals(2, $DB->count_records('local_ousearch_occurs_2013'));
        $this->check_search($course1, 'two', array(2));
        $this->check_search($course2, 'title', array(3, 4));

        // Transfer the other half of the first course.
        $this->assertFalse(year_tables::split_tables_chunk(false, 1));
        $this->assertEquals(4, $DB->count_records('local_ousearch_occurs_2013'));
        $this->check_search($course1, 'two', array(2));
        $this->check_search($course2, 'title', array(3, 4));

        // At this point the data for course 1 should still be present, but not
        // used, in the old tables. Bodge it up to make sure it's not used.
        $DB->set_field('local_ousearch_documents', 'intref1', 99, array('courseid' => $course1->id));
        $this->check_search($course1, 'two', array(2));
        $this->check_search($course2, 'title', array(3, 4));

        // Transfer the second course.
        $this->assertTrue(year_tables::split_tables_chunk(false, 2));
        $this->assertEquals(4, $DB->count_records('local_ousearch_occurs_2015'));
        $this->assertEquals(0, $DB->count_records('local_ousearch_occurrences'));
        $this->check_search($course1, 'two', array(2));
        $this->check_search($course2, 'title', array(3, 4));
    }

    /**
     * Adds a test document to the index and to the in-memory store.
     *
     * @param int $index Document index
     * @param stdClass $course Course object
     * @param string $content Text content
     * @param int $cmid If specified, cmid of document
     */
    private static function add_test_document($index, $course, $content, $cmid = 0) {
        $doc = new local_ousearch_document();
        $doc->courseid = $course->id;
        $doc->plugin = 'test_yeartablestest';
        $doc->timemodified = $index;
        $doc->set_int_refs($index);
        if ($cmid) {
            $doc->coursemoduleid = $cmid;
        }
        $doc->update('Title', $content);
        self::$testdocuments[$index] = array('title' => 'Title', 'content' => $content);
    }

    /**
     * Run a search for test documents.
     *
     * @param stdClass $course Course
     * @param string $query Query
     * @param array $expected List of expected indexes
     */
    private function check_search($course, $query, array $expected) {
        $search = new local_ousearch_search($query);
        $search->set_plugin('test_yeartablestest');
        $search->set_course_id($course->id);
        $this->check_search_inner($search, $expected);
    }

    /**
     * Run a search for test documents after already setting up search object.
     *
     * @param local_ousearch_search $search Search object
     * @param array $expected Array of expected indexes
     */
    private function check_search_inner(local_ousearch_search $search, array $expected) {
        $out = $search->query();
        $indexes = array();
        foreach ($out->results as $result) {
            $indexes[] = $result->intref1;
        }
        sort($indexes);
        $this->assertEquals($expected, $indexes);
    }

    /**
     * Tests the change_dates_chunk function.
     */
    public function test_change_dates_chunk() {
        global $DB;
        $this->resetAfterTest();

        // Create two test courses.
        $course1 = $this->getDataGenerator()->create_course(array(
                'startdate' => strtotime('2013-01-04')));
        $course2 = $this->getDataGenerator()->create_course(array(
                'startdate' => strtotime('2015-01-04')));

        // Turn the system completely on.
        set_config(year_tables::CONFIG_ENABLED, year_tables::ENABLED_TRANSFERRING, 'local_ousearch');
        while (true) {
            if (year_tables::split_tables_chunk(false)) {
                break;
            }
        }

        // Add two documents to one course and one to the other.
        self::add_test_document(1, $course1, 'One');
        self::add_test_document(2, $course1, 'Two');
        self::add_test_document(3, $course2, 'Three');
        $this->assertEquals(4, $DB->count_records('local_ousearch_occurs_2013'));
        $this->assertEquals(2, $DB->count_records('local_ousearch_occurs_2015'));

        // Initial search check.
        $this->check_search($course1, 'title', array(1, 2));
        $this->check_search($course2, 'three', array(3));

        // Change the date for course1.
        $DB->set_field('course', 'startdate',
                strtotime('2015-01-04'), array('id' => $course1->id));
        year_tables::handle_updated_course($course1->id);

        // Move it all.
        $this->assertTrue(year_tables::change_dates_chunk(false));
        $this->assertEquals(0, $DB->count_records('local_ousearch_occurs_2013'));
        $this->assertEquals(6, $DB->count_records('local_ousearch_occurs_2015'));
        $this->assertEquals('', $DB->get_field('local_ousearch_courseyears',
                'oldyears', array('courseid' => $course1->id)));
        $this->check_search($course1, 'title', array(1, 2));

        // Now move it twice.
        $DB->set_field('course', 'startdate',
                strtotime('2017-01-04'), array('id' => $course1->id));
        year_tables::handle_updated_course($course1->id);
        $DB->set_field('course', 'startdate',
                strtotime('2016-01-04'), array('id' => $course1->id));
        year_tables::handle_updated_course($course1->id);

        // This time it will do it in 2 chunks (2015 then 2017).
        $this->assertFalse(year_tables::change_dates_chunk(false));
        $this->assertTrue(year_tables::change_dates_chunk(false));
        $this->assertEquals(2, $DB->count_records('local_ousearch_occurs_2015'));
        $this->assertEquals(4, $DB->count_records('local_ousearch_occurs_2016'));
        $this->check_search($course1, 'title', array(1, 2));

        // If there are too many to move in one chunk, do it in two.
        $DB->set_field('course', 'startdate',
                strtotime('2017-01-04'), array('id' => $course1->id));
        year_tables::handle_updated_course($course1->id);
        $this->assertFalse(year_tables::change_dates_chunk(false, 1));

        // Halfway through, one of the documents should have been copied while
        // the other is in the wrong table.
        $this->check_search($course1, 'title', array(1));

        // Finish the job.
        $this->assertTrue(year_tables::change_dates_chunk(false, 1));
        $this->check_search($course1, 'title', array(1, 2));
    }

    /**
     * Test the search function with a list of CMs. This was broken at one point.
     */
    public function test_search_with_cm_list() {
        global $DB;
        $this->resetAfterTest();

        // Turn on the system.
        set_config(year_tables::CONFIG_ENABLED, year_tables::ENABLED_ON, 'local_ousearch');

        // Create a test courses.
        $course1 = $this->getDataGenerator()->create_course(array(
                'startdate' => strtotime('2013-01-04 10:00')));

        // Create imaginary CMs.
        $cm1 = (object)array('id' => 19, 'course' => $course1->id);
        $cm2 = (object)array('id' => 77, 'course' => $course1->id);
        $cm3 = (object)array('id' => 138, 'course' => $course1->id);

        // Add four documents, two to one CM and one to two others..
        self::add_test_document(1, $course1, 'Doc One', $cm1->id);
        self::add_test_document(2, $course1, 'Doc Two', $cm2->id);
        self::add_test_document(3, $course1, 'Doc Three', $cm2->id);
        self::add_test_document(4, $course1, 'Doc Four', $cm3->id);

        // Check search on whole course.
        $this->check_search($course1, 'Doc', array(1, 2, 3, 4));

        // Check search on cm1 and cm2.
        $search = new local_ousearch_search('Doc');
        $search->set_plugin('test_yeartablestest');
        $search->set_coursemodule_array(array($cm1, $cm2));
        $this->check_search_inner($search, array(1, 2, 3));

        // Check on cm1 and cm3.
        $search = new local_ousearch_search('Doc');
        $search->set_plugin('test_yeartablestest');
        $search->set_coursemodule_array(array($cm1, $cm3));
        $this->check_search_inner($search, array(1, 4));
    }
}

/**
 * OU search function for test_zombie plugin.
 * @param stdClass $result Input values
 * @return boolean|stdClass False if failed, otherwise document data
 */
function yeartablestest_ousearch_get_document($result) {
    if (!array_key_exists($result->intref1,
            local_ousearch_year_tables_test::$testdocuments)) {
        return false;
    }
    $page = new stdClass;
    $content = local_ousearch_year_tables_test::$testdocuments[$result->intref1];
    $page->content = $content['content'];
    $page->title = $content['title'];
    $page->activityname = 'Test activity';
    $page->activityurl = 'http://activity/';
    $page->url = 'http://result/';
    return $page;
}

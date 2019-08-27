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
 * Data provider.
 *
 * @package    local_ousearch
 * @copyright  2018 The Open University
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_ousearch\privacy;

use context;
use core_privacy\local\metadata\collection;
use core_privacy\local\request\approved_contextlist;
use core_privacy\local\request\contextlist;
use core_privacy\local\request\helper;
use core_privacy\local\request\transform;
use core_privacy\local\request\writer;

defined('MOODLE_INTERNAL') || die();

/**
 * Data provider class.
 *
 * @package    local_ousearch
 * @copyright  2018 The Open University
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class provider implements
        \core_privacy\local\metadata\provider,
        \core_privacy\local\request\plugin\provider {

    /**
     * Returns meta data about this system.
     *
     * @param collection $collection The initialised collection to add items to.
     * @return collection A listing of user data stored through this system.
     */
    public static function get_metadata(collection $collection): collection {
        $collection->add_database_table('local_ousearch_documents', [
                'plugin' => 'privacy:metadata:local_ousearch_documents:plugin',
                'userid' => 'privacy:metadata:local_ousearch_documents:userid',
                'stringref' => 'privacy:metadata:local_ousearch_documents:stringref',
                'intref1' => 'privacy:metadata:local_ousearch_documents:intref1',
                'intref2' => 'privacy:metadata:local_ousearch_documents:intref2',
                'timemodified' => 'privacy:metadata:local_ousearch_documents:timemodified',
                'timeexpires' => 'privacy:metadata:local_ousearch_documents:timeexpires',
        ], 'privacy:metadata:local_ousearch_documents');

        $collection->add_database_table('local_ousearch_docs_2011', [
                'plugin' => 'privacy:metadata:local_ousearch_documents:plugin',
                'userid' => 'privacy:metadata:local_ousearch_documents:userid',
                'stringref' => 'privacy:metadata:local_ousearch_documents:stringref',
                'intref1' => 'privacy:metadata:local_ousearch_documents:intref1',
                'intref2' => 'privacy:metadata:local_ousearch_documents:intref2',
                'timemodified' => 'privacy:metadata:local_ousearch_documents:timemodified',
                'timeexpires' => 'privacy:metadata:local_ousearch_documents:timeexpires',
        ], 'privacy:metadata:local_ousearch_documents');

        $collection->add_database_table('local_ousearch_docs_2012', [
                'plugin' => 'privacy:metadata:local_ousearch_documents:plugin',
                'userid' => 'privacy:metadata:local_ousearch_documents:userid',
                'stringref' => 'privacy:metadata:local_ousearch_documents:stringref',
                'intref1' => 'privacy:metadata:local_ousearch_documents:intref1',
                'intref2' => 'privacy:metadata:local_ousearch_documents:intref2',
                'timemodified' => 'privacy:metadata:local_ousearch_documents:timemodified',
                'timeexpires' => 'privacy:metadata:local_ousearch_documents:timeexpires',
        ], 'privacy:metadata:local_ousearch_documents');

        $collection->add_database_table('local_ousearch_docs_2013', [
                'plugin' => 'privacy:metadata:local_ousearch_documents:plugin',
                'userid' => 'privacy:metadata:local_ousearch_documents:userid',
                'stringref' => 'privacy:metadata:local_ousearch_documents:stringref',
                'intref1' => 'privacy:metadata:local_ousearch_documents:intref1',
                'intref2' => 'privacy:metadata:local_ousearch_documents:intref2',
                'timemodified' => 'privacy:metadata:local_ousearch_documents:timemodified',
                'timeexpires' => 'privacy:metadata:local_ousearch_documents:timeexpires',
        ], 'privacy:metadata:local_ousearch_documents');

        $collection->add_database_table('local_ousearch_docs_2014', [
                'plugin' => 'privacy:metadata:local_ousearch_documents:plugin',
                'userid' => 'privacy:metadata:local_ousearch_documents:userid',
                'stringref' => 'privacy:metadata:local_ousearch_documents:stringref',
                'intref1' => 'privacy:metadata:local_ousearch_documents:intref1',
                'intref2' => 'privacy:metadata:local_ousearch_documents:intref2',
                'timemodified' => 'privacy:metadata:local_ousearch_documents:timemodified',
                'timeexpires' => 'privacy:metadata:local_ousearch_documents:timeexpires',
        ], 'privacy:metadata:local_ousearch_documents');

        $collection->add_database_table('local_ousearch_docs_2015', [
                'plugin' => 'privacy:metadata:local_ousearch_documents:plugin',
                'userid' => 'privacy:metadata:local_ousearch_documents:userid',
                'stringref' => 'privacy:metadata:local_ousearch_documents:stringref',
                'intref1' => 'privacy:metadata:local_ousearch_documents:intref1',
                'intref2' => 'privacy:metadata:local_ousearch_documents:intref2',
                'timemodified' => 'privacy:metadata:local_ousearch_documents:timemodified',
                'timeexpires' => 'privacy:metadata:local_ousearch_documents:timeexpires',
        ], 'privacy:metadata:local_ousearch_documents');

        $collection->add_database_table('local_ousearch_docs_2016', [
                'plugin' => 'privacy:metadata:local_ousearch_documents:plugin',
                'userid' => 'privacy:metadata:local_ousearch_documents:userid',
                'stringref' => 'privacy:metadata:local_ousearch_documents:stringref',
                'intref1' => 'privacy:metadata:local_ousearch_documents:intref1',
                'intref2' => 'privacy:metadata:local_ousearch_documents:intref2',
                'timemodified' => 'privacy:metadata:local_ousearch_documents:timemodified',
                'timeexpires' => 'privacy:metadata:local_ousearch_documents:timeexpires',
        ], 'privacy:metadata:local_ousearch_documents');

        $collection->add_database_table('local_ousearch_docs_2017', [
                'plugin' => 'privacy:metadata:local_ousearch_documents:plugin',
                'userid' => 'privacy:metadata:local_ousearch_documents:userid',
                'stringref' => 'privacy:metadata:local_ousearch_documents:stringref',
                'intref1' => 'privacy:metadata:local_ousearch_documents:intref1',
                'intref2' => 'privacy:metadata:local_ousearch_documents:intref2',
                'timemodified' => 'privacy:metadata:local_ousearch_documents:timemodified',
                'timeexpires' => 'privacy:metadata:local_ousearch_documents:timeexpires',
        ], 'privacy:metadata:local_ousearch_documents');

        $collection->add_database_table('local_ousearch_docs_2018', [
                'plugin' => 'privacy:metadata:local_ousearch_documents:plugin',
                'userid' => 'privacy:metadata:local_ousearch_documents:userid',
                'stringref' => 'privacy:metadata:local_ousearch_documents:stringref',
                'intref1' => 'privacy:metadata:local_ousearch_documents:intref1',
                'intref2' => 'privacy:metadata:local_ousearch_documents:intref2',
                'timemodified' => 'privacy:metadata:local_ousearch_documents:timemodified',
                'timeexpires' => 'privacy:metadata:local_ousearch_documents:timeexpires',
        ], 'privacy:metadata:local_ousearch_documents');

        $collection->add_database_table('local_ousearch_docs_2019', [
                'plugin' => 'privacy:metadata:local_ousearch_documents:plugin',
                'userid' => 'privacy:metadata:local_ousearch_documents:userid',
                'stringref' => 'privacy:metadata:local_ousearch_documents:stringref',
                'intref1' => 'privacy:metadata:local_ousearch_documents:intref1',
                'intref2' => 'privacy:metadata:local_ousearch_documents:intref2',
                'timemodified' => 'privacy:metadata:local_ousearch_documents:timemodified',
                'timeexpires' => 'privacy:metadata:local_ousearch_documents:timeexpires',
        ], 'privacy:metadata:local_ousearch_documents');

        $collection->add_database_table('local_ousearch_docs_2020', [
                'plugin' => 'privacy:metadata:local_ousearch_documents:plugin',
                'userid' => 'privacy:metadata:local_ousearch_documents:userid',
                'stringref' => 'privacy:metadata:local_ousearch_documents:stringref',
                'intref1' => 'privacy:metadata:local_ousearch_documents:intref1',
                'intref2' => 'privacy:metadata:local_ousearch_documents:intref2',
                'timemodified' => 'privacy:metadata:local_ousearch_documents:timemodified',
                'timeexpires' => 'privacy:metadata:local_ousearch_documents:timeexpires',
        ], 'privacy:metadata:local_ousearch_documents');

        return $collection;
    }

    /**
     * Get the list of contexts that contain user information for the specified user.
     *
     * @param int $userid The user to search.
     * @return contextlist $contextlist The contextlist containing the list of contexts used in this plugin.
     */
    public static function get_contexts_for_userid(int $userid): contextlist {
        $contextlist = new contextlist();

        $sql = 'SELECT DISTINCT ctx.id
                  FROM {context} ctx
                  JOIN (SELECT * FROM {local_ousearch_documents} UNION
                        SELECT * FROM {local_ousearch_docs_2011} UNION
                        SELECT * FROM {local_ousearch_docs_2012} UNION
                        SELECT * FROM {local_ousearch_docs_2013} UNION
                        SELECT * FROM {local_ousearch_docs_2014} UNION
                        SELECT * FROM {local_ousearch_docs_2015} UNION
                        SELECT * FROM {local_ousearch_docs_2016} UNION
                        SELECT * FROM {local_ousearch_docs_2017} UNION
                        SELECT * FROM {local_ousearch_docs_2018} UNION
                        SELECT * FROM {local_ousearch_docs_2019} UNION
                        SELECT * FROM {local_ousearch_docs_2020}) d
                        ON d.coursemoduleid = ctx.instanceid
                        AND ctx.contextlevel = :contextmodule
                        AND d.userid = :userid';
        $params = [
                'contextmodule' => CONTEXT_MODULE,
                'userid' => $userid
        ];

        $contextlist->add_from_sql($sql, $params);

        return $contextlist;
    }

    /**
     * Export all user data for the specified user, in the specified contexts.
     *
     * @param approved_contextlist $contextlist The approved contexts to export information for.
     * @throws \dml_exception
     * @throws \coding_exception
     */
    public static function export_user_data(approved_contextlist $contextlist) {
        global $DB;

        if (empty($contextlist->count())) {
            return;
        }

        $user = $contextlist->get_user();

        list($contextsql, $contextparams) = $DB->get_in_or_equal($contextlist->get_contextids(), SQL_PARAMS_NAMED);

        $sql = "SELECT ctx.id as contextid, d.*
                  FROM {context} ctx
                  JOIN (SELECT *, 0 as year FROM {local_ousearch_documents} UNION
                        SELECT *, 2011 as year FROM {local_ousearch_docs_2011} UNION
                        SELECT *, 2012 as year FROM {local_ousearch_docs_2012} UNION
                        SELECT *, 2013 as year FROM {local_ousearch_docs_2013} UNION
                        SELECT *, 2014 as year FROM {local_ousearch_docs_2014} UNION
                        SELECT *, 2015 as year FROM {local_ousearch_docs_2015} UNION
                        SELECT *, 2016 as year FROM {local_ousearch_docs_2016} UNION
                        SELECT *, 2017 as year FROM {local_ousearch_docs_2017} UNION
                        SELECT *, 2018 as year FROM {local_ousearch_docs_2018} UNION
                        SELECT *, 2019 as year FROM {local_ousearch_docs_2019} UNION
                        SELECT *, 2020 as year FROM {local_ousearch_docs_2020}) d
                        ON d.coursemoduleid = ctx.instanceid
                        AND ctx.contextlevel = :contextmodule
                        AND d.userid = :userid
                  WHERE ctx.id {$contextsql}
               ORDER BY ctx.id, d.id ASC";
        $params = [
                'contextmodule' => CONTEXT_MODULE,
                'userid' => $user->id
        ];

        $rs = $DB->get_recordset_sql($sql, $params + $contextparams);

        $subcontext = [get_string('ousearch', 'local_ousearch')];
        $contextdata = null;
        $context = null;
        foreach ($rs as $record) {
            if (empty($context->id) || $context->id != $record->contextid) {
                if ($contextdata != null) {
                    writer::with_context($context)->export_data($subcontext, $contextdata);
                    $contextdata = null;
                }

                if ($contextdata == null) {
                    $context = context::instance_by_id($record->contextid);
                    $contextdata = helper::get_context_data($context, $user);
                    $contextdata->documents = [];
                }
            }

            $data = (object) [
                    'plugin' => $record->plugin,
                    'userid' => self::you_or_somebody_else($record->userid, $user),
                    'stringref' => $record->stringref,
                    'intref1' => $record->intref1,
                    'intref2' => $record->intref2,
                    'timemodified' => empty($record->timemodified) ? 0 : transform::datetime($record->timemodified),
                    'timeexpires' => empty($record->timeexpires) ? 0 : transform::datetime($record->timeexpires),
            ];

            if (empty($record->year) || $record->year == '0') {
                $contextdata->documents[$record->id] = $data;
            } else {
                $propertyname = 'documents' . $record->year;
                if (!isset($contextdata->$propertyname)) {
                    $contextdata->$propertyname = [];
                }
                ($contextdata->$propertyname)[$record->id] = $data;
            }
        }
        $rs->close();

        if ($contextdata != null) {
            writer::with_context($context)->export_data($subcontext, $contextdata);
        }
    }

    /**
     * Delete all data for all users in the specified context.
     *
     * @param \context $context The specific context to delete data for.
     * @throws \dml_exception
     */
    public static function delete_data_for_all_users_in_context(\context $context) {
        if (!$context instanceof \context_module) {
            return;
        }

        self::delete_document_for_user($context->instanceid, 'local_ousearch_documents');
        self::delete_document_for_user($context->instanceid, 'local_ousearch_docs_2011');
        self::delete_document_for_user($context->instanceid, 'local_ousearch_docs_2012');
        self::delete_document_for_user($context->instanceid, 'local_ousearch_docs_2013');
        self::delete_document_for_user($context->instanceid, 'local_ousearch_docs_2014');
        self::delete_document_for_user($context->instanceid, 'local_ousearch_docs_2015');
        self::delete_document_for_user($context->instanceid, 'local_ousearch_docs_2016');
        self::delete_document_for_user($context->instanceid, 'local_ousearch_docs_2017');
        self::delete_document_for_user($context->instanceid, 'local_ousearch_docs_2018');
        self::delete_document_for_user($context->instanceid, 'local_ousearch_docs_2019');
        self::delete_document_for_user($context->instanceid, 'local_ousearch_docs_2020');
    }

    /**
     * Delete all user data for the specified user, in the specified contexts.
     *
     * @param approved_contextlist $contextlist The approved contexts and user information to delete information for.
     * @throws \coding_exception
     * @throws \dml_exception
     */
    public static function delete_data_for_user(approved_contextlist $contextlist) {
        if (empty($contextlist->count())) {
            return;
        }

        $userid = $contextlist->get_user()->id;

        $cmids = [];

        foreach ($contextlist as $context) {
            $cmids[] = $context->instanceid;
        }

        self::change_document_owner_to_admin($userid, $cmids, 'local_ousearch_documents');
        self::change_document_owner_to_admin($userid, $cmids, 'local_ousearch_docs_2011');
        self::change_document_owner_to_admin($userid, $cmids, 'local_ousearch_docs_2012');
        self::change_document_owner_to_admin($userid, $cmids, 'local_ousearch_docs_2013');
        self::change_document_owner_to_admin($userid, $cmids, 'local_ousearch_docs_2014');
        self::change_document_owner_to_admin($userid, $cmids, 'local_ousearch_docs_2015');
        self::change_document_owner_to_admin($userid, $cmids, 'local_ousearch_docs_2016');
        self::change_document_owner_to_admin($userid, $cmids, 'local_ousearch_docs_2017');
        self::change_document_owner_to_admin($userid, $cmids, 'local_ousearch_docs_2018');
        self::change_document_owner_to_admin($userid, $cmids, 'local_ousearch_docs_2019');
        self::change_document_owner_to_admin($userid, $cmids, 'local_ousearch_docs_2020');
    }

    /**
     * Delete document belong to user of course module id.
     *
     * @param int $coursemoduleid
     * @param string $tablename
     * @throws \dml_exception
     */
    protected static function delete_document_for_user(int $coursemoduleid, string $tablename) {
        global $DB;

        $sql = "DELETE FROM {{$tablename}}
                 WHERE userid IS NOT NULL
                       AND coursemoduleid = :coursemoduleid";

        $params = [
                'coursemoduleid' => $coursemoduleid
        ];

        $DB->execute($sql, $params);
    }

    /**
     * @param int $userid
     * @param array $coursemoduleids
     * @param string $tablename
     * @throws \coding_exception
     * @throws \dml_exception
     */
    protected static function change_document_owner_to_admin(int $userid, array $coursemoduleids, string $tablename) {
        global $DB;

        list($cmsql, $cmparams) = $DB->get_in_or_equal($coursemoduleids, SQL_PARAMS_NAMED);

        $sql = "UPDATE {{$tablename}}
                   SET userid = :adminuserid
                 WHERE userid = :userid
                       AND coursemoduleid {$cmsql}";

        $params = [
                'adminuserid' => get_admin()->id,
                'userid' => $userid
        ];
        $params += $cmparams;

        $DB->execute($sql, $params);
    }

    /**
     * Removes personally-identifiable data from a user id for export.
     *
     * @param int $userid User id of a person
     * @param \stdClass $user Object representing current user being considered
     * @return string 'You' if the two users match, 'Somebody else' otherwise
     * @throws \coding_exception
     */
    protected static function you_or_somebody_else($userid, $user) {
        if ($userid == $user->id) {
            return get_string('privacy_you', 'local_ousearch');
        } else {
            return get_string('privacy_somebodyelse', 'local_ousearch');
        }
    }
}

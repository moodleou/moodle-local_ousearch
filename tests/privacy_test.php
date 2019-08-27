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
 * Data provider tests for booking system module.
 *
 * @package    local_ousearch
 * @copyright  2018 The Open University
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

use local_ousearch\privacy\provider;
use core_privacy\local\request\transform;
use core_privacy\tests\provider_testcase;
use core_privacy\local\request\approved_contextlist;
use core_privacy\local\request\writer;

/**
 * Data provider testcase class.
 *
 * @package    local_ousearch
 * @copyright  2018 The Open University
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class local_ousearch_privacy_testcase extends provider_testcase {
    /**
     * @var stdClass
     */
    protected $course;

    /**
     * @var stdClass
     */
    protected $user;

    /**
     * @var array
     */
    protected $contexts;

    /**
     * @var array
     */
    protected $documents;

    /**
     * @var array
     */
    protected $subcontext;

    /**
     * Set up data required for the test case.
     *
     * @throws dml_exception
     * @throws moodle_exception
     */
    public function setUp() {
        $this->resetAfterTest();

        $generator = $this->getDataGenerator();
        $this->course = $generator->create_course();
        $this->user = $generator->create_user();
        $page1 = $this->getDataGenerator()->create_module('page',
                ['course' => $this->course->id], ['section' => 1]);
        $page2 = $this->getDataGenerator()->create_module('page',
                ['course' => $this->course->id], ['section' => 1]);

        $this->contexts = [
                context_module::instance($page1->cmid),
                context_module::instance($page2->cmid),
        ];

        $this->documents = [
                $this->create_search_document('page', $this->course->id, $this->contexts[0]->instanceid, 0, $this->user->id, 2018),
                $this->create_search_document('page', $this->course->id, $this->contexts[0]->instanceid, 0, $this->user->id),
                $this->create_search_document('page', $this->course->id, $this->contexts[1]->instanceid, 0, $this->user->id, 2017),
        ];

        $this->subcontext = [get_string('ousearch', 'local_ousearch')];
    }

    /**
     * Test get context list for user id.
     *
     * @throws dml_exception
     */
    public function test_get_contexts_for_userid() {
        $contextids = provider::get_contexts_for_userid($this->user->id)->get_contextids();

        $this->assertCount(2, $contextids);
        $this->assertContains($this->contexts[0]->id, $contextids);
        $this->assertContains($this->contexts[1]->id, $contextids);

        // Test get data for new user.
        $user2 = $this->getDataGenerator()->create_user();
        $contextids = provider::get_contexts_for_userid($user2->id)->get_contextids();
        $this->assertEmpty($contextids);

        $this->create_search_document('page', $this->course->id, $this->contexts[0]->instanceid, 0, $user2->id, 2019);
        $contextids = provider::get_contexts_for_userid($user2->id)->get_contextids();
        $this->assertCount(1, $contextids);
        $this->assertContains($this->contexts[0]->id, $contextids);
    }

    /**
     * Test export data for user.
     *
     * @throws coding_exception
     * @throws dml_exception
     */
    public function test_export_user_data() {
        $contextids = [$this->contexts[0]->id, $this->contexts[1]->id];

        $appctx = new approved_contextlist($this->user, 'local_ousearch', $contextids);
        provider::export_user_data($appctx);

        $contextdata = writer::with_context($this->contexts[0]);
        $result = $contextdata->get_data($this->subcontext);

        $this->assertCount(1, $result->documents);
        $this->assertCount(1, $result->documents2018);
        $expected = (object) [
                'plugin' => $this->documents[1]->plugin,
                'userid' => get_string('privacy_you', 'local_ousearch'),
                'stringref' => $this->documents[1]->stringref,
                'intref1' => $this->documents[1]->intref1,
                'intref2' => $this->documents[1]->intref2,
                'timemodified' => transform::datetime($this->documents[1]->timemodified),
                'timeexpires' => transform::datetime($this->documents[1]->timeexpires),
        ];
        $this->assertEquals($expected, $result->documents[$this->documents[1]->id]);
        $expected = (object) [
                'plugin' => $this->documents[0]->plugin,
                'userid' => get_string('privacy_you', 'local_ousearch'),
                'stringref' => $this->documents[0]->stringref,
                'intref1' => $this->documents[0]->intref1,
                'intref2' => $this->documents[0]->intref2,
                'timemodified' => transform::datetime($this->documents[0]->timemodified),
                'timeexpires' => transform::datetime($this->documents[0]->timeexpires),
        ];
        $this->assertEquals($expected, $result->documents2018[$this->documents[0]->id]);

        $contextdata = writer::with_context($this->contexts[1]);
        $result = $contextdata->get_data($this->subcontext);
        $this->assertCount(0, $result->documents);
        $this->assertCount(1, $result->documents2017);
        $expected = (object) [
                'plugin' => $this->documents[2]->plugin,
                'userid' => get_string('privacy_you', 'local_ousearch'),
                'stringref' => $this->documents[2]->stringref,
                'intref1' => $this->documents[2]->intref1,
                'intref2' => $this->documents[2]->intref2,
                'timemodified' => transform::datetime($this->documents[2]->timemodified),
                'timeexpires' => transform::datetime($this->documents[2]->timeexpires),
        ];
        $this->assertEquals($expected, $result->documents2017[$this->documents[2]->id]);
    }

    /**
     * Test delete data for all user in the context.
     *
     * @throws dml_exception
     */
    public function test_delete_data_for_all_users_in_context() {
        global $DB;

        // Add one document without user to context 1.
        $newdoc = $this->create_search_document('page', $this->course->id, $this->contexts[0]->instanceid,
                0, null, 2018);

        // Delete all user's data belong to module1.
        provider::delete_data_for_all_users_in_context($this->contexts[0]);

        // Delete all user's data belong to first module.
        $records = $DB->get_records('local_ousearch_documents');
        $records2017 = $DB->get_records('local_ousearch_docs_2017');
        $records2018 = $DB->get_records('local_ousearch_docs_2018');

        // Check only user data belong to first context is deleted.
        $this->assertEmpty($records);
        $this->assertCount(1, $records2017);
        $this->assertCount(1, $records2018);
        $this->assertArrayHasKey($newdoc->id, $records2018);
        $this->assertArrayHasKey($this->documents[2]->id, $records2017);
    }

    /**
     * Test delete data for users in context.
     *
     * @throws coding_exception
     * @throws dml_exception
     */
    public function test_delete_data_for_user_context() {
        global $DB;

        // Check delete user data for only for bs2 to make sure the query's condition is correct.
        $appctx = new approved_contextlist($this->user, 'local_ousearch', [
                $this->contexts[0]->id,
                $this->contexts[1]->id
        ]);
        provider::delete_data_for_user($appctx);

        $records = $DB->get_records('local_ousearch_documents');
        $records2017 = $DB->get_records('local_ousearch_docs_2017');
        $records2018 = $DB->get_records('local_ousearch_docs_2018');

        // Check owner of deleting document is change to admin.
        $this->assertCount(1, $records);
        $this->assertCount(1, $records2017);
        $this->assertCount(1, $records2018);

        $adminuserid = get_admin()->id;

        $this->assertEquals($adminuserid, $records[$this->documents[1]->id]->userid);
        $this->assertEquals($adminuserid, $records2017[$this->documents[2]->id]->userid);
        $this->assertEquals($adminuserid, $records2018[$this->documents[0]->id]->userid);
    }

    /**
     * Create search document.
     *
     * @param string $plugin
     * @param int $courseid
     * @param int $coursemoduleid
     * @param int $groupid
     * @param int $userid
     * @param string|null $year
     * @return bool|object
     * @throws dml_exception
     */
    protected function create_search_document(string $plugin, int $courseid, int $coursemoduleid, int $groupid, $userid,
            string $year = null) {
        global $DB;

        $data = (object) [
                'id' => 0,
                'plugin' => $plugin,
                'courseid' => $courseid,
                'coursemoduleid' => $coursemoduleid,
                'groupid' => $groupid,
                'userid' => $userid,
                'stringref' => 'Sample string ref',
                'intref1' => rand(1, 10),
                'intref2' => rand(1, 10),
                'timemodified' => rand(1300000000, 1500000000),
                'timeexpires' => rand(1500000001, 2000000000),
        ];

        if (empty($year)) {
            $tablename = 'local_ousearch_documents';
        } else {
            $tablename = 'local_ousearch_docs_' . $year;
        }

        $data->id = $DB->insert_record($tablename, $data);

        return empty($data->id) ? false : $data;
    }
}

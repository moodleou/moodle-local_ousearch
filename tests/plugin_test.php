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
 * Unit tests for ousearch using plugins.
 *
 * @package local_ousearch
 * @copyright 2015 The Open University
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot . '/local/ousearch/searchlib.php');

/**
 * Unit tests for ousearch using ForumNG as a real-life example module.
 */
class local_ousearch_plugin_test extends advanced_testcase {
    /**
     * Checks that ForumNG is installed in this Moodle instance, otherwise skip.
     */
    private function check_skip() {
        global $CFG;
        if (!file_exists($CFG->dirroot . '/mod/forumng')) {
            $this->markTestSkipped('The real-life search test only works if ForumNG is installed.');
        }
    }

    /**
     * Tests searching across a whole course in cases of grouped and non-grouped
     * forums and users.
     */
    public function test_coursewide_search() {
        global $DB, $CFG;
        require_once($CFG->dirroot . '/course/lib.php');

        $this->check_skip();
        $this->resetAfterTest();

        // Create a couple of courses for testing.
        $generator = $this->getDataGenerator();
        $course1 = $generator->create_course();
        $course2 = $generator->create_course();

        // Create some groups in course 1.
        $group1 = $generator->create_group(array('courseid' => $course1->id));
        $group2 = $generator->create_group(array('courseid' => $course1->id));

        // Create two forums in course 1 and one in course 2.
        $forum1n = $generator->create_module('forumng', array('course' => $course1->id));
        $forum1g = $generator->create_module('forumng', array('course' => $course1->id,
                'groupmode' => SEPARATEGROUPS));
        $forum2 = $generator->create_module('forumng', array('course' => $course2->id));

        // Create two students on the course. One belongs to groups, the other doesn't.
        $student1 = $generator->create_user();
        $student2 = $generator->create_user();
        $studentroleid = $DB->get_field('role', 'id', array('shortname' => 'student'));
        $generator->enrol_user($student1->id, $course1->id, $studentroleid);
        $generator->enrol_user($student2->id, $course1->id, $studentroleid);
        $generator->enrol_user($student1->id, $course2->id, $studentroleid);
        groups_add_member($group1, $student1);

        // Add two discussions in forum 1N.
        $forumgen = $generator->get_plugin_generator('mod_forumng');
        $params = array('course' => $course1->id, 'forum' => $forum1n->id,
                'userid' => $student1->id);
        $params['subject'] = 'D1 Apples';
        $forumgen->create_discussion($params);
        $params['subject'] = 'D2 Oranges';
        $forumgen->create_discussion($params);

        // Add a discussion to each group in forum 1G.
        $params['forum'] = $forum1g->id;
        $params['groupid'] = $group1->id;
        $params['subject'] = 'D3 Apples';
        $forumgen->create_discussion($params);
        $params['groupid'] = $group2->id;
        $params['subject'] = 'D4 Oranges';
        $forumgen->create_discussion($params);

        // Add two discussions in forum 2.
        $params = array('course' => $course2->id, 'forum' => $forum2->id,
                'userid' => $student1->id);
        $params['subject'] = 'D5 Apples';
        $forumgen->create_discussion($params);
        $params['subject'] = 'D6 Oranges';
        $forumgen->create_discussion($params);

        // Do a coursewide search for 'apples' on course 1 as student 1.
        $this->setUser($student1);
        $this->assertEquals(array('D1 Apples', 'D3 Apples'),
                $this->coursewide_search($course1, 'apples'));

        // Coursewide search for 'oranges' (doesn't have the group result.
        $this->assertEquals(array('D2 Oranges'),
                $this->coursewide_search($course1, 'oranges'));

        // Coursewide search for 'apples' on course 2.
        $this->assertEquals(array('D5 Apples'),
                $this->coursewide_search($course2, 'apples'));

        // Try as student2 (not in group).
        $this->setUser($student2);
        $this->assertEquals(array('D1 Apples'),
                $this->coursewide_search($course1, 'apples'));

        // Check it works if we hide one of the forums.
        set_coursemodule_visible($forum1n->cmid, false);
        $this->assertEquals(array(),
                $this->coursewide_search($course1, 'apples'));

        // Try as admin user (also not in group).
        $this->setAdminUser();
        $this->assertEquals(array('D2 Oranges', 'D4 Oranges'),
                $this->coursewide_search($course1, 'oranges'));
    }

    /**
     * Tests (multiple) user content specific searches as supported by oublog
     */
    public function test_user_specific_search() {
        global $DB, $CFG;
        require_once($CFG->dirroot . '/course/lib.php');

        if (!file_exists($CFG->dirroot . '/mod/oublog')) {
            $this->markTestSkipped('The real-life search test only works if oublog is installed.');
        }

        require_once($CFG->dirroot . '/mod/oublog/locallib.php');

        $this->resetAfterTest();
        $this->setAdminUser();

        // Create a course for testing.
        $generator = $this->getDataGenerator();
        $course1 = $generator->create_course();
        $student1 = $generator->create_user();
        $student2 = $generator->create_user();
        $studentroleid = $DB->get_field('role', 'id', array('shortname' => 'student'));
        $generator->enrol_user($student1->id, $course1->id, $studentroleid);
        $generator->enrol_user($student2->id, $course1->id, $studentroleid);

        $indvidualblog = $generator->create_module('oublog', array('course' => $course1->id,
                'individual' => OUBLOG_SEPARATE_INDIVIDUAL_BLOGS));
        $bloggen = $generator->get_plugin_generator('mod_oublog');
        $post = array(
            'post' => (object) array (
                'userid' => $student1->id,
                'title' => 'Apple 1',
                'message' => 'Apple 1',
            )
        );
        $bloggen->create_content($indvidualblog, $post);
        $post = array(
            'post' => (object) array (
                'userid' => $student2->id,
                'title' => 'Apple 2',
                'message' => 'Apple 2',
            )
        );
        $bloggen->create_content($indvidualblog, $post);
        $this->assertEquals(array('Apple 1'),
                $this->coursewide_search($course1, 'Apple', 'oublog', array($student1->id)));
        $this->assertEquals(array('Apple 1', 'Apple 2'),
                $this->coursewide_search($course1, 'Apple', 'oublog',
                array($student1->id, $student2->id)));
        $this->assertEquals(array('Apple 1', 'Apple 2'),
                $this->coursewide_search($course1, 'Apple', 'oublog', array()));
        $this->assertEmpty($this->coursewide_search($course1, 'Apple', 'oublog',
                array(local_ousearch_search::NONE)));
    }

    /**
     * Do a coursewide search as the current user on the given course, through
     * all forums.
     *
     * @param stdClass $course Moodle course object
     * @param string $search Search query
     * @param string $plugin Mod to search against e.g. forumng
     * @param array $userids Search against specified user ids only (Default is user + groups)
     * @return array Array of titles of results (empty array if none)
     */
    protected function coursewide_search($course, $search, $plugin = 'forumng', $userids = null) {
        global $USER;

        // Based on the code in blocks/resources_search/search.class.php.
        $query = new local_ousearch_search($search);
        $query->set_course_id($course->id);
        $query->set_plugin("mod_$plugin");
        $query->set_visible_modules_in_course($course, $plugin);
        if (is_null($userids)) {
            $groups = groups_get_all_groups($course->id, $USER->id);
            $groupids = array();
            foreach ($groups as $group) {
                $groupids[] = $group->id;
            }
            $query->set_group_ids($groupids);
            $query->set_group_exceptions($query->get_group_exceptions($course->id));
            $query->set_user_id($USER->id);
        } else {
            $query->set_user_ids($userids);
        }

        $results = $query->query(0, 10);
        $out = array();
        if ($results->success) {
            foreach ($results->results as $result) {
                $out[] = strip_tags($result->title);
            }
        }

        sort($out);
        return $out;
    }
}

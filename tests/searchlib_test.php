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
 * Unit tests.
 *
 * @package local_ousearch
 * @copyright 2015 The Open University
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot . '/local/ousearch/searchlib.php');

class local_ousearch_test extends advanced_testcase {
    /**
     * @var array Array of document data used in the test
     */
    public static $zombiedocuments;

    /**
     * Tests adding search documents to database and updating them.
     */
    public function test_update() {
        global $DB;
        $this->resetAfterTest();

        $course = $this->getDataGenerator()->create_course();

        // Add document with all metadata.
        $doc1 = new local_ousearch_document();
        $doc1->init_test('frog');
        $doc1->set_group_id(13);
        $doc1->set_user_id(666);
        $doc1->set_string_ref('ribbit');
        $doc1->set_int_refs(3, 7);
        $doc1->update('Document title', 'Document text', 4, 0x7fffffff,
                array('extra string 1', 'extra string 2'));

        // Check db tables contain expected values.
        $this->assertEquals(1, $DB->count_records('local_ousearch_documents'));
        $record = $DB->get_record('local_ousearch_documents',
                array('stringref' => 'ribbit'));
        unset($record->id);
        $this->assertEquals((object)array('plugin' => 'test_frog',
                'courseid' => 0, 'coursemoduleid' => 0, 'groupid' => 13,
                'userid' => 666, 'stringref' => 'ribbit', 'intref1' => 3,
                'intref2' => 7, 'timemodified' => 4, 'timeexpires' => 0x7fffffff),
                $record);
        // 7 words are: document title text extra string 1 2.
        $this->assertEquals(7, $DB->count_records('local_ousearch_words'));
        $this->assertEquals(7, $DB->count_records('local_ousearch_occurrences'));

        // Add another document with the other init method and nothing set.
        $doc2 = new local_ousearch_document();
        $doc2->init_module_instance('frog', (object)array('id' => 13, 'course' => $course->id));
        $doc2->update('Document title 2', 'Document text 2');

        // Check db tables again.
        $this->assertEquals(2, $DB->count_records('local_ousearch_documents'));
        $record = $DB->get_record('local_ousearch_documents',
                array('plugin' => 'mod_frog'));
        unset($record->id);
        $this->assertTrue(time() - $record->timemodified <= 10);
        unset($record->timemodified);
        $this->assertEquals((object)array('plugin' => 'mod_frog',
                'courseid' => $course->id, 'coursemoduleid' => 13, 'groupid' => 0,
                'userid' => null, 'stringref' => null, 'intref1' => null,
                'intref2' => null, 'timeexpires' => null),
                $record);
        $this->assertEquals(7, $DB->count_records('local_ousearch_words'));
        $this->assertEquals(11, $DB->count_records('local_ousearch_occurrences'));

        // Update first document to change the text a bit.
        $doc1->update('Document title', 'Document', 4, 0x7fffffff,
            array('extra string 1', 'extra string 2'));

        // Updates existing document, not adds new one.
        $this->assertEquals(2, $DB->count_records('local_ousearch_documents'));
        // No added (or removed!) words.
        $this->assertEquals(7, $DB->count_records('local_ousearch_words'));
        // One occurrence removed (for 'text' in this doc).
        $this->assertEquals(10, $DB->count_records('local_ousearch_occurrences'));
    }

    /**
     * Tests the 'timemodified' field is updated as expected.
     */
    public function test_update_timemodified() {
        global $DB;
        $this->resetAfterTest();

        $doc1 = new local_ousearch_document();
        $doc1->init_test('frog');
        $doc1->update('Document title', 'Document text');

        // Check modified time is set to current.
        $now = $DB->get_field('local_ousearch_documents', 'timemodified', array());
        $this->assertTrue(time() - $now < 10);

        // Set it to something stupid.
        $DB->set_field('local_ousearch_documents', 'timemodified', 4);

        // Update it again.
        $doc1 = new local_ousearch_document();
        $doc1->init_test('frog');
        $doc1->update('Document title', 'Document text 2');

        // Check the time is updated too.
        $now = $DB->get_field('local_ousearch_documents', 'timemodified', array());
        $this->assertTrue(time() - $now < 10);
    }

    /**
     * Tests the delete function.
     */
    public function test_delete() {
        global $DB;
        $this->resetAfterTest();

        $course = $this->getDataGenerator()->create_course();

        // Create 2 documents.
        $doc1 = new local_ousearch_document();
        $doc1->init_module_instance('frog', (object)array('id' => 13, 'course' => $course->id));
        $doc1->update('Document title 1', 'Document text 1');
        $doc2 = new local_ousearch_document();
        $doc2->init_module_instance('frog', (object)array('id' => 14, 'course' => $course->id));
        $doc2->update('Document title 2', 'Document text 2');

        // Check table counts.
        $this->assertEquals(2, $DB->count_records('local_ousearch_documents'));
        $this->assertEquals(5, $DB->count_records('local_ousearch_words'));
        $this->assertEquals(8, $DB->count_records('local_ousearch_occurrences'));

        // Delete second document.
        $doc2->delete();

        // Only one document left.
        $this->assertEquals(1, $DB->count_records('local_ousearch_documents'));
        // No removed words - system never removes words.
        $this->assertEquals(5, $DB->count_records('local_ousearch_words'));
        // Half the occurrences removed.
        $this->assertEquals(4, $DB->count_records('local_ousearch_occurrences'));
    }

    /**
     * Tests the delete_all_for_cm function.
     */
    public function test_delete_all_for_cm() {
        global $DB;
        $this->resetAfterTest();

        $course = $this->getDataGenerator()->create_course();

        // Create a couple of documents for a specific course-module.
        $doc = new local_ousearch_document();
        $cm1 = (object)array('id' => 15, 'course' => $course->id);
        $doc->init_module_instance('frog', $cm1);
        $doc->set_int_refs(7);
        $doc->update('Document title', 'Document');
        $doc = new local_ousearch_document();
        $doc->init_module_instance('frog', $cm1);
        $doc->set_int_refs(3);
        $doc->update('Document title', 'Document');

        // And one document for another one.
        $doc = new local_ousearch_document();
        $cm2 = (object)array('id' => 14, 'course' => $course->id);
        $doc->init_module_instance('frog', $cm2);
        $doc->update('Document title', 'Document');

        // 3 documents now.
        $this->assertEquals(3, $DB->count_records('local_ousearch_documents'));

        // Delete all for cm.
        local_ousearch_document::delete_module_instance_data($cm1);

        // 1 document now.
        $this->assertEquals(1, $DB->count_records('local_ousearch_documents'));
    }

    public function test_ousearch_query() {
        global $DB;
        $this->resetAfterTest();

        // Create a bunch of search documents within test_zombie plugin.
        self::$zombiedocuments = array(
            1 => (object)array(
                'title' => 'Document title',
                'content' => 'First zombie document'),
            2 => (object)array(
                'title' => 'Another title',
                'content' => 'Title title first'),
            3 => (object)array(
                'title' => 'Document title',
                'content' => 'Not a zombie document title'),
            4 => (object)array(
                'title' => 'Delete me',
                'content' => 'Delete me'),
            100 => (object)array(
                'title' => 'Bottle quantity',
                'content' => 'There are this many bottles on the wall: 0'),
        );
        for ($i = 101; $i <= 199; $i++) {
            self::$zombiedocuments[$i] = self::$zombiedocuments[100];
            self::$zombiedocuments[$i]->content = str_replace(': 0',
                    ': ' . ($i - 100), self::$zombiedocuments[$i]->content);
        }
        foreach (self::$zombiedocuments as $key => $content) {
            $doc = new local_ousearch_document();
            $doc->init_test('zombie');
            $doc->set_int_refs($key);
            $doc->update($content->title, $content->content, null, null, null);
        }

        // Search for single unique term.
        $result = $this->do_zombie_query('not');
        $this->assertTrue($result->success);
        $this->assertEquals(array(3), $this->get_result_ids($result));

        // Search for nonexistent word.
        $result = $this->do_zombie_query('xyzzy');
        $this->assertFalse($result->success);
        $this->assertEquals('xyzzy', $result->problemword);

        // Search for nothing.
        $result = $this->do_zombie_query('   ');
        $this->assertFalse($result->success);
        $this->assertEquals('', $result->problemword);

        // Search for pair of terms.
        $result = $this->do_zombie_query('first document');
        $this->assertTrue($result->success);
        $this->assertEquals(array(1), $this->get_result_ids($result));

        // Search for quoted terms.
        $result = $this->do_zombie_query('"title title"');
        $this->assertTrue($result->success);
        $this->assertEquals(array(2), $this->get_result_ids($result));

        // Negative terms.
        $result = $this->do_zombie_query('title -not');
        $this->assertTrue($result->success);
        $this->assertEquals(array(1, 2), $this->get_result_ids($result));

        // Negative quoted terms.
        $result = $this->do_zombie_query('title -"not frog"');
        $this->assertTrue($result->success);
        $this->assertEquals(array(1, 2, 3), $this->get_result_ids($result));
        $result = $this->do_zombie_query('title -"not a"');
        $this->assertTrue($result->success);
        $this->assertEquals(array(1, 2), $this->get_result_ids($result));

        // Deleting stale results (those which the module responsible can no
        // longer find).
        $before = $DB->count_records('local_ousearch_documents');
        unset(self::$zombiedocuments[4]);
        $result = $this->do_zombie_query('delete');
        $this->assertEquals(1, count(phpunit_util::get_debugging_messages()));
        phpunit_util::reset_debugging();
        $this->assertTrue($result->success);
        $this->assertEquals(array(), $this->get_result_ids($result));
        $this->assertEquals($before - 1,
                $DB->count_records('local_ousearch_documents'));

        // Ranking based on title vs content and number of occurrences.
        $result = $this->do_zombie_query('title');
        $this->assertTrue($result->success);
        $this->assertEquals(array(1, 2, 3), $this->get_result_ids($result));
        $this->assertEquals(2, $result->results[0]->intref1);
        $this->assertEquals(18, $result->results[0]->totalscore);

        // Managing result lists.
        $found = array();
        $dbstart = 0;
        for ($i = 0; $i < 10; $i++) {
            $result = $this->do_zombie_query('bottles', $dbstart);
            $this->assertTrue($result->success);
            $this->assertEquals(10, count($result->results));
            foreach ($result->results as $thing) {
                $found[$thing->intref1] = true;
            }
            $dbstart = $result->dbstart;
        }
        $this->assertEquals(100, count($found));
        $result = $this->do_zombie_query('bottles', $dbstart);
        $this->assertTrue($result->success);
        $this->assertEquals(0, count($result->results));
    }

    /**
     * Reused code to run a query on the zombie documents.
     *
     * @param string $query Query text
     * @param int $dbstart DB start
     * @param int $desired Desired number
     * @return stdClass Search result object
     */
    private function do_zombie_query($query, $dbstart=0, $desired=10) {
        $search = new local_ousearch_search($query);
        $search->set_plugin('test_zombie');
        return $search->query($dbstart, $desired);
    }

    /**
     * Gets sorted list of ids from a search result.
     *
     * @param stdClass $result Search result object
     * @return array Sorted array of ids
     */
    private function get_result_ids($result) {
        $ids = array();
        foreach ($result->results as $thing) {
            $ids[] = $thing->intref1;
        }
        sort($ids);
        return $ids;
    }

    /**
     * Tests the split_words function.
     */
    public function test_split_words() {
        // Standard usage and caps.
        $this->assertEquals(
            local_ousearch_document::split_words('Hello I AM a basic test'),
            array('hello', 'i', 'am', 'a', 'basic', 'test'));
        // Numbers.
        $this->assertEquals(
            local_ousearch_document::split_words('13 2by2'),
            array('13', '2by2'));
        // Ignored and accepted punctuation and whitespace.
        $this->assertEquals(
            local_ousearch_document::split_words('  hello,testing!what\'s&up      there-by   '),
            array('hello', 'testing', 'what\'s', 'up', 'there', 'by'));
        // Unicode letters and nonletter, including one capital for lower-casing.
        $this->assertEquals(
                local_ousearch_document::split_words(html_entity_decode(
                    'caf&eacute; &Aacute;&ecirc;&iuml;&otilde;&ugrave;&emsp;tonight',
                    ENT_QUOTES, 'UTF-8')),
                array(html_entity_decode('caf&eacute;', ENT_QUOTES, 'UTF-8'),
                    html_entity_decode('&aacute;&ecirc;&iuml;&otilde;&ugrave;',
                        ENT_QUOTES, 'UTF-8'),
                    'tonight'));

        // Query mode (keeps " + -).
        $this->assertEquals(
            local_ousearch_document::split_words('"hello there" +frog -doughnut extra-special', true),
            array('"hello', 'there"', '+frog', '-doughnut', 'extra-special'));

        // Position mode: normal.
        $this->assertEquals(
            local_ousearch_document::split_words('hello test', false, true),
            array(array('hello', 'test'), array(0, 6, 10)));
        // Position mode: whitespace.
        $this->assertEquals(
            local_ousearch_document::split_words('    hello    test    ', false, true),
            array(array('hello', 'test'), array(4, 13, 21)));
        // Position mode: unicode (positions in characters).
        $eacute = html_entity_decode('&eacute;', ENT_QUOTES, 'UTF-8');
        $this->assertEquals(
                local_ousearch_document::split_words(
                    "h{$eacute}llo t{$eacute}st", false, true),
                array(array("h{$eacute}llo", "t{$eacute}st"), array(0, 6, 10)));
    }

    /**
     * Tests query parsing.
     */
    public function test_construct_query() {
        // Simple query.
        $this->assertEquals('+frogs -',
                $this->display_terms(new local_ousearch_search('frogs')));
        // Case, whitespace, punctuation.
        $this->assertEquals('+frogs -',
                $this->display_terms(new local_ousearch_search('  FRoGs!!   ')));
        // Requirement (currently unused but).
        $this->assertEquals('+frogs:req -',
                $this->display_terms(new local_ousearch_search('+frogs')));
        // Multiple terms.
        $this->assertEquals('+green,frogs -',
                $this->display_terms(new local_ousearch_search('green frogs')));
        // Negative terms.
        $this->assertEquals('+frogs -green',
                $this->display_terms(new local_ousearch_search('frogs -green')));
        // Quotes.
        $this->assertEquals('+green/frogs -',
                $this->display_terms(new local_ousearch_search('"green frogs"')));
        // Mixed quotes and other.
        $this->assertEquals('+green/frogs,sing -',
                $this->display_terms(new local_ousearch_search('"green frogs" sing')));
        // Mixed quotes and quotes.
        $this->assertEquals('+green/frogs,sing/off/key -',
                $this->display_terms(new local_ousearch_search('"green frogs" "sing off key"')));
        // Mixed quotes and negative quotes.
        $this->assertEquals('+green/frogs -sing/off/key:req',
                $this->display_terms(new local_ousearch_search('"green frogs" -"sing off key"')));
        // Mixed other and negative quotes.
        $this->assertEquals('+frogs -sing/off/key:req',
                $this->display_terms(new local_ousearch_search('frogs -"sing off key"')));
        // Req. quotes (currently unused).
        $this->assertEquals('+green/frogs:req -',
                $this->display_terms(new local_ousearch_search('+"green frogs"')));

        // Hyphens (argh).
        $this->assertEquals('+double/dutch -',
                $this->display_terms(new local_ousearch_search('double-dutch')));
        $this->assertEquals('+it\'s,all,double/dutch,to,me -',
                $this->display_terms(new local_ousearch_search('It\'s all double-dutch to me')));
        $this->assertEquals('+what/double/dutch -',
                $this->display_terms(new local_ousearch_search('"What double-dutch"')));
        $this->assertEquals('+double/dutch/what -',
                $this->display_terms(new local_ousearch_search('"double-dutch what"')));
        $this->assertEquals('+so/called/double/dutch -',
                $this->display_terms(new local_ousearch_search('"so-called double-dutch"')));
        $this->assertEquals('+so/called,double/dutch -',
                $this->display_terms(new local_ousearch_search('so-called double-dutch')));
    }

    /**
     * Displays query terms in a way to make comparisons easy.
     *
     * @param local_ousearch_search $query
     * @return string Query terms as string
     */
    protected function display_terms(local_ousearch_search $query) {
        $input = array($query->terms, $query->negativeterms);
        $output = array();
        foreach ($input as $thing) {
            $value = '';
            foreach ($thing as $term) {
                if ($value !== '') {
                    $value .= ',';
                }
                $value .= implode('/', $term->words);
                if (!empty($term->required)) {
                    $value .= ':req';
                }
            }
            $output[] = $value;
        }
        return '+'.$output[0].' -'.$output[1];
    }
}

/**
 * OU search function for test_zombie plugin.
 * @param stdClass $result Input values
 * @return boolean|stdClass False if failed, otherwise document data
 */
function zombie_ousearch_get_document($result) {
    if (!array_key_exists($result->intref1,
            local_ousearch_test::$zombiedocuments)) {
        return false;
    }
    $page = new stdClass;
    $content = local_ousearch_test::$zombiedocuments[$result->intref1];
    $page->content = $content->content;
    if (isset($content->extra)) {
        $page->extrastrings = $content->extra;
    }
    $page->title = $content->title;
    if (isset($content->hide)) {
        $page->hide = $content->hide;
    }
    if (isset($content->data)) {
        $page->data = $content->data;
    }
    $page->activityname = 'Zombie activity';
    $page->activityurl = 'http://activity/';
    $page->url = 'http://result/';
    return $page;
}

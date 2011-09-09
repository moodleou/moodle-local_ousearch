<?php
defined('MOODLE_INTERNAL') || die;

require_once(dirname(__FILE__) . '/../searchlib.php');

/**
 * Tests search facilities using full database setup.
 */
class searchlib_db_test extends UnitTestCaseUsingDatabase {
    static $zombiedocuments;

    public function setUp() {
        parent::setUp();
        $this->switch_to_test_db();
    }

    function test_everything() {
        // To make the tests faster, all tests happen in the same function
        // so it can use the same tables as here.
        $this->create_test_tables(array('local_ousearch_words',
                'local_ousearch_documents', 'local_ousearch_occurrences'),
                'local/ousearch');
        $this->create_test_tables(array('course', 'groups'),
                'lib');

        // Do individual tests (note: these tests are NOT independent, sorry)
        $this->inner_add_documents();
        $this->inner_update_document();
        $this->inner_delete_documents();
        $this->inner_search();
    }

    private function inner_add_documents() {
        global $DB;

        // Add document with all metadata
        $doc = new local_ousearch_document();
        $doc->init_test('frog');
        $doc->set_group_id(13);
        $doc->set_user_id(666);
        $doc->set_string_ref('ribbit');
        $doc->set_int_refs(3, 7);
        $doc->update('Document title', 'Document text', 4, 0x7fffffff,
            array('extra string 1', 'extra string 2'));

        // Check db tables contain expected values
        $this->assertEqual(1, $DB->count_records('local_ousearch_documents'));
        $record = $DB->get_record('local_ousearch_documents',
                array('stringref' => 'ribbit'));
        unset($record->id);
        $this->assertEqual((object)array('plugin'=>'test_frog',
                'courseid'=>0, 'coursemoduleid'=>0, 'groupid'=>13,
                'userid'=>666, 'stringref'=>'ribbit', 'intref1'=>3,
                'intref2'=>7, 'timemodified'=>4, 'timeexpires'=>0x7fffffff),
                $record);
        // 7 words are: document title text extra string 1 2
        $this->assertEqual(7, $DB->count_records('local_ousearch_words'));
        $this->assertEqual(7, $DB->count_records('local_ousearch_occurrences'));

        // Add another document with the other init method and nothing set
        $doc = new local_ousearch_document();
        $doc->init_module_instance('frog',
            (object)array('id'=>13, 'course'=>666));
        $doc->update('Document title 2', 'Document text 2');

        // Check db tables again
        $this->assertEqual(2, $DB->count_records('local_ousearch_documents'));
        $record = $DB->get_record('local_ousearch_documents',
                array('plugin' => 'mod_frog'));
        unset($record->id);
        $this->assertTrue(time() - $record->timemodified <= 10);
        unset($record->timemodified);
        $this->assertEqual((object)array('plugin'=>'mod_frog',
                'courseid'=>666, 'coursemoduleid'=>13, 'groupid'=>0,
                'userid'=>null, 'stringref'=>null, 'intref1'=>null,
                'intref2'=>null, 'timeexpires'=>null),
                $record);
        $this->assertEqual(7, $DB->count_records('local_ousearch_words'));
        $this->assertEqual(11, $DB->count_records('local_ousearch_occurrences'));
    }

    private function inner_update_document() {
        global $DB;

        // Update first document to change the text a bit
        $doc = new local_ousearch_document();
        $doc->init_test('frog');
        $doc->set_group_id(13);
        $doc->set_user_id(666);
        $doc->set_string_ref('ribbit');
        $doc->set_int_refs(3, 7);
        $doc->update('Document title', 'Document', 4, 0x7fffffff,
            array('extra string 1', 'extra string 2'));

        // Updates existing document, not adds new one
        $this->assertEqual(2, $DB->count_records('local_ousearch_documents'));
        // No added (or removed!) words
        $this->assertEqual(7, $DB->count_records('local_ousearch_words'));
        // One occurrence removed (for 'text' in this doc)
        $this->assertEqual(10, $DB->count_records('local_ousearch_occurrences'));
    }

    private function inner_delete_documents() {
        global $DB;

        // Delete second document
        $doc = new local_ousearch_document();
        $doc->init_module_instance('frog',
            (object)array('id'=>13, 'course'=>666));
        $doc->delete();

        // Only one document left
        $this->assertEqual(1, $DB->count_records('local_ousearch_documents'));
        // No removed words - system never removes words
        $this->assertEqual(7, $DB->count_records('local_ousearch_words'));
        // More occurrences removed
        $this->assertEqual(6, $DB->count_records('local_ousearch_occurrences'));

        // Create a couple more documents for a specific course-module
        $doc = new local_ousearch_document();
        $cm = (object)array('id'=>14, 'course'=>667);
        $doc->init_module_instance('frog', $cm);
        $doc->set_int_refs(7);
        $doc->update('Document title', 'Document');
        $doc = new local_ousearch_document();
        $doc->init_module_instance('frog', $cm);
        $doc->set_int_refs(3);
        $doc->update('Document title', 'Document');

        // 3 documents now
        $this->assertEqual(3, $DB->count_records('local_ousearch_documents'));

        // Delete all for cm
        local_ousearch_document::delete_module_instance_data($cm);

        // 1 document now
        $this->assertEqual(1, $DB->count_records('local_ousearch_documents'));
    }

    private function inner_search() {
        global $DB;

        // We are going to search documents within test_zombie, so let's create
        // a bunch
        self::$zombiedocuments = array(
            1 => (object)array(
                'title'=>'Document title',
                'content'=>'First zombie document'),
            2 => (object)array(
                'title'=>'Another title',
                'content'=>'Title title first'),
            3 => (object)array(
                'title'=>'Document title',
                'content'=>'Not a zombie document title'),
            4 => (object)array(
                'title'=>'Delete me',
                'content'=>'Delete me'),
            100 => (object)array(
                'title'=>'Bottle quantity',
                'content'=>'There are this many bottles on the wall: 0'),
        );
        for($i=101; $i<=199; $i++) {
            self::$zombiedocuments[$i] = self::$zombiedocuments[100];
            self::$zombiedocuments[$i]->content = str_replace(': 0',
                ': ' . ($i-100), self::$zombiedocuments[$i]->content);
        }
        foreach (self::$zombiedocuments as $key=>$content) {
            $doc = new local_ousearch_document();
            $doc->init_test('zombie');
            $doc->set_int_refs($key);
            $extra = isset($content->extra) ? $content->extra : null;
            $doc->update($content->title, $content->content, null, null,
                    $extra);
        }

        // Search for single unique term
        $result = $this->do_zombie_query('not');
        $this->assertTrue($result->success);
        $this->assertEqual(array(3), $this->get_result_ids($result));

        // Search for nonexistent word
        $result = $this->do_zombie_query('xyzzy');
        $this->assertFalse($result->success);
        $this->assertEqual('xyzzy', $result->problemword);

        // Search for nothing
        $result = $this->do_zombie_query('   ');
        $this->assertFalse($result->success);
        $this->assertEqual('', $result->problemword);

        // Search for pair of terms
        $result = $this->do_zombie_query('first document');
        $this->assertTrue($result->success);
        $this->assertEqual(array(1), $this->get_result_ids($result));

        // Search for quoted terms
        $result = $this->do_zombie_query('"title title"');
        $this->assertTrue($result->success);
        $this->assertEqual(array(2), $this->get_result_ids($result));

        // Negative terms
        $result = $this->do_zombie_query('title -not');
        $this->assertTrue($result->success);
        $this->assertEqual(array(1, 2), $this->get_result_ids($result));

        // Negative quoted terms
        $result = $this->do_zombie_query('title -"not frog"');
        $this->assertTrue($result->success);
        $this->assertEqual(array(1, 2, 3), $this->get_result_ids($result));
        $result = $this->do_zombie_query('title -"not a"');
        $this->assertTrue($result->success);
        $this->assertEqual(array(1, 2), $this->get_result_ids($result));

        // Deleting stale results (those which the module responsible can no
        // longer find)
        $before = $DB->count_records('local_ousearch_documents');
        unset(self::$zombiedocuments[4]);
        $result = $this->do_zombie_query('delete');
        $this->assertTrue($result->success);
        $this->assertEqual(array(), $this->get_result_ids($result));
        $this->assertEqual($before-1,
                $DB->count_records('local_ousearch_documents'));

        // Ranking based on title vs content and number of occurrences
        $result = $this->do_zombie_query('title');
        $this->assertTrue($result->success);
        $this->assertEqual(array(1, 2, 3), $this->get_result_ids($result));
        $this->assertEqual(2, $result->results[0]->intref1);
        $this->assertEqual(18, $result->results[0]->totalscore);

        // Managing result lists
        $found = array();
        $dbstart = 0;
        for($i=0; $i<10; $i++) {
            $result = $this->do_zombie_query('bottles', $dbstart);
            $this->assertTrue($result->success);
            $this->assertEqual(10, count($result->results));
            foreach($result->results as $thing) {
                $found[$thing->intref1] = true;
            }
            $dbstart = $result->dbstart;
        }
        $this->assertEqual(100, count($found));
        $result = $this->do_zombie_query('bottles', $dbstart);
        $this->assertTrue($result->success);
        $this->assertEqual(0, count($result->results));
    }

    private function do_zombie_query($query, $dbstart=0, $desired=10) {
        $search = new local_ousearch_search($query);
        $search->set_plugin('test_zombie');
        return $search->query($dbstart, $desired);
    }

    private function get_result_ids($result) {
        $ids = array();
        foreach($result->results as $thing) {
            $ids[] = $thing->intref1;
        }
        sort($ids);
        return $ids;
    }
}

function zombie_ousearch_get_document($result) {
    if (!array_key_exists($result->intref1,
            searchlib_db_test::$zombiedocuments)) {
        return false;
    }
    $page = new stdClass;
    $content = searchlib_db_test::$zombiedocuments[$result->intref1];
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

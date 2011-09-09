<?php
defined('MOODLE_INTERNAL') || die;

require_once(dirname(__FILE__) . '/../searchlib.php');

class searchlib_nodb_test extends UnitTestCase {
    function test_split_words() {
        // Standard usage and caps
        $this->assertEqual(
            local_ousearch_document::split_words('Hello I AM a basic test'),
            array('hello', 'i', 'am', 'a', 'basic', 'test'));
        // Numbers
        $this->assertEqual(
            local_ousearch_document::split_words('13 2by2'),
            array('13', '2by2'));
        // Ignored and accepted punctuation and whitespace
        $this->assertEqual(
            local_ousearch_document::split_words('  hello,testing!what\'s&up      there-by   '),
            array('hello', 'testing', 'what\'s', 'up', 'there', 'by'));
        // Unicode letters and nonletter, including one capital for lower-casing
        $this->assertEqual(
                local_ousearch_document::split_words(html_entity_decode(
                    'caf&eacute; &Aacute;&ecirc;&iuml;&otilde;&ugrave;&emsp;tonight',
                    ENT_QUOTES, 'UTF-8')),
                array(html_entity_decode('caf&eacute;', ENT_QUOTES, 'UTF-8'),
                    html_entity_decode('&aacute;&ecirc;&iuml;&otilde;&ugrave;',
                        ENT_QUOTES, 'UTF-8'),
                    'tonight'));

        // Query mode (keeps " + -)
        $this->assertEqual(
            local_ousearch_document::split_words('"hello there" +frog -doughnut extra-special', true),
            array('"hello', 'there"', '+frog', '-doughnut', 'extra-special'));

        // Position mode: normal
        $this->assertEqual(
            local_ousearch_document::split_words('hello test', false, true),
            array(array('hello', 'test'), array(0, 6, 10)));
        // Position mode: whitespace
        $this->assertEqual(
            local_ousearch_document::split_words('    hello    test    ', false, true),
            array(array('hello', 'test'), array(4, 13, 21)));
        // Position mode: unicode (positions in characters)
        $eacute = html_entity_decode('&eacute;', ENT_QUOTES, 'UTF-8');
        $this->assertEqual(
                local_ousearch_document::split_words(
                    "h{$eacute}llo t{$eacute}st", false, true),
                array(array("h{$eacute}llo", "t{$eacute}st"), array(0, 6, 10)));
    }

    function test_construct_query() {
        // Simple query
        $this->assertEqual($this->display_terms(new local_ousearch_search('frogs')),
            '+frogs -');
        // Case, whitespace, punctuation
        $this->assertEqual($this->display_terms(new local_ousearch_search('  FRoGs!!   ')),
            '+frogs -');
        // Requirement (currently unused but)
        $this->assertEqual($this->display_terms(new local_ousearch_search('+frogs')),
            '+frogs:req -');
        // Multiple terms
        $this->assertEqual($this->display_terms(new local_ousearch_search('green frogs')),
            '+green,frogs -');
        // Negative terms
        $this->assertEqual($this->display_terms(new local_ousearch_search('frogs -green')),
            '+frogs -green');
        // Quotes
        $this->assertEqual($this->display_terms(new local_ousearch_search('"green frogs"')),
            '+green/frogs -');
        // Mixed quotes and other
        $this->assertEqual($this->display_terms(new local_ousearch_search('"green frogs" sing')),
            '+green/frogs,sing -');
        // Mixed quotes and quotes
        $this->assertEqual($this->display_terms(new local_ousearch_search('"green frogs" "sing off key"')),
            '+green/frogs,sing/off/key -');
        // Mixed quotes and negative quotes
        $this->assertEqual($this->display_terms(new local_ousearch_search('"green frogs" -"sing off key"')),
            '+green/frogs -sing/off/key:req');
        // Mixed other and negative quotes
        $this->assertEqual($this->display_terms(new local_ousearch_search('frogs -"sing off key"')),
            '+frogs -sing/off/key:req');
        // Req. quotes (currently unused)
        $this->assertEqual($this->display_terms(new local_ousearch_search('+"green frogs"')),
            '+green/frogs:req -');

        // Hyphens (argh)
        $this->assertEqual($this->display_terms(new local_ousearch_search('double-dutch')),
            '+double/dutch -');
        $this->assertEqual($this->display_terms(new local_ousearch_search('It\'s all double-dutch to me')),
            '+it\'s,all,double/dutch,to,me -');
        $this->assertEqual($this->display_terms(new local_ousearch_search('"What double-dutch"')),
            '+what/double/dutch -');
        $this->assertEqual($this->display_terms(new local_ousearch_search('"double-dutch what"')),
            '+double/dutch/what -');
        $this->assertEqual($this->display_terms(new local_ousearch_search('"so-called double-dutch"')),
            '+so/called/double/dutch -');
        $this->assertEqual($this->display_terms(new local_ousearch_search('so-called double-dutch')),
            '+so/called,double/dutch -');
    }

    function display_terms($query) {
        $input = array($query->terms,$query->negativeterms);
        $output = array();
        foreach ($input as $thing) {
            $value = '';
            foreach ($thing as $term) {
                if($value !== '') {
                    $value .= ',';
                }
                $value.=implode('/',$term->words);
                if(!empty($term->required)) {
                    $value .= ':req';
                }
            }
            $output[] = $value;
        }
        return '+'.$output[0].' -'.$output[1];
    }
}

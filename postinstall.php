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
 * On install, builds the search data for all existing content.
 *
 * @package local_ousearch
 * @copyright 2014 The Open University
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require(__DIR__ . '/../../config.php');

// Check administrator or similar.
require_login();
require_capability('moodle/site:config', context_system::instance());

$url = new moodle_url('/local/ousearch/postinstall.php');
$PAGE->set_url($url);
$PAGE->set_context(context_system::instance());

echo $OUTPUT->header();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && confirm_sesskey()) {
    require_once(__DIR__ . '/searchlib.php');
    $plugins = get_plugin_list_with_function('mod', 'ousearch_update_all');
    foreach ($plugins as $plugin => $fn) {
        $fn(true);
    }
} else {
    echo $OUTPUT->box(get_string('postinstall', 'local_ousearch'));
    echo $OUTPUT->single_button($url, get_string('continue'));
}

echo $OUTPUT->footer();

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
 * Database upgrades.
 *
 * @package local_ousearch
 * @copyright 2015 The Open University
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

function xmldb_local_ousearch_upgrade($oldversion=0) {
    global $DB;

    if ($oldversion < 2015010500) {
        // Change the collation for MySQL. Otherwise it thinks various characters
        // are the same as each other, causing problems.
        if ($DB->get_dbfamily() == 'mysql') {
            $DB->execute("ALTER TABLE {local_ousearch_words} CONVERT TO CHARACTER SET utf8 COLLATE utf8_bin");
        }

        // Savepoint reached.
        upgrade_plugin_savepoint(true, 2015010500, 'local', 'ousearch');
    }

    return true;
}

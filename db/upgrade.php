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

    $dbman = $DB->get_manager();

    if ($oldversion < 2015010500) {
        // Change the collation for MySQL. Otherwise it thinks various characters
        // are the same as each other, causing problems.
        if ($DB->get_dbfamily() == 'mysql') {
            $DB->execute("ALTER TABLE {local_ousearch_words} CONVERT TO CHARACTER SET utf8 COLLATE utf8_bin");
        }

        // Savepoint reached.
        upgrade_plugin_savepoint(true, 2015010500, 'local', 'ousearch');
    }

    if ($oldversion < 2015050100) {
        // Loop to create the tables for each year.
        foreach (array('2011', '2012', '2013', '2014', '2015', '2016', '2017',
                '2018', '2019', '2020') as $year) {
            // Create docs table.
            $table = new xmldb_table('local_ousearch_docs_' . $year);
            $table->add_field('id', XMLDB_TYPE_INTEGER, '9', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
            $table->add_field('plugin', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null);
            $table->add_field('courseid', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
            $table->add_field('coursemoduleid', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
            $table->add_field('groupid', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
            $table->add_field('userid', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
            $table->add_field('stringref', XMLDB_TYPE_CHAR, '255', null, null, null, null);
            $table->add_field('intref1', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
            $table->add_field('intref2', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
            $table->add_field('timemodified', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
            $table->add_field('timeexpires', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
            $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
            $table->add_key('groupid', XMLDB_KEY_FOREIGN, array('groupid'), 'groups', array('id'));
            $table->add_key('userid', XMLDB_KEY_FOREIGN, array('userid'), 'user', array('id'));
            $table->add_index('courseid_coursemoduleid', XMLDB_INDEX_NOTUNIQUE, array('courseid', 'coursemoduleid'));
            $table->add_index('plugin', XMLDB_INDEX_NOTUNIQUE, array('plugin'));
            if (!$dbman->table_exists($table)) {
                $dbman->create_table($table);
            }

            // Create occurrences table.
            $table = new xmldb_table('local_ousearch_occurs_' . $year);
            $table->add_field('wordid', XMLDB_TYPE_INTEGER, '9', null, XMLDB_NOTNULL, null, null);
            $table->add_field('documentid', XMLDB_TYPE_INTEGER, '9', null, XMLDB_NOTNULL, null, null);
            $table->add_field('score', XMLDB_TYPE_INTEGER, '9', null, XMLDB_NOTNULL, null, null);
            $table->add_key('primary', XMLDB_KEY_PRIMARY, array('wordid', 'documentid'));
            $table->add_key('documentid', XMLDB_KEY_FOREIGN, array('documentid'), 'local_ousearch_documents', array('id'));
            if (!$dbman->table_exists($table)) {
                $dbman->create_table($table);
            }
        }

        // Define table local_ousearch_courseyears to be created.
        $table = new xmldb_table('local_ousearch_courseyears');

        // Adding fields to table local_ousearch_courseyears.
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('courseid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('year', XMLDB_TYPE_INTEGER, '4', null, XMLDB_NOTNULL, null, null);
        $table->add_field('oldyears', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null);

        // Adding keys to table local_ousearch_courseyears.
        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
        $table->add_key('courseid', XMLDB_KEY_FOREIGN_UNIQUE, array('courseid'), 'course', array('id'));

        // Conditionally launch create table for local_ousearch_courseyears.
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        // Ousearch savepoint reached.
        upgrade_block_savepoint(true, 2015050100, 'ousearch');
    }

    return true;
}

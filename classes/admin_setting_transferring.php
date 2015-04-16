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
 * Custom admin setting so that you can turn on the 'split tables' process
 * but you can't turn it off.
 *
 * @package local_ousearch
 * @copyright 2015 The Open University
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_ousearch;

/**
 * Custom admin setting so that you can turn on the 'split tables' process
 * but you can't turn it off.
 *
 * The reason you can't turn it off is that we haven't implemented code to
 * move data back from the year tables to the single table, which would be
 * necessary because during the transferring process search data might go into
 * the year tables.
 */
class admin_setting_transferring extends \admin_setting_configcheckbox {
    /**
     * Constructs setting.
     */
    public function __construct() {
        parent::__construct('local_ousearch/yearsenabled',
                get_string('yearsenabled', 'local_ousearch'),
                get_string('yearsenabled_desc', 'local_ousearch'),
                year_tables::ENABLED_OFF,
                year_tables::ENABLED_TRANSFERRING, year_tables::ENABLED_OFF);
    }

    public function output_html($data, $query = '') {
        global $DB;
        $defaultinfo = get_string('checkboxno', 'admin');

        if ((string)$data === (string)year_tables::ENABLED_ON) {
            $html = \html_writer::div(
                    get_string('yearsenabled_on', 'local_ousearch'),
                    'local_ousearch_yearsenabled defaultsnext');
        } else if ((string)$data === (string)year_tables::ENABLED_TRANSFERRING) {
            // Show transferring progress. This database query only occurs
            // during the transferring phase.
            $transferring = get_config('local_ousearch', year_tables::CONFIG_TRANSFERRING_COURSE);
            if (!$transferring) {
                $pc = '0';
            } else {
                $progress = $DB->get_record_sql(
                        'SELECT (SELECT COUNT(1) FROM {course} WHERE id < ?) AS done,
                        (SELECT COUNT(1) FROM {course}) AS total',
                        array($transferring, $transferring));
                $pc = round(100.0 * $progress->done / $progress->total, 1);
            }
            $html = \html_writer::div(
                    get_string('yearsenabled_transferring', 'local_ousearch', $pc),
                    'local_ousearch_yearsenabled defaultsnext');
        } else {
            // This is similar to standard checkbox except I didn't support it
            // being shown as checked.
            $html = \html_writer::div(
                    \html_writer::empty_tag('input', array('type' => 'hidden',
                        'name' => $this->get_full_name(), 'value' => $this->no)) .
                    \html_writer::empty_tag('input', array('type' => 'checkbox',
                        'name' => $this->get_full_name(), 'value' => $this->yes)),
                    'form-checkbox defaultsnext');
        }

        return format_admin_setting($this, $this->visiblename, $html,
                $this->description, true, '', $defaultinfo, $query);
    }
}

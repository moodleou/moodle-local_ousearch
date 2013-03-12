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
 * Admin settings.
 *
 * @package local_ousearch
 * @copyright 2013 The Open University
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

if ($hassiteconfig) { // needs condition or error on login page
    $settings = new admin_settingpage(
            'local_ousearch', get_string('ousearch', 'local_ousearch'));
    $ADMIN->add('localplugins', $settings);

    $settings->add(new admin_setting_configtext(
            'local_ousearch/remote', get_string('remote', 'local_ousearch'),
            get_string('configremote', 'local_ousearch'), '', PARAM_RAW));

    $settings->add(new admin_setting_configtext(
            'local_ousearch_maxterms', get_string('maxterms', 'local_ousearch'),
            get_string('maxterms_desc', 'local_ousearch'), '20', PARAM_INT));
}

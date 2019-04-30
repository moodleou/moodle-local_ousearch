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
 * @copyright 2015 The Open University
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

if ($hassiteconfig) {
    $settings = new admin_settingpage(
            'local_ousearch', get_string('ousearch', 'local_ousearch'));
    $ADMIN->add('localplugins', $settings);

    $settings->add(new admin_setting_configcheckbox(
            'local_ousearch/ousearchindexingdisabled', get_string('ousearchindexingdisabled', 'local_ousearch'),
            get_string('ousearchindexingdisabled_desc', 'local_ousearch'), '0'));

    $settings->add(new admin_setting_configtext(
            'local_ousearch/remote', get_string('remote', 'local_ousearch'),
            get_string('configremote', 'local_ousearch'), '', PARAM_RAW));

    $settings->add(new admin_setting_configtext(
            'local_ousearch_maxterms', get_string('maxterms', 'local_ousearch'),
            get_string('maxterms_desc', 'local_ousearch'), '20', PARAM_INT));

    $settings->add(new \local_ousearch\admin_setting_transferring());

    // Time limits for cron operations.
    $options = array(
        60 => get_string('numseconds', '', 60),
        180 => get_string('numminutes', '', 3),
        300 => get_string('numminutes', '', 5),
        600 => get_string('numminutes', '', 10),
        900 => get_string('numminutes', '', 15),
        1200 => get_string('numminutes', '', 20)
    );
    $settings->add(new admin_setting_configselect('local_ousearch/splittimelimit',
            get_string('splittimelimit', 'local_ousearch'),
            get_string('splittimelimit_desc', 'local_ousearch'), 600, $options));
    $settings->add(new admin_setting_configselect('local_ousearch/datetimelimit',
            get_string('datetimelimit', 'local_ousearch'),
            get_string('datetimelimit_desc', 'local_ousearch'), 600, $options));
}

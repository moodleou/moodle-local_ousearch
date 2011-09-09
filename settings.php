<?php
defined('MOODLE_INTERNAL') || die;

if ($hassiteconfig) { // needs condition or error on login page
    $settings = new admin_settingpage(
            'local_ousearch', get_string('ousearch', 'local_ousearch'));
    $ADMIN->add('localplugins', $settings);

    $plugin = new stdClass;
    require($CFG->dirroot . '/local/ousearch/version.php');
    $settings->add(new admin_setting_heading('ousearch_version', '',
            get_string('displayversion', 'local_ousearch',
            $plugin->displayversion)));

    $settings->add(new admin_setting_configtext(
            'local_ousearch/remote', get_string('remote', 'local_ousearch'),
            get_string('configremote', 'local_ousearch'), '', PARAM_RAW));
}

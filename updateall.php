<?php
// For admins to use to force updates of search indexes on a course. There
// is no user interface for this.

require_once('../../config.php');
require_once('searchlib.php');
require_capability('moodle/site:config', context_system::instance());

$courseid = optional_param('course', 0, PARAM_INT);
$module = required_param('module',PARAM_ALPHA);
$confirm = optional_param('confirm', 0, PARAM_INT);

require_once($CFG->dirroot.'/mod/'.$module.'/lib.php');
$function = $module . '_ousearch_update_all';
print $OUTPUT->header();

print $OUTPUT->heading(
    get_string('reindex', 'local_ousearch',
        (object)array('module'=>$module, 'courseid'=>$courseid)));

if ($courseid == 0 && $confirm == 0) {
    $params = array('courseid' => 0, 'confirm' => 1, 'module' => $module);
    $go = new moodle_url('/local/ousearch/updateall.php', $params);
    print $OUTPUT->confirm('Confirm update to all courses', $go, $CFG->wwwroot);
    print $OUTPUT->footer();
    exit;
}

print "<ul>";

$function(true,$courseid);

print "</ul>";

print $OUTPUT->footer();
?>

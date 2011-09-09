<?php
// For admins to use to force updates of search indexes on a course. There
// is no user interface for this.

require_once('../../config.php');
require_once('searchlib.php');
require_capability('moodle/site:config', get_context_instance(CONTEXT_SYSTEM));

$courseid = required_param('course',PARAM_INT);
$module = required_param('module',PARAM_ALPHA);

require_once($CFG->dirroot.'/mod/'.$module.'/lib.php');
$function = $module . '_ousearch_update_all';
print $OUTPUT->header();

print $OUTPUT->heading(
    get_string('reindex', 'local_ousearch',
        (object)array('module'=>$module, 'courseid'=>$courseid)));

print "<ul>";

$function(true,$courseid);

print "</ul>";

print $OUTPUT->footer();
?>

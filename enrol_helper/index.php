<?php
require_once(__DIR__.'/../../../config.php');
require_once($CFG->libdir.'/adminlib.php');
require_once(__DIR__.'/classes/form/update_cohort_enrolments_form.php');

// use \tool_enrol_helper\task\update_cohort_enrolments_task;

admin_externalpage_setup('tool_enrol_helper');

$title = get_string('updatecohortenrolments', 'tool_enrol_helper');
$PAGE->set_title($title);
$PAGE->set_heading($title);

echo $OUTPUT->header();
echo $OUTPUT->heading($title);

// Find cohort enrolments without groups and with status=0 (enabled).
$enrolments = $DB->get_records('enrol', array(
    'enrol' => 'cohort',
    'customint2' => 0,
    'status' => 0
));

$form = new update_cohort_enrolments_form(null, array('enrolments' => $enrolments));

if ($form->is_cancelled()) {
    redirect(new moodle_url('/admin/category.php', array('category' => 'tools')));
} else if ($data = $form->get_data()) {
    require_sesskey();
    
    $enrolids = isset($data->enrolids) ? array_keys($data->enrolids) : array();
    
    if (!empty($enrolids)) {
        $task = new \tool_enrol_helper\task\update_cohort_enrolments_task();
        $task->set_custom_data(array('enrolids' => $enrolids));
        \core\task\manager::queue_adhoc_task($task);
        
        echo $OUTPUT->notification(get_string('taskqueued', 'tool_enrol_helper'), 'notifysuccess');
    }
}

$form->display();

echo $OUTPUT->footer();

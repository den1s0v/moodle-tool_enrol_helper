<?php
defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir.'/formslib.php');

class update_cohort_enrolments_form extends moodleform {
    
    public function definition() {
        global $DB, $OUTPUT;
        
        $mform = $this->_form;
        $enrolments = $this->_customdata['enrolments'];
        
        if (empty($enrolments)) {
            $mform->addElement('html', $OUTPUT->notification(
                get_string('nochangesrequired', 'tool_enrol_helper'), 'notifysuccess'));
            return;
        }
        
        $mform->addElement('html', $OUTPUT->notification(
            get_string('enrolmentsfound', 'tool_enrol_helper', count($enrolments)), 'notifymessage'));
        
        $table = new html_table();
        $table->head = array(
            get_string('course', 'tool_enrol_helper'),
            get_string('cohort', 'tool_enrol_helper'),
            get_string('status', 'tool_enrol_helper'),
            get_string('action', 'tool_enrol_helper')
        );
        
        foreach ($enrolments as $enrol) {
            $course = $DB->get_record('course', array('id' => $enrol->courseid));
            $cohort = $DB->get_record('cohort', array('id' => $enrol->customint1));
            
            $checkbox = html_writer::checkbox(
                "enrolids[{$enrol->id}]", 
                1, 
                true,
                get_string('creategroup', 'tool_enrol_helper'),
                array('class' => 'enrolcheckbox')
            );
            
            $table->data[] = array(
                $course->fullname,
                $cohort->name,
                $enrol->status == 0 ? 'Enabled' : 'Disabled',
                $checkbox
            );
        }
        
        $mform->addElement('html', html_writer::table($table));
        $mform->addElement('submit', 'submit', get_string('execute', 'tool_enrol_helper'));
    }
}

<?php
defined('MOODLE_INTERNAL') || die();

// namespace \tool_enrol_helper\task;

class tool_enrol_helper_update_cohort_enrolments_task extends \core\task\adhoc_task {
    
    public function execute() {
        global $DB, $CFG;
        
        require_once($CFG->dirroot.'/group/lib.php');
        
        $data = $this->get_custom_data();
        $enrolids = $data->enrolids;
        $count = count($enrolids);
        $processed = 0;
        
        foreach ($enrolids as $enrolid) {
            $enrol = $DB->get_record('enrol', array('id' => $enrolid));
            if (!$enrol || $enrol->enrol != 'cohort') {
                continue;
            }
            
            $cohort = $DB->get_record('cohort', array('id' => $enrol->customint1));
            if (!$cohort) {
                continue;
            }
            
            // Create group with cohort name
            $groupdata = new stdClass();
            $groupdata->courseid = $enrol->courseid;
            $groupdata->name = $cohort->name;
            $groupid = groups_create_group($groupdata);
            
            // Update enrolment with new groupid
            $DB->update_record('enrol', array(
                'id' => $enrol->id,
                'customint2' => $groupid
            ));
            
            $processed++;
            $this->update_progress($processed, $count);
        }
    }
    
    protected function update_progress($processed, $total) {
        if ($processed % 10 == 0 || $processed == $total) {
            mtrace(get_string('progress', 'tool_enrol_helper', "$processed/$total"));
        }
    }
}

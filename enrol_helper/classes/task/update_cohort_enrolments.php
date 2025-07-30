<?php
namespace tool_enrol_helper\task;

defined('MOODLE_INTERNAL') || die();

class update_cohort_enrolments extends \core\task\adhoc_task {
    
    public function execute() {
        global $DB, $CFG;
        
        require_once($CFG->dirroot.'/group/lib.php');
        require_once($CFG->dirroot.'/cohort/lib.php');
        require_once($CFG->dirroot.'/enrol/cohort/locallib.php');

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

            // 1. Проверяем существующую группу.
            $groupid = $this->get_or_create_group($enrol->courseid, $cohort->name);
            
            // 2. Обновляем способ зачисления.
            if ($enrol->customint2 != $groupid) {
                $DB->update_record('enrol', array(
                    'id' => $enrol->id,
                    'customint2' => $groupid
                ));
            }
            
            // 3. Синхронизируем состав группы.
            $this->sync_group_members($cohort->id, $enrol->courseid, $groupid);

            $processed++;
            $this->update_progress($processed, $count);
        }
    }
    
    protected function get_or_create_group($courseid, $groupname) {
        global $DB;
        
        // Ищем группу с таким именем в курсе.
        if ($group = $DB->get_record('groups', 
            array('courseid' => $courseid, 'name' => $groupname), 'id')) {
            return $group->id;
        }
        
        // Если не найдена - создаём новую.
        $groupdata = new \stdClass();
        $groupdata->courseid = $courseid;
        $groupdata->name = $groupname;
        return groups_create_group($groupdata);
    }
    
    protected function sync_group_members($cohortid, $courseid, $groupid) {
        global $DB;
        
        // Получаем текущих участников группы.
        $current_members = $DB->get_fieldset_select(
            'groups_members', 
            'userid', 
            'groupid = :groupid', 
            array('groupid' => $groupid)
        );
        $current_members = array_flip($current_members);
        
        // Получаем участников когорты.
        $cohort_members = $DB->get_fieldset_select(
            'cohort_members', 
            'userid', 
            'cohortid = :cohortid', 
            array('cohortid' => $cohortid)
        );
        
        // Добавляем отсутствующих участников.
        foreach ($cohort_members as $userid) {
            if (!isset($current_members[$userid])) {
                // No need to ... Check if user is enrolled in the course.
                if (true /* $this->is_user_enrolled($userid, $courseid) */) {
                    groups_add_member($groupid, $userid);
                }
            }
        }
        
        // Удаляем участников, которых нет в когорте (опционально)
        // foreach ($current_members as $userid => $_) {
        //     if (!in_array($userid, $cohort_members)) {
        //         groups_remove_member($groupid, $userid);
        //     }
        // }
    }
    
    protected function is_user_enrolled($userid, $courseid) {
        global $DB;
        
        $context = \context_course::instance($courseid);
        return is_enrolled($context, $userid);
    }
    
    protected function update_progress($processed, $total) {
        if ($processed % 10 == 0 || $processed == $total) {
            mtrace(get_string('progress', 'tool_enrol_helper', "$processed/$total"));
        }
    }
}

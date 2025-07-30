<?php
defined('MOODLE_INTERNAL') || die();

function tool_enrol_helper_extend_navigation_category_settings($navigation, $context) {
    global $PAGE;
    
    if (has_capability('moodle/site:config', context_system::instance())) {
        $url = new moodle_url('/admin/tool/enrol_helper/index.php');
        $navigation->add(
            get_string('updatecohortenrolments', 'tool_enrol_helper'),
            $url,
            navigation_node::TYPE_SETTING,
            null,
            'tool_enrol_helper',
            new pix_icon('i/settings', '')
        );
    }
}

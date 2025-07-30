<?php
defined('MOODLE_INTERNAL') || die();

if ($hassiteconfig) {
    $ADMIN->add('tools', new admin_externalpage(
        'tool_enrol_helper',
        get_string('pluginname', 'tool_enrol_helper'),
        new moodle_url('/admin/tool/enrol_helper/index.php')
    ));
}

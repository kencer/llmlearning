<?php
defined('MOODLE_INTERNAL') || die();

if ($hassiteconfig) {

    $settings = new admin_settingpage('local_llmlearning', get_string('pluginname', 'local_llmlearning'));

    $settings->add(new admin_setting_configtext(
        'local_llmlearning/apikey',
        'LLM API Key',
        'Enter your LLM API Key',
        ''
    ));

    $settings->add(new admin_setting_configtext(
        'local_llmlearning/model',
        'LLM Model',
        'Model name (e.g., gpt-5)',
        'gpt-5'
    ));

    $ADMIN->add('localplugins', $settings);
}
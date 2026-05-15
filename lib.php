<?php
defined('MOODLE_INTERNAL') || die();

function local_llmlearning_extend_navigation(global_navigation $nav) {

    if (has_capability('local/llmlearning:viewdashboard', context_system::instance())) {

        $nav->add(
            'LLM Dashboard',
            new moodle_url('/local/llmlearning/dashboard.php'),
            navigation_node::TYPE_CUSTOM,
            null,
            'llmlearning_dashboard'
        );
    }
}
// Future hooks go here.
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

/**
 * Extend course navigation.
 */
function local_llmlearning_extend_navigation_course(
    navigation_node $navigation,
    stdClass $course,
    context_course $context
) {

    if (has_capability('local/llmlearning:view', $context)) {

        $url = new moodle_url(
            '/local/llmlearning/index.php',
            ['courseid' => $course->id]
        );

        $navigation->add(
            get_string('pluginname', 'local_llmlearning'),
            $url,
            navigation_node::TYPE_CUSTOM,
            null,
            'llmlearning',
            new pix_icon('i/chat', '')
        );
    }
}
// Future hooks go here.
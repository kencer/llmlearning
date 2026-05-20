<?php
require('../../config.php');

$courseid = optional_param('courseid', SITEID, PARAM_INT);

$course = get_course($courseid);

//require_login();
require_login($course);

//$context = context_system::instance();
$context = context_course::instance($course->id);

require_capability('local/llmlearning:view', $context);

$PAGE->set_url('/local/llmlearning/index.php');
$PAGE->set_context($context);
$PAGE->set_course($course);
//$PAGE->set_title('LLM Learning');
//$PAGE->set_heading('LLM Learning Companion');
$PAGE->set_title('AI Learning Companion');
$PAGE->set_heading($course->fullname);

$PAGE->requires->js_call_amd('local_llmlearning/chat', 'init', [SITEID]);

echo $OUTPUT->header();

// Render Mustache template
echo $OUTPUT->render_from_template('local_llmlearning/chat', []);

echo $OUTPUT->footer();
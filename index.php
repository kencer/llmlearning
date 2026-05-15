<?php
require('../../config.php');

require_login();

$context = context_system::instance();
require_capability('local/llmlearning:view', $context);

$PAGE->set_url('/local/llmlearning/index.php');
$PAGE->set_title('LLM Learning');
$PAGE->set_heading('LLM Learning Companion');

$PAGE->requires->js_call_amd('local_llmlearning/chat', 'init', [SITEID]);

echo $OUTPUT->header();

// Render Mustache template
echo $OUTPUT->render_from_template('local_llmlearning/chat', []);

echo $OUTPUT->footer();
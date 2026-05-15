<?php
require('../../config.php');

require_login();

$context = context_system::instance();
require_capability('local/llmlearning:viewdashboard', $context);

$PAGE->set_url('/local/llmlearning/dashboard.php');
$PAGE->set_title('LLM Learning Dashboard');
$PAGE->set_heading('Instructor Dashboard');

echo $OUTPUT->header();

use local_llmlearning\service\dashboard_service;

$data = dashboard_service::get_students_summary();

echo $OUTPUT->render_from_template('local_llmlearning/dashboard', [
    'students' => $data
]);

echo $OUTPUT->footer();
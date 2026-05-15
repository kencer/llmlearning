<?php
require('../../config.php');

require_login();

header('Content-Type: application/json');

$action = required_param('action', PARAM_ALPHA);
$message = optional_param('message', '', PARAM_TEXT);
$courseid = optional_param('courseid', SITEID, PARAM_INT);

$context = context_system::instance();
require_capability('local/llmlearning:view', $context);

use local_llmlearning\service\chat_service;

try {

    if ($action === 'send') {

        $result = chat_service::process_message(
            $USER->id,
            $courseid,
            $message
        );

        echo json_encode([
            'status' => 'success',
            'data' => $result
        ]);

        exit;
    }

    if ($action === 'history') {

        $history = chat_service::get_history(
            $USER->id,
            $courseid
        );

        echo json_encode([
            'status' => 'success',
            'data' => array_values($history)
        ]);

        exit;
    }

    echo json_encode([
        'status' => 'error',
        'message' => 'Invalid action'
    ]);

} catch (\Throwable $e) {

    error_log('LLMLEARNING ERROR: ' . $e->getMessage());
    error_log($e->getTraceAsString());

    http_response_code(500);

    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);

    exit;
}
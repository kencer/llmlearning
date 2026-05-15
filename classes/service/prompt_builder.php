<?php
namespace local_llmlearning\service;

defined('MOODLE_INTERNAL') || die();

class prompt_builder {

    public static function build($history, $currentmessage, $action = null) {

        $context = "";

        $recent = array_slice($history, -5);

        foreach ($recent as $item) {
            $context .= "Student: " . $item->userinput . "\n";
            $context .= "Tutor: " . $item->agentresponse . "\n";
        }

        $prompt = "
You are an AI tutor designed to promote exploratory learning and creativity.

Guidelines:
- Ask open-ended questions
- Encourage multiple perspectives
- Avoid giving direct answers immediately
- Stimulate curiosity and deeper thinking

Conversation so far:
$context

Student: $currentmessage

Tutor:
";

        // Action-specific instruction (light layer)
        if ($action) {
            $prompt .= "\n[Instruction: Adapt teaching strategy: $action]";
        }

        return $prompt;
    }
}
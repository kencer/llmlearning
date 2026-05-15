<?php
namespace local_llmlearning\policy;

defined('MOODLE_INTERNAL') || die();

class strategy_manager {

    public static function apply($action, $baseprompt) {

        switch ($action) {

            case 'increase_engagement':
                return $baseprompt . "\n\nMake the response more engaging, friendly, and motivating. Ask a simple relatable question.";

            case 'prompt_more_questions':
                return $baseprompt . "\n\nAsk more open-ended questions to encourage exploration.";

            case 'encourage_divergent_thinking':
                return $baseprompt . "\n\nEncourage the student to think of multiple perspectives or alternative ideas.";

            case 'challenge_learner':
                return $baseprompt . "\n\nChallenge the learner with a deeper or more complex question.";

            case 'normal_scaffolding':
            default:
                return $baseprompt . "\n\nProvide balanced guidance with some prompting.";
        }
    }
}
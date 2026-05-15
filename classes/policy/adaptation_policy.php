<?php
namespace local_llmlearning\policy;

defined('MOODLE_INTERNAL') || die();

class adaptation_policy {

    /**
     * Select pedagogical action based on learner state
     */
    public static function select_action($state) {

        $flags = $state['flags'];

        // Priority-based decisions

        if (in_array('low_engagement', $flags)) {
            return 'increase_engagement';
        }

        if (in_array('needs_exploration', $flags)) {
            return 'prompt_more_questions';
        }

        if (in_array('needs_creativity', $flags)) {
            return 'encourage_divergent_thinking';
        }

        if (in_array('advanced_learner', $flags)) {
            return 'challenge_learner';
        }

        return 'normal_scaffolding';
    }

    /**
     * Optional: Future RL integration placeholder
     */
    public static function select_action_rl($state) {
        // Placeholder for reinforcement learning
        return self::select_action($state);
    }
}
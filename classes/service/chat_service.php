<?php
namespace local_llmlearning\service;

defined('MOODLE_INTERNAL') || die();

use local_llmlearning\service\llm_service;
use local_llmlearning\service\prompt_builder;
use local_llmlearning\analytics\exploration_engine;
use local_llmlearning\analytics\creativity_engine;
use local_llmlearning\analytics\state_engine;
use local_llmlearning\policy\adaptation_policy;
use local_llmlearning\policy\strategy_manager;

class chat_service {

    /**
     * Process a student message → generate response → store interaction
     */
    public static function process_message($userid, $courseid, $message) {
        global $DB;

        $message = trim($message);
        if (empty($message)) {
            return [
                'interactionid' => null,
                'response' => '⚠️ Message cannot be empty.',
                'time' => date('H:i:s')
            ];
        }

        try {

            // 1. Get history
            $history = self::get_recent_history($userid, $courseid, 5);

            // 2. Build base prompt (no action yet)
            $baseprompt = prompt_builder::build($history, $message);

            // 3. LLM (temporary response before adaptation if needed)
            $response = llm_service::generate_response($baseprompt);

            // 4. Store interaction
            $record = new \stdClass();
            $record->userid = $userid;
            $record->courseid = $courseid;
            $record->userinput = $message;
            $record->agentresponse = $response;
            $record->timecreated = time();

            $interactionid = $DB->insert_record('llmlearning_interactions', $record);

            // 5. Exploration
            $exploration = exploration_engine::compute($history, $message);
            $DB->insert_record('llmlearning_exploration', (object)[
                'userid' => $userid,
                'interactionid' => $interactionid,
                'entropy' => $exploration['entropy'],
                'divergence' => $exploration['divergence'],
                'explorationscore' => $exploration['explorationscore'],
                'timecreated' => time()
            ]);

            // 6. Creativity
            $creativity = creativity_engine::compute($history, $message);
            $DB->insert_record('llmlearning_creativity', (object)[
                'userid' => $userid,
                'interactionid' => $interactionid,
                'novelty' => $creativity['novelty'],
                'flexibility' => $creativity['flexibility'],
                'elaboration' => $creativity['elaboration'],
                'diversity' => $creativity['diversity'],
                'creativityscore' => $creativity['creativityscore'],
                'timecreated' => time()
            ]);

            // 7. State update
            $state = state_engine::update($userid, $exploration, $creativity);

            // 8. Select action
            $action = adaptation_policy::select_action($state);

            // 9. Apply strategy (modify prompt)
            $adaptiveprompt = strategy_manager::apply($action, $baseprompt);

            // 10. Generate FINAL adaptive response
            $adaptive_response = llm_service::generate_response($adaptiveprompt);

            // 11. Update stored response (overwrite initial)
            $DB->set_field('llmlearning_interactions', 'agentresponse', $adaptive_response, ['id' => $interactionid]);

            $response = $adaptive_response;

            $creative = new \stdClass();
            $creative->userid = $userid;
            $creative->interactionid = $interactionid;
            $creative->novelty = $creativity['novelty'];
            $creative->flexibility = $creativity['flexibility'];
            $creative->elaboration = $creativity['elaboration'];
            $creative->diversity = $creativity['diversity'];
            $creative->creativityscore = $creativity['creativityscore'];
            $creative->timecreated = time();

            $DB->insert_record('llmlearning_creativity', $creative);

            return [
                'interactionid' => $interactionid,
                'response' => $response,
                'time' => date('H:i:s'),
                'exploration' => $exploration,
                'creativity' => $creativity,
                'state' => $state
            ];
        } catch (\Exception $e) {

            debugging('LLM Learning Error: ' . $e->getMessage(), DEBUG_DEVELOPER);

            return [
                'interactionid' => null,
                'response' => '⚠️ System error. Check logs.',
                'time' => date('H:i:s')
            ];
        }
    }

    /**
     * Retrieve full conversation history for a user in a course
     */
    public static function get_history($userid, $courseid) {
        global $DB;

        return $DB->get_records(
            'llmlearning_interactions',
            [
                'userid' => $userid,
                'courseid' => $courseid
            ],
            'timecreated ASC'
        );
    }

    /**
     * Retrieve recent history (limited)
     */
    public static function get_recent_history($userid, $courseid, $limit = 5) {
        global $DB;

        $sql = "SELECT *
                FROM {llmlearning_interactions}
                WHERE userid = :userid AND courseid = :courseid
                ORDER BY timecreated DESC";

        $records = $DB->get_records_sql($sql, [
            'userid' => $userid,
            'courseid' => $courseid
        ], 0, $limit);

        // Reverse to chronological order
        return array_reverse($records);
    }

    /**
     * Optional: Delete user history (useful for GDPR / testing)
     */
    public static function delete_history($userid, $courseid) {
        global $DB;

        return $DB->delete_records('llmlearning_interactions', [
            'userid' => $userid,
            'courseid' => $courseid
        ]);
    }

    /**
     * Optional: Count interactions
     */
    public static function count_interactions($userid, $courseid) {
        global $DB;

        return $DB->count_records('llmlearning_interactions', [
            'userid' => $userid,
            'courseid' => $courseid
        ]);
    }
}
<?php
namespace local_llmlearning\analytics;

defined('MOODLE_INTERNAL') || die();

class state_engine {

    /**
     * Update learner state based on latest metrics
     */
    public static function update($userid, $exploration, $creativity) {
        global $DB;

        // 1. Fetch previous state
        $existing = $DB->get_record('llmlearning_state', ['userid' => $userid]);

        // 2. Compute levels
        $exploration_level = self::level($exploration['explorationscore']);
        $creativity_level  = self::level($creativity['creativityscore']);

        // 3. Compute engagement (simple proxy)
        $engagement = self::engagement_score($userid);

        // 4. Compute trend
        $trend = self::compute_trend($userid, $exploration['explorationscore']);

        // 5. Flags (important for pedagogy later)
        $flags = self::generate_flags($exploration_level, $creativity_level, $engagement);

        // 6. Build state object
        $state = [
            'exploration_level' => $exploration_level,
            'creativity_level' => $creativity_level,
            'engagement' => $engagement,
            'trend' => $trend,
            'flags' => $flags
        ];

        $statejson = json_encode($state);

        // 7. Insert or update
        if ($existing) {
            $existing->explorationscore = $exploration['explorationscore'];
            $existing->creativityscore = $creativity['creativityscore'];
            $existing->statejson = $statejson;
            $existing->timeupdated = time();

            $DB->update_record('llmlearning_state', $existing);

        } else {
            $record = new \stdClass();
            $record->userid = $userid;
            $record->explorationscore = $exploration['explorationscore'];
            $record->creativityscore = $creativity['creativityscore'];
            $record->statejson = $statejson;
            $record->timeupdated = time();

            $DB->insert_record('llmlearning_state', $record);
        }

        return $state;
    }

    /**
     * Convert numeric score → level
     */
    private static function level($score) {
        if ($score < 0.3) return 'low';
        if ($score < 0.7) return 'medium';
        return 'high';
    }

    /**
     * Engagement score (based on number of interactions)
     */
    private static function engagement_score($userid) {
        global $DB;

        $count = $DB->count_records('llmlearning_interactions', ['userid' => $userid]);

        if ($count < 5) return 'low';
        if ($count < 20) return 'medium';
        return 'high';
    }

    /**
     * Trend: improving / declining / stable
     */
    private static function compute_trend($userid, $current_score) {
        global $DB;

        $sql = "SELECT explorationscore
                FROM {llmlearning_exploration}
                WHERE userid = :userid
                ORDER BY timecreated DESC";

        $records = $DB->get_records_sql($sql, ['userid' => $userid], 0, 5);

        if (count($records) < 2) {
            return 'stable';
        }

        $scores = array_values(array_map(function($r) {
            return $r->explorationscore;
        }, $records));

        $avg_previous = array_sum(array_slice($scores, 1)) / (count($scores) - 1);

        if ($current_score > $avg_previous + 0.05) return 'improving';
        if ($current_score < $avg_previous - 0.05) return 'declining';

        return 'stable';
    }

    /**
     * Generate pedagogical flags
     */
    private static function generate_flags($exploration, $creativity, $engagement) {

        $flags = [];

        if ($exploration === 'low') {
            $flags[] = 'needs_exploration';
        }

        if ($creativity === 'low') {
            $flags[] = 'needs_creativity';
        }

        if ($engagement === 'low') {
            $flags[] = 'low_engagement';
        }

        if ($exploration === 'high' && $creativity === 'high') {
            $flags[] = 'advanced_learner';
        }

        return $flags;
    }
}
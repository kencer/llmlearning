<?php
namespace local_llmlearning\service;

defined('MOODLE_INTERNAL') || die();

class dashboard_service {

    public static function get_students_summary() {
        global $DB;

        $sql = "
            SELECT u.id, u.firstname, u.lastname,
                   s.explorationscore,
                   s.creativityscore,
                   s.statejson
            FROM {user} u
            JOIN {llmlearning_state} s ON s.userid = u.id
            ORDER BY s.timeupdated DESC
        ";

        $records = $DB->get_records_sql($sql);

        $data = [];

        foreach ($records as $r) {

            $state = json_decode($r->statejson, true);

            $data[] = [
                'fullname' => $r->firstname . ' ' . $r->lastname,
                'exploration' => round($r->explorationscore, 2),
                'creativity' => round($r->creativityscore, 2),
                'exploration_level' => $state['exploration_level'] ?? '',
                'creativity_level' => $state['creativity_level'] ?? '',
                'engagement' => $state['engagement'] ?? '',
                'trend' => $state['trend'] ?? '',
                'flags' => implode(', ', $state['flags'] ?? [])
            ];
        }

        return $data;
    }
}
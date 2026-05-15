<?php
namespace local_llmlearning\analytics;

defined('MOODLE_INTERNAL') || die();

class exploration_engine {

    /**
     * Main computation function
     */
    public static function compute($history, $currentinput) {

        // Combine previous inputs
        $previous_text = "";
        foreach ($history as $item) {
            $previous_text .= " " . $item->userinput;
        }

        // Clean + tokenize
        $prev_tokens = self::tokenize($previous_text);
        $current_tokens = self::tokenize($currentinput);

        // Metrics
        $divergence = self::semantic_shift($prev_tokens, $current_tokens);
        $entropy = self::compute_entropy(array_merge($prev_tokens, $current_tokens));
        $diversity = self::lexical_diversity($current_tokens);

        // Final exploration score
        $exploration_score = self::combine($entropy, $divergence, $diversity);

        return [
            'entropy' => $entropy,
            'divergence' => $divergence,
            'diversity' => $diversity,
            'explorationscore' => $exploration_score
        ];
    }

    /**
     * Tokenize text into words
     */
    private static function tokenize($text) {
        $text = strtolower($text);
        $text = preg_replace('/[^a-z0-9\s]/', '', $text);
        $tokens = preg_split('/\s+/', $text);

        return array_values(array_filter($tokens));
    }

    /**
     * Semantic shift (Jaccard distance)
     */
    private static function semantic_shift($tokens1, $tokens2) {
        if (empty($tokens1) || empty($tokens2)) {
            return 0;
        }

        $set1 = array_unique($tokens1);
        $set2 = array_unique($tokens2);

        $intersection = count(array_intersect($set1, $set2));
        $union = count(array_unique(array_merge($set1, $set2)));

        if ($union == 0) {
            return 0;
        }

        // Jaccard distance
        return 1 - ($intersection / $union);
    }

    /**
     * Entropy calculation
     */
    private static function compute_entropy($tokens) {
        if (empty($tokens)) {
            return 0;
        }

        $counts = array_count_values($tokens);
        $total = count($tokens);

        $entropy = 0.0;

        foreach ($counts as $count) {
            $p = $count / $total;
            $entropy -= $p * log($p, 2);
        }

        return $entropy;
    }

    /**
     * Lexical diversity
     */
    private static function lexical_diversity($tokens) {
        if (empty($tokens)) {
            return 0;
        }

        return count(array_unique($tokens)) / count($tokens);
    }

    /**
     * Combine metrics into exploration score
     */
    private static function combine($entropy, $divergence, $diversity) {

        // Weighted combination (tunable later)
        return (0.4 * $entropy) + (0.4 * $divergence) + (0.2 * $diversity);
    }
}
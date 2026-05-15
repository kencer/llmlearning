<?php
namespace local_llmlearning\analytics;

defined('MOODLE_INTERNAL') || die();

class creativity_engine {

    public static function compute($history, $currentinput) {

        // Combine previous inputs
        $previous_text = "";
        foreach ($history as $item) {
            $previous_text .= " " . $item->userinput;
        }

        $prev_tokens = self::tokenize($previous_text);
        $current_tokens = self::tokenize($currentinput);

        $novelty = self::novelty($prev_tokens, $current_tokens);
        $flexibility = self::flexibility($prev_tokens, $current_tokens);
        $elaboration = self::elaboration($currentinput);
        $diversity = self::diversity($current_tokens);

        $creativity_score = self::combine($novelty, $flexibility, $elaboration, $diversity);

        return [
            'novelty' => $novelty,
            'flexibility' => $flexibility,
            'elaboration' => $elaboration,
            'diversity' => $diversity,
            'creativityscore' => $creativity_score
        ];
    }

    private static function tokenize($text) {
        $text = strtolower($text);
        $text = preg_replace('/[^a-z0-9\s]/', '', $text);
        $tokens = preg_split('/\s+/', $text);

        return array_values(array_filter($tokens));
    }

    /**
     * Novelty: proportion of new words not seen before
     */
    private static function novelty($prev, $current) {
        if (empty($current)) return 0;

        $new_words = array_diff($current, $prev);
        return count($new_words) / count($current);
    }

    /**
     * Flexibility: how different the vocabulary is
     */
    private static function flexibility($prev, $current) {
        if (empty($prev) || empty($current)) return 0;

        $intersection = count(array_intersect($prev, $current));
        $union = count(array_unique(array_merge($prev, $current)));

        if ($union == 0) return 0;

        return 1 - ($intersection / $union);
    }

    /**
     * Elaboration: normalized length score
     */
    private static function elaboration($text) {
        $length = str_word_count($text);

        // Normalize (cap at 50 words)
        return min($length / 50, 1);
    }

    /**
     * Diversity: unique word ratio
     */
    private static function diversity($tokens) {
        if (empty($tokens)) return 0;

        return count(array_unique($tokens)) / count($tokens);
    }

    /**
     * Combine into final creativity score
     */
    private static function combine($novelty, $flexibility, $elaboration, $diversity) {

        return (0.3 * $novelty) +
               (0.3 * $flexibility) +
               (0.2 * $elaboration) +
               (0.2 * $diversity);
    }
}
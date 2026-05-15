<?php

namespace local_llmlearning\service;

defined('MOODLE_INTERNAL') || die();

class llm_service {

    public static function generate_response($prompt) {

        global $CFG;
        require_once($CFG->libdir . '/filelib.php');

        $apikey = get_config('local_llmlearning', 'apikey');
        $model = get_config('local_llmlearning', 'model');

        if (empty($apikey) && !empty($CFG->openai_api_key)) {
            $apikey = $CFG->openai_api_key;
        }

        // Fallback if no API key
        if (empty($apikey)) {
            return "⚠️ OpenAI API key not configured.";
        }

        if (empty($model)) {
            $model = 'gpt-4.1-mini';
        }

        $endpoint = 'https://api.openai.com/v1/chat/completions';

        $payload = [
            'model' => $model,
            'messages' => [
                [
                    'role' => 'system',
                    'content' => 'You are a helpful AI tutor that promotes exploratory learning and creativity.'
                ],
                [
                    'role' => 'user',
                    'content' => $prompt
                ]
            ],
            'temperature' => 0.7,
            'max_tokens' => 300
        ];

        $curl = new \curl();

        $options = [
            'CURLOPT_HTTPHEADER' => [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $apikey
            ],
            'CURLOPT_TIMEOUT' => 60
        ];

        try {

            $response = $curl->post(
                $endpoint,
                json_encode($payload),
                $options
            );

            $data = json_decode($response, true);

            // Debug logging (temporary)
            error_log("OpenAI Response: " . $response);

            if (!empty($data['choices'][0]['message']['content'])) {
                return trim($data['choices'][0]['message']['content']);
            }

            // API returned error
            if (!empty($data['error']['message'])) {
                return "⚠️ OpenAI Error: " . $data['error']['message'];
            }

            return "⚠️ No response generated.";

        } catch (\Exception $e) {

            error_log("LLM Service Error: " . $e->getMessage());

            return "⚠️ AI service connection failed.";
        }
    }
}


/*namespace local_llmlearning\service;

defined('MOODLE_INTERNAL') || die();

class llm_service {

    public static function generate_response($prompt) {

        $apikey = get_config('local_llmlearning', 'apikey');
        $model  = get_config('local_llmlearning', 'model');

        if (empty($apikey)) {
            return "⚠️ Gemini API key not configured.";
        }

        // Default Gemini model (safe for free tier)
        if (empty($model)) {
            $model = "gemini-2.5-flash";
        }

        //$endpoint = "https://generativelanguage.googleapis.com/v1beta/models/" 
                    . $model . ":generateContent?key=" . $apikey;

        //$data = [
            "contents" => [
                [
                    "parts" => [
                        ["text" => $prompt]
                    ]
                ]
            ]
        ];

        // Use rawurlencode to prevent the colon from being treated as a port
        $encoded_model = rawurlencode($model);

        $endpoint = "https://generativelanguage.googleapis.com/v1/models/". $model . ":generateContent?key=" . $apikey;

        //$endpoint = "https://googleapis.com" . $model . ":generateContent?key=" . $apikey;

        $data = [
            "contents" => [
                [
                    "parts" => [
                        ["text" => $prompt]
                    ]
                ]
            ]
        ];

        $ch = curl_init($endpoint);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Content-Type: application/json"
        ]);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

        // Timeout
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);

        $response = curl_exec($ch);

        if (curl_errno($ch)) {
            return "⚠️ Gemini connection error: " . curl_error($ch);
        }

        curl_close($ch);

        $decoded = json_decode($response, true);

        // Debug fallback
        if (!$decoded) {
            return "⚠️ Invalid response from Gemini API.";
        }

        // Extract Gemini response safely
        if (isset($decoded['candidates'][0]['content']['parts'][0]['text'])) {
            return trim($decoded['candidates'][0]['content']['parts'][0]['text']);
        }

        // Show API error if exists
        if (isset($decoded['error']['message'])) {
            return "⚠️ Gemini API Error: " . $decoded['error']['message'];
        }

        return "⚠️ No response generated.";
    }
}
/* for ChatGPT (OpenAI code)
<?php
namespace local_llmlearning\service;

defined('MOODLE_INTERNAL') || die();

class llm_service {

    public static function generate_response($prompt) {

        $apikey = get_config('local_llmlearning', 'apikey');
        $model  = get_config('local_llmlearning', 'model');

        if (empty($apikey)) {
            return "⚠️ LLM API key not configured.";
        }

        $endpoint = "https://api.openai.com/v1/chat/completions";

        $data = [
            "model" => $model ?: "gpt-5-nano",
            "messages" => [
                ["role" => "system", "content" => "You are an AI learning companion that encourages curiosity, exploration, and creative thinking."],
                ["role" => "user", "content" => $prompt]
            ],
            "temperature" => 0.7,
            'max_tokens' => 300
        ];

        $ch = curl_init($endpoint);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Content-Type: application/json",
            "Authorization: Bearer " . $apikey
        ]);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

        // Timeout safety
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);

        $response = curl_exec($ch);

        if (curl_errno($ch)) {
            return "⚠️ LLM connection error: " . curl_error($ch);
        }

        curl_close($ch);

        $decoded = json_decode($response, true);

        if (isset($decoded['choices'][0]['message']['content'])) {
            return trim($decoded['choices'][0]['message']['content']);
        }

        return "⚠️ Unexpected LLM response.";
    }
}*/
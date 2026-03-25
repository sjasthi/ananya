<?php
// Load .env file if it exists
$envPaths = [];

// Preferred for shared hosting: absolute env file path set at server level.
// You can set APP_ENV_PATH or ANANYA_ENV_PATH to something like:
// /home/yourcpaneluser/.ananya/.env
$absoluteEnvPath = getenv('APP_ENV_PATH') ?: getenv('ANANYA_ENV_PATH') ?: '';
if (!empty($absoluteEnvPath)) {
    $envPaths[] = $absoluteEnvPath;
}

// Fallbacks for local development.
$envPaths[] = __DIR__ . '/mcp_server/.env';
$envPaths[] = __DIR__ . '/.env';
$envPaths[] = __DIR__ . '/../.env';

foreach ($envPaths as $envPath) {
    if (!file_exists($envPath)) {
        continue;
    }

    $lines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '' || str_starts_with($line, '#') || strpos($line, '=') === false) {
            continue;
        }

        list($key, $val) = explode('=', $line, 2);
        putenv(trim($key) . '=' . trim($val));
    }

    break;
}

header('Content-Type: application/json; charset=utf-8');
// chat_api.php - receives question and performs in-process LLM + tool orchestration

require_once __DIR__ . '/includes/api_reference.php';
require_once __DIR__ . '/includes/llm_handler.php';

// Tool-loop settings
$CHAT_MAX_TOOL_ITERS = (int)(getenv('CHAT_MAX_TOOL_ITERS') ?: 5);

// Helper function to call detected APIs
function call_detected_api($api_name, $params) {
    global $API_REFERENCE;
    
    // Find the API in the reference
    $api = null;
    foreach ($API_REFERENCE as $a) {
        if (strtolower($a['id']) === strtolower($api_name)) {
            $api = $a;
            break;
        }
    }
    
    if (!$api) {
        error_log("API not found: $api_name");
        return null;
    }
    
    // Build REST API URL using the current script base path
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $scriptDir = dirname($_SERVER['SCRIPT_NAME'] ?? '/ananya/chat_api.php');
    $basePath = rtrim(str_replace('\\', '/', $scriptDir), '/');
    $url = $protocol . '://' . $host . $basePath . '/' . $api['path'];
    
    // Add query parameters
    $url .= '?' . http_build_query($params);
    
    error_log("Calling API: $url");
    
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_HEADER, false);
    
    $result = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    // curl_close($ch);

    error_log("API Response ($http_code): " . substr($result, 0, 200));

    // If non-JSON response, try router endpoint as fallback
    if ($result && json_decode($result, true) === null && json_last_error() !== JSON_ERROR_NONE) {
        if (preg_match('#^api/([^/]+)/([^/]+)\.php$#i', $api['path'], $m)) {
            $category = $m[1];
            $action = $m[2];
            $routerUrl = $protocol . '://' . $host . $basePath . '/api.php/' . $category . '/' . $action;
            $routerUrl .= '?' . http_build_query($params);
            error_log("Non-JSON API response, retrying via router: $routerUrl");

            $ch2 = curl_init($routerUrl);
            curl_setopt($ch2, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch2, CURLOPT_TIMEOUT, 10);
            curl_setopt($ch2, CURLOPT_HEADER, false);
            $retry = curl_exec($ch2);
            $retry_code = curl_getinfo($ch2, CURLINFO_HTTP_CODE);
            // curl_close($ch2);'curl_close' is deprecated.

            error_log("Router Response ($retry_code): " . substr($retry, 0, 200));
            return $retry ?: $result;
        }
    }

    return $result ?: null;
}

// Infer parameters from the user question if the model didn't provide them
function infer_params_from_question($api_name, $question, $language) {
    $params = [];

    // Extract quoted text if present
    if (preg_match('/"([^"]+)"|\'([^\']+)\'/', $question, $m)) {
        $params['string'] = $m[1] ?: $m[2];
    } else {
        // Fallback: use the last word
        $parts = preg_split('/\s+/', trim($question));
        $params['string'] = end($parts);
    }

    $params['language'] = $language ?: 'english';

    return $params;
}

function extract_quoted_strings($question) {
    $strings = [];
    if (preg_match_all('/"([^"]+)"|\'([^\']+)\'/', $question, $matches)) {
        foreach ($matches[1] as $idx => $val) {
            $s = $val ?: $matches[2][$idx];
            if (!empty($s)) $strings[] = $s;
        }
    }
    return $strings;
}

function normalize_candidate_text($text) {
    $text = trim($text, " \t\n\r\0\x0B\"'");
    // Remove leading English stop words (Unicode-aware, space-based)
    $text = preg_replace('/^(does|do|is|are|can|could|would|should|please)\s+/iu', '', $text);
    // Remove enclosed stop words (Unicode-aware)
    $text = preg_replace('/\s+(the|a|an|word|string)\s+/iu', ' ', $text);
    return trim($text, " \t\n\r\0\x0B\"'.,?!");
}

function extract_single_quoted_string($question) {
    $strings = extract_quoted_strings($question);
    return count($strings) > 0 ? $strings[0] : null;
}

function extract_first_noun_from_question($question, $intent_keywords = []) {
    // Extract quoted text first
    $quoted = extract_single_quoted_string($question);
    if ($quoted) return $quoted;

    // Remove intent keywords and common stop words, keep first remaining word
    $words = preg_split('/\s+/u', trim($question));
    $filtered = [];
    
    $stop_words = array_merge(
        $intent_keywords,
        ['the', 'a', 'an', 'is', 'are', 'does', 'do', 'spell', 'word', 'string',
         'how', 'long', 'many', 'character', 'characters',
         'what', 'which', 'who', 'where', 'when', 'why',
         'of', 'in', 'for', 'with', 'on', 'at', 'by', 'to',
         'give', 'tell', 'me', 'please', 'find', 'get', 'show']
    );
    
    foreach ($words as $word) {
        $normalized = strtolower(trim($word, '?,!.;:'));
        if (!in_array($normalized, $stop_words) && !empty($normalized)) {
            $filtered[] = trim($word, '?,!.;:');
        }
    }
    
    return count($filtered) > 0 ? $filtered[0] : null;
}

function extract_two_strings_from_question($question) {
    $strings = extract_quoted_strings($question);
    if (count($strings) >= 2) return [$strings[0], $strings[1]];

    // Unicode-aware "X and Y" matching (space-based, no word boundaries)
    if (preg_match('/(.+?)\s+and\s+(.+)/iu', $question, $m)) {
        return [normalize_candidate_text($m[1]), normalize_candidate_text($m[2])];
    }

    if (count($strings) === 1) return [$strings[0], null];

    return [null, null];
}

function extract_string_and_input2($question, $phrase) {
    $strings = extract_quoted_strings($question);
    if (count($strings) >= 2) return [$strings[0], $strings[1]];

    // Unicode-aware phrase matching (space-based, no word boundaries)
    $phrasePattern = '/\s' . preg_quote($phrase, '/') . '(?:\s|$)/iu';
    if (!preg_match($phrasePattern, ' ' . $question)) return [null, null];

    if (count($strings) === 1) {
        $parts = preg_split($phrasePattern, ' ' . $question, 2);
        $input2 = isset($parts[1]) ? normalize_candidate_text($parts[1]) : null;
        return [$strings[0], $input2];
    }

    // Unicode-aware "X phrase Y" matching
    if (preg_match('/(.*?)\s' . preg_quote($phrase, '/') . '\s(.+)$/iu', $question, $m)) {
        return [normalize_candidate_text($m[1]), normalize_candidate_text($m[2])];
    }

    return [null, null];
}

// Load Quick Navigation labels from docs/api.php
function get_doc_nav_map() {
    static $cache = null;
    if ($cache !== null) return $cache;

    $cache = [];
    $doc_path = __DIR__ . '/docs/api.php';
    if (!file_exists($doc_path)) return $cache;

    $html = file_get_contents($doc_path);
    if (!$html) return $cache;

    if (preg_match_all('/<a\s+class="nav-link"\s+href="#([^"]+)">([^<]+)<\/a>/i', $html, $matches)) {
        foreach ($matches[1] as $idx => $anchor) {
            $label = trim($matches[2][$idx]);
            $cache[$anchor] = $label;
        }
    }

    return $cache;
}

function resolve_doc_label($api_name) {
    if (!$api_name) return null;
    $map = get_doc_nav_map();
    $normalized = str_replace('_', '-', strtolower($api_name));

    if (isset($map[$normalized])) return $map[$normalized];

    return null;
}

function number_to_words_small($num) {
    $map = [
        0 => 'zero', 1 => 'one', 2 => 'two', 3 => 'three', 4 => 'four', 5 => 'five',
        6 => 'six', 7 => 'seven', 8 => 'eight', 9 => 'nine', 10 => 'ten',
        11 => 'eleven', 12 => 'twelve', 13 => 'thirteen', 14 => 'fourteen',
        15 => 'fifteen', 16 => 'sixteen', 17 => 'seventeen', 18 => 'eighteen',
        19 => 'nineteen', 20 => 'twenty'
    ];
    return $map[$num] ?? (string)$num;
}

function format_api_result($api_name, $params, $api_result, $language) {
    if (!$api_result) return null;

    $decoded = json_decode($api_result, true);
    $is_success = false;
    if (is_array($decoded)) {
        if (isset($decoded['success'])) {
            $is_success = (bool)$decoded['success'];
        } elseif (isset($decoded['response_code'])) {
            $is_success = ($decoded['response_code'] >= 200 && $decoded['response_code'] < 300);
        }
    }

    if (is_array($decoded) && $is_success) {
        $result_value = $decoded['result'] ?? $decoded['data'] ?? null;
        if ($api_name === 'text_length' && is_numeric($result_value)) {
            $word = $params['string'] ?? '';
            $count_word = number_to_words_small((int)$result_value);
            return "The length of the word \"$word\" in $language is $count_word characters.";
        }
        return "Result: " . json_encode($result_value, JSON_UNESCAPED_UNICODE);
    }

    if ($decoded === null && json_last_error() !== JSON_ERROR_NONE) {
        return "I could not complete that request: API returned a non-JSON response.";
    }

    $error_message = is_array($decoded) ? ($decoded['message'] ?? 'API error') : 'API error';
    return "I could not complete that request: $error_message.";
}

function detect_intent($question, $language) {
    $q = strtolower($question);

    if (preg_match('/\b(length|how long|how many characters)\b/u', $q)) {
        $string = extract_first_noun_from_question($question, ['length', 'how', 'long', 'many', 'characters']);
        $params = ['language' => $language ?: 'english'];
        if ($string) $params['string'] = $string;
        return [
            'api_id' => 'text_length',
            'params' => $params,
        ];
    }

    if (preg_match('/\b(reverse|backwards|backward)\b/u', $q)) {
        $string = extract_first_noun_from_question($question, ['reverse', 'backwards', 'backward']);
        $params = ['language' => $language ?: 'english'];
        if ($string) $params['string'] = $string;
        return [
            'api_id' => 'text_reverse',
            'params' => $params,
        ];
    }

    if (preg_match('/\b(randomize|scramble|shuffle)\b/u', $q)) {
        $string = extract_first_noun_from_question($question, ['randomize', 'scramble', 'shuffle']);
        $params = ['language' => $language ?: 'english'];
        if ($string) $params['string'] = $string;
        return [
            'api_id' => 'text_randomize',
            'params' => $params,
        ];
    }

    if (preg_match('/\bpalindrome\b/u', $q)) {
        $string = extract_first_noun_from_question($question, ['palindrome', 'is', 'are']);
        $params = ['language' => $language ?: 'english'];
        if ($string) $params['string'] = $string;
        return [
            'api_id' => 'analysis_is_palindrome',
            'params' => $params,
        ];
    }

    if (preg_match('/\banagram\b/u', $q)) {
        [$s1, $s2] = extract_two_strings_from_question($question);
        $params = ['language' => $language ?: 'english'];
        if ($s1) $params['string'] = $s1;
        if ($s2) $params['input2'] = $s2;
        return [
            'api_id' => 'analysis_is_anagram',
            'params' => $params,
        ];
    }

    if (preg_match('/\b(starts with|begins with)\b/u', $q)) {
        [$s1, $s2] = extract_string_and_input2($question, 'starts with');
        if (!$s2) [$s1, $s2] = extract_string_and_input2($question, 'begins with');
        $params = ['language' => $language ?: 'english'];
        if ($s1) $params['string'] = $s1;
        if ($s2) $params['input2'] = $s2;
        return [
            'api_id' => 'comparison_starts_with',
            'params' => $params,
        ];
    }

    if (preg_match('/\b(ends with|finishes with)\b/u', $q)) {
        [$s1, $s2] = extract_string_and_input2($question, 'ends with');
        if (!$s2) [$s1, $s2] = extract_string_and_input2($question, 'finishes with');
        $params = ['language' => $language ?: 'english'];
        if ($s1) $params['string'] = $s1;
        if ($s2) $params['input2'] = $s2;
        return [
            'api_id' => 'comparison_ends_with',
            'params' => $params,
        ];
    }

    if (preg_match('/\b(contains|includes)\b/u', $q)) {
        [$s1, $s2] = extract_string_and_input2($question, 'contains');
        if (!$s2) [$s1, $s2] = extract_string_and_input2($question, 'includes');
        $params = ['language' => $language ?: 'english'];
        if ($s1) $params['string'] = $s1;
        if ($s2) $params['input2'] = $s2;
        return [
            'api_id' => 'validation_contains_string',
            'params' => $params,
        ];
    }

    return null;
}

function chat_system_prompt() {
    return "You are Ananya, a helpful AI assistant specialized in word and text processing. "
        . "Use tools for precise text analysis, comparison, validation, and transformations in English and Indic languages. "
        . "When tools return data, explain results clearly and concisely. "
        . "Do not output raw JSON unless user explicitly asks for JSON.";
}

function build_local_api_base_url() {
    $configured = getenv('API_BASE_URL');
    if (!empty($configured)) {
        return rtrim($configured, '/');
    }

    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $scriptDir = dirname($_SERVER['SCRIPT_NAME'] ?? '/ananya/chat_api.php');
    $basePath = rtrim(str_replace('\\', '/', $scriptDir), '/');

    return $protocol . '://' . $host . $basePath . '/api.php';
}

function get_tool_dispatch_map() {
    return [
        'reverse_text' => ['category' => 'text', 'action' => 'reverse', 'params' => ['string' => 'word', 'language' => 'language']],
        'get_text_length' => ['category' => 'text', 'action' => 'length', 'params' => ['string' => 'word', 'language' => 'language']],
        'randomize_text' => ['category' => 'text', 'action' => 'randomize', 'params' => ['string' => 'word', 'language' => 'language']],
        'split_text' => ['category' => 'text', 'action' => 'split', 'params' => ['string' => 'word', 'input2' => 'delimiter', 'language' => 'language']],
        'replace_in_text' => ['category' => 'text', 'action' => 'replace', 'params' => ['string' => 'word', 'input2' => 'search', 'input3' => 'replace_with', 'language' => 'language']],

        'get_logical_characters' => ['category' => 'characters', 'action' => 'logical', 'params' => ['string' => 'word', 'language' => 'language']],
        'get_base_characters' => ['category' => 'characters', 'action' => 'base', 'params' => ['string' => 'word', 'language' => 'language']],
        'get_code_points' => ['category' => 'characters', 'action' => 'codepoints', 'params' => ['string' => 'word', 'language' => 'language']],
        'get_character_at_position' => ['category' => 'characters', 'action' => 'logical-at', 'params' => ['string' => 'word', 'input2' => 'index', 'language' => 'language']],

        'check_palindrome' => ['category' => 'analysis', 'action' => 'is-palindrome', 'params' => ['string' => 'word', 'language' => 'language']],
        'check_anagram' => ['category' => 'analysis', 'action' => 'is-anagram', 'params' => ['string' => 'word1', 'input2' => 'word2', 'language' => 'language']],
        'can_make_word' => ['category' => 'analysis', 'action' => 'can-make-word', 'params' => ['string' => 'source_word', 'input2' => 'target_word', 'language' => 'language']],
        'can_make_all_words' => ['category' => 'analysis', 'action' => 'can-make-all-words', 'params' => ['string' => 'source_word', 'input2' => 'words', 'language' => 'language']],
        'get_word_strength' => ['category' => 'analysis', 'action' => 'word-strength', 'params' => ['string' => 'word', 'language' => 'language']],
        'get_word_weight' => ['category' => 'analysis', 'action' => 'word-weight', 'params' => ['string' => 'word', 'language' => 'language']],
        'get_word_level' => ['category' => 'analysis', 'action' => 'word-level', 'params' => ['string' => 'word', 'language' => 'language']],
        'detect_language' => ['category' => 'analysis', 'action' => 'detect-language', 'params' => ['string' => 'text']],
        'check_intersecting' => ['category' => 'comparison', 'action' => 'is-intersecting', 'params' => ['string' => 'word1', 'input2' => 'word2', 'language' => 'language']],
        'get_intersecting_rank' => ['category' => 'analysis', 'action' => 'intersecting-rank', 'params' => ['string' => 'word1', 'input2' => 'word2', 'language' => 'language']],
        'check_ladder_words' => ['category' => 'analysis', 'action' => 'are-ladder-words', 'params' => ['string' => 'word1', 'input2' => 'word2', 'language' => 'language']],
        'check_head_tail_words' => ['category' => 'analysis', 'action' => 'are-head-tail-words', 'params' => ['string' => 'word1', 'input2' => 'word2', 'language' => 'language']],
        'parse_to_logical_chars' => ['category' => 'analysis', 'action' => 'parse-to-logical-chars', 'params' => ['string' => 'word', 'language' => 'language']],

        'check_starts_with' => ['category' => 'comparison', 'action' => 'starts-with', 'params' => ['string' => 'word', 'input2' => 'prefix', 'language' => 'language']],
        'check_ends_with' => ['category' => 'comparison', 'action' => 'ends-with', 'params' => ['string' => 'word', 'input2' => 'suffix', 'language' => 'language']],
        'compare_words' => ['category' => 'comparison', 'action' => 'compare-to', 'params' => ['string' => 'word1', 'input2' => 'word2', 'language' => 'language']],
        'check_equals' => ['category' => 'comparison', 'action' => 'equals', 'params' => ['string' => 'word1', 'input2' => 'word2', 'language' => 'language']],
        'check_reverse_equals' => ['category' => 'comparison', 'action' => 'reverse-equals', 'params' => ['string' => 'word1', 'input2' => 'word2', 'language' => 'language']],
        'find_index_of' => ['category' => 'utility', 'action' => 'index-of', 'params' => ['string' => 'word', 'input2' => 'search', 'language' => 'language']],

        'check_contains_char' => ['category' => 'validation', 'action' => 'contains-char', 'params' => ['string' => 'word', 'input2' => 'char', 'language' => 'language']],
        'check_contains_string' => ['category' => 'validation', 'action' => 'contains-string', 'params' => ['string' => 'word', 'input2' => 'substring', 'language' => 'language']],
        'check_is_consonant' => ['category' => 'validation', 'action' => 'is-consonant', 'params' => ['string' => 'character', 'language' => 'language']],
        'check_is_vowel' => ['category' => 'validation', 'action' => 'is-vowel', 'params' => ['string' => 'character', 'language' => 'language']],
        'check_contains_space' => ['category' => 'validation', 'action' => 'contains-space', 'params' => ['string' => 'word', 'language' => 'language']],

        'get_length_no_spaces' => ['category' => 'utility', 'action' => 'length-no-spaces', 'params' => ['string' => 'word', 'language' => 'language']],
    ];
}

function tool_output_from_api_response($decoded) {
    if (!is_array($decoded)) {
        return 'Tool execution failed: invalid API JSON response.';
    }

    if (isset($decoded['success']) && $decoded['success'] === false) {
        $msg = $decoded['error'] ?? ($decoded['message'] ?? 'Unknown API error');
        return 'Tool execution failed: ' . $msg;
    }

    $result = $decoded['result'] ?? ($decoded['data'] ?? $decoded);
    if (is_string($result) || is_numeric($result) || is_bool($result) || $result === null) {
        return is_bool($result) ? ($result ? 'true' : 'false') : (string)$result;
    }

    return json_encode($result, JSON_UNESCAPED_UNICODE);
}

function execute_chat_tool($toolName, $args, $language, $apiBaseUrl) {
    $map = get_tool_dispatch_map();
    if (!isset($map[$toolName])) {
        return [
            'ok' => false,
            'output' => 'Unknown tool: ' . $toolName,
        ];
    }

    $conf = $map[$toolName];
    $query = [];
    foreach ($conf['params'] as $apiKey => $toolArgName) {
        if (isset($args[$toolArgName]) && $args[$toolArgName] !== '') {
            $query[$apiKey] = $args[$toolArgName];
        }
    }

    if (!isset($query['language']) && isset($conf['params']['language'])) {
        $query['language'] = $language ?: 'english';
    }

    $url = rtrim($apiBaseUrl, '/') . '/' . $conf['category'] . '/' . $conf['action'] . '?' . http_build_query($query);
    error_log('Tool call [' . $toolName . '] URL: ' . $url);

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, (int)(getenv('LOCAL_API_TIMEOUT') ?: 15));
    curl_setopt($ch, CURLOPT_HEADER, false);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlErr = curl_error($ch);
    curl_close($ch);

    if ($response === false || $httpCode < 200 || $httpCode >= 400) {
        $message = 'Tool execution failed (HTTP ' . $httpCode . '): ' . ($curlErr ?: 'Request error');
        return [
            'ok' => false,
            'output' => $message,
        ];
    }

    $decoded = json_decode($response, true);
    if (!is_array($decoded)) {
        return [
            'ok' => false,
            'output' => 'Tool execution failed: non-JSON API response.',
        ];
    }

    return [
        'ok' => true,
        'output' => tool_output_from_api_response($decoded),
    ];
}

function run_php_tool_orchestration($question, $language, $maxIters) {
    $messages = [
        ['role' => 'system', 'content' => chat_system_prompt()],
        ['role' => 'user', 'content' => $question],
    ];
    $tools = ananya_chat_tools();
    $apiBaseUrl = build_local_api_base_url();

    for ($i = 0; $i < $maxIters; $i++) {
        $llm = llm_request_openai_compatible($messages, [
            'tools' => $tools,
            'tool_choice' => 'auto',
            'parallel_tool_calls' => false,
            'temperature' => (float)(getenv('LLM_TEMPERATURE') ?: 0.2),
            'max_tokens' => (int)(getenv('LLM_MAX_TOKENS') ?: 1200),
        ]);

        if (!$llm['ok']) {
            return [
                'ok' => false,
                'error' => $llm['error'] ?? 'LLM orchestration call failed.',
            ];
        }

        $assistantMessage = $llm['assistant_message'] ?? ['role' => 'assistant', 'content' => ($llm['content'] ?? '')];
        $messages[] = $assistantMessage;

        $toolCalls = $llm['tool_calls'] ?? [];
        if (count($toolCalls) === 0) {
            $answer = trim($llm['content'] ?? '');
            if ($answer === '') {
                $answer = 'I could not generate a response.';
            }

            return [
                'ok' => true,
                'answer' => $answer,
                'source' => 'mcp',
                'llm_consulted' => true,
            ];
        }

        foreach ($toolCalls as $tc) {
            $rawArgs = $tc['arguments'] ?? '{}';
            $decodedArgs = json_decode($rawArgs, true);
            if (!is_array($decodedArgs)) {
                $decodedArgs = [];
            }

            $tool = execute_chat_tool($tc['name'], $decodedArgs, $language, $apiBaseUrl);
            $messages[] = [
                'role' => 'tool',
                'tool_call_id' => $tc['id'],
                'name' => $tc['name'],
                'content' => $tool['output'],
            ];
        }
    }

    return [
        'ok' => true,
        'answer' => 'I required too many steps to complete this request and had to stop.',
        'source' => 'mcp',
        'llm_consulted' => true,
    ];
}

$raw = file_get_contents('php://input');
$data = [];
if($raw) {
    $decoded = json_decode($raw, true);
    if(is_array($decoded)) $data = $decoded;
}

$question = trim($_POST['question'] ?? ($data['question'] ?? ''));
$language = $_POST['language'] ?? ($data['language'] ?? 'english');

if(!$question) {
    echo json_encode(['error' => 'Missing question parameter']);
    exit;
}

// ── Primary path: in-process tool orchestration (shared-hosting friendly) ──
$orchestrated = run_php_tool_orchestration($question, $language, $CHAT_MAX_TOOL_ITERS);
if (!empty($orchestrated['ok'])) {
    echo json_encode([
        'question' => $question,
        'language' => $language,
        'answer' => $orchestrated['answer'] ?? '',
        'source' => $orchestrated['source'] ?? 'mcp',
        'llm_consulted' => true,
    ]);
    exit;
}

error_log('PHP tool orchestration failed; falling back to legacy single-shot flow. Error: ' . ($orchestrated['error'] ?? 'unknown'));

// ── Fallback: direct LLM call (no tool execution) ─────────────────

$context = generate_api_context($API_REFERENCE);

// Build detailed API param reference for the LLM
$param_reference = "API Parameter Reference (use these EXACT names):\n";
foreach ($API_REFERENCE as $api) {
    if (!empty($api['params'])) {
        $param_reference .= "- " . $api['id'] . " (path: " . $api['path'] . "): " . implode(", ", array_keys($api['params'])) . "\n";
    }
}

$prompt = "You must respond with ONLY valid JSON in this exact structure:\n";
$prompt .= '{"api": "api_id", "params": {"param1": "value1", "param2": "value2"}}' . "\n\n";
$prompt .= "EXAMPLES:\n\n";
$prompt .= "User: 'How long is hello?'\n";
$prompt .= '{"api": "text_length", "params": {"string": "hello", "language": "english"}}' . "\n\n";
$prompt .= "User: 'Reverse the word test'\n";
$prompt .= '{"api": "text_reverse", "params": {"string": "test", "language": "english"}}' . "\n\n";
$prompt .= "User: 'Scramble xyz' or 'Randomize xyz'\n";
$prompt .= '{"api": "text_randomize", "params": {"string": "xyz", "language": "english"}}' . "\n\n";
$prompt .= $param_reference . "\n";
$prompt .= "Available APIs:\n" . $context . "\n\n";
$prompt .= "User: '" . $question . "' (language: $language)\n";
$prompt .= "JSON response: ";

$resp = llm_ask($prompt, [
    'model' => 'mistral',
    'temperature' => 0.0,
    'system_prompt' => 'You output ONLY valid JSON. No explanations.',
]);

error_log("LLM Raw Response:\n" . $resp . "\n---END---");

// Initialize variables
$api_name = null;
$api_doc_label = null;
$params = [];

// Try to parse JSON response
$json_match = null;
if (preg_match('/\{[^}]+\}/', $resp, $json_match)) {
    $decoded = json_decode($json_match[0], true);
    if ($decoded && isset($decoded['api'])) {
        $api_name = $decoded['api'];
        $params = $decoded['params'] ?? [];
        $api_doc_label = resolve_doc_label($api_name);
        error_log("Parsed JSON - API: $api_name, Params: " . json_encode($params));
    }
}

// Fallback: try old API_CALL format
if (!$api_name && preg_match('/API_CALL:\s*([A-Za-z0-9_-]+)/i', $resp, $m1)) {
    $api_name = $m1[1];
    $api_doc_label = resolve_doc_label($api_name);
    if (preg_match('/PARAMS:\s*([^\n]+)/i', $resp, $m2)) {
        parse_str($m2[1], $params);
    }
    error_log("Parsed old format - API: $api_name");
}

// Retry once with a stricter format if the model didn't follow instructions
if (!$api_name) {
    $strict_prompt = "Output ONLY this JSON structure with no extra text:\n";
    $strict_prompt .= '{"api": "api_id_here", "params": {"key": "value"}}' . "\n\n";
    $strict_prompt .= $param_reference . "\n";
    $strict_prompt .= "Available APIs:\n" . $context . "\n\n";
    $strict_prompt .= "User: '" . $question . "' (language: $language)\n";

    $resp = llm_ask($strict_prompt, [
        'model' => 'mistral',
        'temperature' => 0.0,
        'system_prompt' => 'Output ONLY valid JSON.',
    ]);

    error_log("LLM Strict Response:\n" . $resp . "\n---END---");
    
    // Parse JSON from strict response
    if (preg_match('/\{[^}]+\}/', $resp, $json_match)) {
        $decoded = json_decode($json_match[0], true);
        if ($decoded && isset($decoded['api'])) {
            $api_name = $decoded['api'];
            $params = $decoded['params'] ?? [];
            $api_doc_label = resolve_doc_label($api_name);
        }
    }
}

// Ensure language is set
if ($api_name && !isset($params['language'])) {
    $params['language'] = $language ?: 'english';
    error_log("Final params: " . json_encode($params));
}

// If an API call was detected, execute it
if ($api_name) {
    $api_result = call_detected_api($api_name, $params);
    if ($api_result) {
        $resp = format_api_result($api_name, $params, $api_result, $language);
    }
}

// Append doc label for UI display
if ($api_doc_label && strpos($resp, 'API used:') === false) {
    $resp .= "\n\nAPI used: $api_doc_label";
}

// Debug: log the response
error_log("chat_api.php | Question: " . $question . " | API: " . ($api_name ?: 'none') . " | Doc: " . ($api_doc_label ?: 'none') . " | Response: " . substr($resp, 0, 100));

// Append LLM consulted status for fallback responses
if (stripos($resp, 'LLM consulted') === false) {
    $resp .= "\n\nLLM consulted - Yes";
}

echo json_encode([
    'question' => $question,
    'language' => $language,
    'answer' => $resp,
    'api_doc_name' => $api_doc_label,
    'source' => 'fallback',
    'llm_consulted' => true,
]);

// End of file

<?php
// Load .env file if it exists
if (file_exists(__DIR__ . '/../.env')) {
    $lines = file(__DIR__ . '/../.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos($line, '=') !== false && !str_starts_with($line, '#')) {
            list($key, $val) = explode('=', $line, 2);
            putenv(trim($key) . '=' . trim($val));
        }
    }
}

header('Content-Type: application/json; charset=utf-8');
// chat_api.php - receives question, proxies to MCP server for LLM + tool orchestration
// Falls back to direct LLM call if MCP server is unreachable

require_once __DIR__ . '/includes/api_reference.php';
require_once __DIR__ . '/includes/llm_handler.php';

// MCP Server settings
$MCP_SERVER_URL = 'http://localhost:8000/chat';
$MCP_TIMEOUT = 120; // seconds — two-stage filtering keeps it fast, but allow headroom for cold starts

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
    $scriptDir = dirname($_SERVER['SCRIPT_NAME'] ?? '/ananya/ananya_files/chat_api.php');
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
    curl_close($ch);

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
            curl_close($ch2);

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
        ['the', 'a', 'an', 'is', 'are', 'does', 'do', 'spell', 'word', 'string', 'how', 'long', 'many', 'character', 'characters']
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

// ── Intent router (no LLM) ───────────────────────────────────────
$intent = detect_intent($question, $language);
if ($intent) {
    $api_name = $intent['api_id'] ?? null;
    $params = $intent['params'] ?? [];
    $api_doc_label = resolve_doc_label($api_name);

    $api_result = call_detected_api($api_name, $params);
    $resp = format_api_result($api_name, $params, $api_result, $language);

    if ($api_doc_label && strpos($resp, 'API used:') === false) {
        $resp .= "\n\nAPI used: $api_doc_label";
    }
    if (stripos($resp, 'LLM consulted') === false) {
        $resp .= "\n\nLLM consulted - No";
    }

    echo json_encode([
        'question' => $question,
        'language' => $language,
        'answer' => $resp,
        'api_doc_name' => $api_doc_label,
        'source' => 'intent-router',
        'llm_consulted' => false,
    ]);
    exit;
}

// ── Try MCP Server first ───────────────────────────────────────────
$mcp_result = call_mcp_server($MCP_SERVER_URL, $question, $language, $MCP_TIMEOUT);

if($mcp_result !== null) {
    // MCP server responded successfully
    if (is_array($mcp_result)) {
        $mcp_result['llm_consulted'] = true;
        if (!empty($mcp_result['answer']) && stripos($mcp_result['answer'], 'LLM consulted') === false) {
            $mcp_result['answer'] .= "\n\nLLM consulted - Yes";
        }
    }
    echo json_encode($mcp_result);
    exit;
}

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

// ── Helper: call the Python MCP server ─────────────────────────────
function call_mcp_server($url, $question, $language, $timeout) {
    $payload = json_encode([
        'question' => $question,
        'language' => $language,
    ]);

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Accept: application/json',
    ]);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
    curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);

    $result = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $err = curl_error($ch);
    curl_close($ch);

    if($result === false || $httpCode < 200 || $httpCode >= 500) {
        // MCP server is down or errored — trigger fallback
        error_log("MCP server unreachable ($url): $err (HTTP $httpCode)");
        return null;
    }

    $decoded = json_decode($result, true);
    if(!is_array($decoded)) {
        error_log("MCP server returned invalid JSON");
        return null;
    }

    return $decoded;
}

// End of file

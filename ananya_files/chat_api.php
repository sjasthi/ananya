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
$MCP_TIMEOUT = 60; // seconds — tool-calling loops can take a while

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

    if (preg_match('/\b(length|how long|how many characters)\b/', $q)) {
        return [
            'api_id' => 'text_length',
            'params' => infer_params_from_question('text_length', $question, $language),
        ];
    }

    if (preg_match('/\b(reverse|backwards|backward)\b/', $q)) {
        return [
            'api_id' => 'text_reverse',
            'params' => infer_params_from_question('text_reverse', $question, $language),
        ];
    }

    if (preg_match('/\bpalindrome\b/', $q)) {
        return [
            'api_id' => 'analysis_is_palindrome',
            'params' => infer_params_from_question('analysis_is_palindrome', $question, $language),
        ];
    }

    if (preg_match('/\banagram\b/', $q)) {
        return [
            'api_id' => 'analysis_is_anagram',
            'params' => infer_params_from_question('analysis_is_anagram', $question, $language),
        ];
    }

    if (preg_match('/\bstarts with\b/', $q)) {
        return [
            'api_id' => 'comparison_starts_with',
            'params' => infer_params_from_question('comparison_starts_with', $question, $language),
        ];
    }

    if (preg_match('/\bends with\b/', $q)) {
        return [
            'api_id' => 'comparison_ends_with',
            'params' => infer_params_from_question('comparison_ends_with', $question, $language),
        ];
    }

    if (preg_match('/\bcontains\b/', $q)) {
        return [
            'api_id' => 'validation_contains_string',
            'params' => infer_params_from_question('validation_contains_string', $question, $language),
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

$prompt = "You are a smart assistant that answers user questions by calling available APIs.\n";
$prompt .= "When the user asks something that can be answered by an API, output ONLY:\n";
$prompt .= "API_CALL: api_id\n";
$prompt .= "PARAMS: param1=value1&param2=value2\n";
$prompt .= "Do NOT include instructions, URLs, examples, or any extra text.\n";
$prompt .= "If no API applies, answer normally in one short paragraph.\n\n";
$prompt .= $param_reference . "\n";
$prompt .= "Available APIs:\n" . $context . "\n";
$prompt .= "User question (language: $language):\n" . $question . "\n";

$resp = llm_ask($prompt, [
    'model' => 'mistral',
    'temperature' => 0.2,
]);

error_log("LLM Raw Response:\n" . $resp . "\n---END---");

// Retry once with a stricter format if the model didn't follow instructions
if (!preg_match('/API_CALL:\s*([A-Za-z0-9_-]+)/i', $resp)) {
    $strict_prompt = "You MUST respond with ONLY the two lines below and nothing else:\n";
    $strict_prompt .= "API_CALL: api_id\n";
    $strict_prompt .= "PARAMS: param1=value1&param2=value2\n";
    $strict_prompt .= "Use the exact parameter names from the reference.\n";
    $strict_prompt .= "If no API applies, respond with: API_CALL: none and PARAMS: none\n\n";
    $strict_prompt .= $param_reference . "\n";
    $strict_prompt .= "Available APIs:\n" . $context . "\n";
    $strict_prompt .= "User question (language: $language):\n" . $question . "\n";

    $resp = llm_ask($strict_prompt, [
        'model' => 'mistral',
        'temperature' => 0.0,
    ]);

    error_log("LLM Strict Response:\n" . $resp . "\n---END---");
}

// Parse API_CALL from response
$api_name = null;
$api_doc_label = null;
$params = [];
if (preg_match('/API_CALL:\s*([A-Za-z0-9_-]+)/i', $resp, $m1)) {
    $api_name = $m1[1];
    $api_doc_label = resolve_doc_label($api_name);
    error_log("Found API_CALL: $api_name");
}
if ($api_name && preg_match('/PARAMS:\s*([^\n]+)/i', $resp, $m2)) {
    parse_str($m2[1], $params);
    error_log("Found PARAMS: " . $m2[1]);
    error_log("Parsed params: " . json_encode($params));
}

// If still no API_CALL, attempt to extract API name and params from freeform text
if (!$api_name) {
    foreach ($API_REFERENCE as $api) {
        if (!empty($api['id']) && preg_match('/\b' . preg_quote($api['id'], '/') . '\b/i', $resp)) {
            $api_name = $api['id'];
            $api_doc_label = resolve_doc_label($api_name);
            error_log("Extracted API id from freeform: $api_name");
            break;
        }
    }

    if ($api_name && preg_match('/(string|language|input2|input3)\s*=\s*[^\s&]+/i', $resp)) {
        if (preg_match('/(string=[^\s&]+(?:&language=[^\s&]+)?(?:&input2=[^\s&]+)?(?:&input3=[^\s&]+)?)/i', $resp, $m3)) {
            parse_str($m3[1], $params);
            error_log("Extracted params from freeform: " . json_encode($params));
        }
    }
}

// Heuristic: infer missing parameters or fill missing keys
if ($api_name) {
    $inferred = infer_params_from_question($api_name, $question, $language);
    if (empty($params)) {
        $params = $inferred;
    } else {
        if (empty($params['string']) && !empty($inferred['string'])) {
            $params['string'] = $inferred['string'];
        }
        if (empty($params['language']) && !empty($inferred['language'])) {
            $params['language'] = $inferred['language'];
        }
    }
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

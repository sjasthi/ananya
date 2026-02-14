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
// chat_api.php - receives question, queries LLM with API context, returns JSON

require_once __DIR__ . '/includes/api_reference.php';
require_once __DIR__ . '/includes/llm_handler.php';

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
    
    // Build REST API URL using the new router
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $url = $protocol . '://' . $host . '/ananya/ananya_files/' . $api['path'];
    
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

// Build a concise API context for the model
$context = generate_api_context($API_REFERENCE);

// Build detailed API param reference for the LLM
$param_reference = "API Parameter Reference (use these EXACT names):\n";
foreach ($API_REFERENCE as $api) {
    if (!empty($api['params'])) {
        $param_reference .= "- " . $api['id'] . " (endpoint: " . $api['endpoint'] . "): " . implode(", ", array_keys($api['params'])) . "\n";
    }
}

$prompt = "You are a smart assistant that answers user questions by calling available REST APIs.\n";
$prompt .= "When the user asks something that can be answered by an API:\n";
$prompt .= "1. Identify which API to call\n";
$prompt .= "2. Format it as:\n";
$prompt .= "API_CALL: api_id\n";
$prompt .= "PARAMS: param1=value1&param2=value2\n";
$prompt .= "3. Then provide your response based on the result.\n\n";
$prompt .= $param_reference . "\n";
$prompt .= "Available APIs:\n" . $context . "\n";
$prompt .= "User question (language: $language):\n" . $question . "\n";

// Query the LLM
$resp = llm_ask($prompt, [
    'model' => 'mistral',
    'temperature' => 0.2,
]);

error_log("LLM Raw Response:\n" . $resp . "\n---END---");

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
        $decoded = json_decode($api_result, true);
        if (is_array($decoded) && ($decoded['success'] ?? false)) {
            $result_value = $decoded['result'] ?? $decoded['data'] ?? null;
            if ($api_name === 'text_length' && is_numeric($result_value)) {
                $word = $params['string'] ?? '';
                $resp = "The length of the word \"$word\" in $language is $result_value characters.";
            } else {
                $resp = "Result: " . json_encode($result_value, JSON_UNESCAPED_UNICODE);
            }
        } else {
            // On error, return a concise message without dumping the raw API error
            $error_message = is_array($decoded) ? ($decoded['message'] ?? 'API error') : 'API error';
            $resp = "I could not complete that request: $error_message.";
        }
    }
}

// Append doc label for UI display
if ($api_doc_label && strpos($resp, '(API:') === false) {
    $resp .= "\n\n(API: $api_doc_label)";
}

// Debug: log the response
error_log("chat_api.php | Question: " . $question . " | API: " . ($api_name ?: 'none') . " | Doc: " . ($api_doc_label ?: 'none') . " | Response: " . substr($resp, 0, 100));

// Return structured JSON
echo json_encode([
    'question' => $question,
    'language' => $language,
    'answer' => $resp,
    'api_doc_name' => $api_doc_label,
]);

// End of file

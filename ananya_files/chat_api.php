<?php
header('Content-Type: application/json; charset=utf-8');
// chat_api.php - receives question, proxies to MCP server for LLM + tool orchestration
// Falls back to direct LLM call if MCP server is unreachable

// MCP Server settings
$MCP_SERVER_URL = 'http://localhost:8000/chat';
$MCP_TIMEOUT = 60; // seconds — tool-calling loops can take a while

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

// ── Try MCP Server first ───────────────────────────────────────────
$mcp_result = call_mcp_server($MCP_SERVER_URL, $question, $language, $MCP_TIMEOUT);

if($mcp_result !== null) {
    // MCP server responded successfully
    echo json_encode($mcp_result);
    exit;
}

// ── Fallback: direct LLM call (no tool execution) ─────────────────
require_once __DIR__ . '/includes/api_reference.php';
require_once __DIR__ . '/includes/llm_handler.php';

$context = generate_api_context($API_REFERENCE);

$prompt = "You are Ananya, a helpful assistant for word processing and text analysis.\n";
$prompt .= "You have knowledge of these APIs but cannot call them right now (MCP server is offline).\n";
$prompt .= "Answer the user's question using your own knowledge. If relevant, mention which API could help.\n\n";
$prompt .= $context . "\n\n";
$prompt .= "User question (language: $language):\n" . $question . "\n";

$resp = llm_ask($prompt, [
    'model' => 'gpt-4o-mini',
    'max_tokens' => 800,
    'temperature' => 0.2,
]);

echo json_encode([
    'question' => $question,
    'language' => $language,
    'answer' => $resp,
    'source' => 'fallback',
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

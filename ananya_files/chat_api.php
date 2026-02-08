<?php
header('Content-Type: application/json; charset=utf-8');
// chat_api.php - receives question, queries LLM with API context, returns JSON

require_once __DIR__ . '/includes/api_reference.php';
require_once __DIR__ . '/includes/llm_handler.php';

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

$prompt = "You are an assistant that answers user questions and may reference the app's available APIs.\n";
$prompt .= "Use the API reference below to provide accurate guidance or examples for calling the APIs.\n\n";
$prompt .= $context . "\n\n";
$prompt .= "User question (language: $language):\n" . $question . "\n\n";
$prompt .= "When appropriate, return a short answer and an optional example curl or PHP snippet showing how to call the relevant API.\n";

// Query the LLM
$resp = llm_ask($prompt, [
    'model' => 'gpt-4o-mini',
    'max_tokens' => 800,
    'temperature' => 0.2,
]);

// Return structured JSON
echo json_encode([
    'question' => $question,
    'language' => $language,
    'answer' => $resp,
]);

// End of file

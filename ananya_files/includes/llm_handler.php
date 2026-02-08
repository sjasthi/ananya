<?php
// Simple LLM handler - uses OpenAI-compatible chat completions
// Reads API key from environment variable OPENAI_API_KEY or from $_ENV

function llm_ask($prompt, $opts = []) {
    $apiKey = getenv('OPENAI_API_KEY') ?: ($_ENV['OPENAI_API_KEY'] ?? null);
    if(!$apiKey) return 'LLM API key not configured. Set OPENAI_API_KEY environment variable.';

    $model = $opts['model'] ?? 'gpt-4o-mini';
    $max_tokens = $opts['max_tokens'] ?? 800;
    $temperature = $opts['temperature'] ?? 0.2;

    $payload = [
        'model' => $model,
        'messages' => [
            ['role' => 'system', 'content' => 'You are a helpful assistant. Provide concise, accurate answers and sample API calls when relevant.'],
            ['role' => 'user', 'content' => $prompt]
        ],
        'max_tokens' => $max_tokens,
        'temperature' => $temperature,
    ];

    $ch = curl_init('https://api.openai.com/v1/chat/completions');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Authorization: Bearer ' . $apiKey
    ]);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));

    $result = curl_exec($ch);
    if($result === false) {
        $err = curl_error($ch);
        curl_close($ch);
        return 'LLM request failed: ' . $err;
    }
    curl_close($ch);

    $decoded = json_decode($result, true);
    if(!$decoded) return 'Invalid response from LLM service.';

    // Extract message content
    if(isset($decoded['choices'][0]['message']['content'])) {
        return trim($decoded['choices'][0]['message']['content']);
    }

    return $result;
}

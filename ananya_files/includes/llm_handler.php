<?php
// LLM handler using Ollama (local, free, no API key needed)
// Make sure Ollama is running: ollama serve
// Available models: ollama list

function llm_ask($prompt, $opts = []) {
    $ollamaUrl = getenv('OLLAMA_URL') ?: 'http://localhost:11434';
    $model = $opts['model'] ?? 'mistral'; // Change to 'llama2', 'neural-chat', etc.
    $temperature = $opts['temperature'] ?? 0.2;
    $systemPrompt = $opts['system_prompt'] ?? 'You are a helpful assistant. Provide concise, accurate answers.';

    $payload = [
        'model' => $model,
        'prompt' => $prompt,
        'system' => $systemPrompt,
        'stream' => false,
        'options' => [
            'temperature' => $temperature,
            'top_k' => 40,
            'top_p' => 0.9,
        ]
    ];

    $ch = curl_init($ollamaUrl . '/api/generate');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json'
    ]);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
    curl_setopt($ch, CURLOPT_TIMEOUT, 300); // Long timeout for first run

    $result = curl_exec($ch);
    if($result === false) {
        $err = curl_error($ch);
        curl_close($ch);
        return 'Ollama request failed: ' . $err . '. Make sure Ollama is running (ollama serve)';
    }
    curl_close($ch);

    $decoded = json_decode($result, true);
    if(!$decoded) return 'Invalid response from Ollama.';

    // Extract response content
    if(isset($decoded['response'])) {
        return trim($decoded['response']);
    }

    return 'No response from model. Check Ollama is running.';
}

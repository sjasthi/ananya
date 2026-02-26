<?php
// LLM handler — supports Gemini (default), OpenAI, or Ollama
// Set LLM_PROVIDER in the root .env to switch providers.

function llm_ask($prompt, $opts = []) {
    $provider = strtolower(getenv('LLM_PROVIDER') ?: 'gemini');

    if ($provider === 'gemini') {
        return llm_ask_gemini($prompt, $opts);
    } elseif ($provider === 'openai') {
        return llm_ask_openai($prompt, $opts);
    } else {
        return llm_ask_ollama($prompt, $opts);
    }
}

function llm_ask_gemini($prompt, $opts = []) {
    $apiKey      = getenv('GEMINI_API_KEY');
    $model       = $opts['model'] ?? (getenv('LLM_MODEL') ?: 'gemini-2.0-flash');
    $temperature = $opts['temperature'] ?? 0.2;
    $systemPrompt = $opts['system_prompt'] ?? 'You are a helpful assistant. Provide concise, accurate answers.';

    if (!$apiKey) {
        return 'Gemini API key not configured. Set GEMINI_API_KEY in .env';
    }

    $url = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$apiKey}";

    $payload = [
        'system_instruction' => ['parts' => [['text' => $systemPrompt]]],
        'contents'           => [['role' => 'user', 'parts' => [['text' => $prompt]]]],
        'generationConfig'   => [
            'temperature'     => $temperature,
            'maxOutputTokens' => $opts['max_tokens'] ?? 1200,
        ],
    ];

    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => json_encode($payload),
        CURLOPT_HTTPHEADER     => ['Content-Type: application/json'],
        CURLOPT_TIMEOUT        => 60,
    ]);

    $result = curl_exec($ch);
    if ($result === false) {
        $err = curl_error($ch);
        curl_close($ch);
        return 'Gemini request failed: ' . $err;
    }
    curl_close($ch);

    $decoded = json_decode($result, true);
    if (!$decoded) return 'Invalid response from Gemini.';

    return trim($decoded['candidates'][0]['content']['parts'][0]['text'] ?? 'No response from Gemini.');
}

function llm_ask_openai($prompt, $opts = []) {
    $apiKey       = getenv('OPENAI_API_KEY');
    $model        = $opts['model'] ?? (getenv('LLM_MODEL') ?: 'gpt-4o-mini');
    $temperature  = $opts['temperature'] ?? 0.2;
    $systemPrompt = $opts['system_prompt'] ?? 'You are a helpful assistant. Provide concise, accurate answers.';

    if (!$apiKey) return 'OpenAI API key not configured. Set OPENAI_API_KEY in .env';

    $payload = [
        'model'       => $model,
        'temperature' => $temperature,
        'messages'    => [
            ['role' => 'system', 'content' => $systemPrompt],
            ['role' => 'user',   'content' => $prompt],
        ],
    ];

    $ch = curl_init('https://api.openai.com/v1/chat/completions');
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => json_encode($payload),
        CURLOPT_HTTPHEADER     => [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $apiKey,
        ],
        CURLOPT_TIMEOUT => 60,
    ]);

    $result = curl_exec($ch);
    if ($result === false) {
        $err = curl_error($ch);
        curl_close($ch);
        return 'OpenAI request failed: ' . $err;
    }
    curl_close($ch);

    $decoded = json_decode($result, true);
    return trim($decoded['choices'][0]['message']['content'] ?? 'No response from OpenAI.');
}

function llm_ask_ollama($prompt, $opts = []) {
    $ollamaUrl    = getenv('OLLAMA_URL') ?: 'http://localhost:11434';
    $model        = $opts['model'] ?? 'mistral';
    $temperature  = $opts['temperature'] ?? 0.2;
    $systemPrompt = $opts['system_prompt'] ?? 'You are a helpful assistant. Provide concise, accurate answers.';

    $payload = [
        'model'  => $model,
        'prompt' => $prompt,
        'system' => $systemPrompt,
        'stream' => false,
        'options' => ['temperature' => $temperature, 'top_k' => 40, 'top_p' => 0.9],
    ];

    $ch = curl_init($ollamaUrl . '/api/generate');
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => json_encode($payload),
        CURLOPT_HTTPHEADER     => ['Content-Type: application/json'],
        CURLOPT_TIMEOUT        => 300,
    ]);

    $result = curl_exec($ch);
    if ($result === false) {
        $err = curl_error($ch);
        curl_close($ch);
        return 'Ollama request failed: ' . $err . '. Make sure Ollama is running (ollama serve)';
    }
    curl_close($ch);

    $decoded = json_decode($result, true);
    if (!$decoded) return 'Invalid response from Ollama.';

    return trim($decoded['response'] ?? 'No response from model. Check Ollama is running.');
}

<?php
// LLM handler for shared hosting.
// Supports OpenAI-compatible chat completions with optional tool-calling.

function llm_default_model_for_provider($provider) {
    $defaults = [
        'ollama' => 'mistral',
        'gemini' => 'gemini-2.0-flash',
        'groq' => 'llama-3.3-70b-versatile',
        'openai' => 'gpt-4o-mini',
    ];

    return $defaults[$provider] ?? 'gpt-4o-mini';
}

function llm_get_provider_config($opts = []) {
    $provider = strtolower($opts['provider'] ?? (getenv('LLM_PROVIDER') ?: 'gemini'));
    $envModel = trim((string)(getenv('LLM_MODEL') ?: ''));
    $optModel = isset($opts['model']) ? trim((string)$opts['model']) : '';
    if ($optModel !== '') {
        $model = $optModel;
    } elseif ($envModel === '' || strtolower($envModel) === 'auto') {
        $model = llm_default_model_for_provider($provider);
    } else {
        $model = $envModel;
    }
    $timeout = (int)($opts['timeout'] ?? (getenv('LLM_TIMEOUT') ?: 90));

    if ($provider === 'openai') {
        $key = getenv('OPENAI_API_KEY') ?: '';
        return [
            'provider' => $provider,
            'model' => $model,
            'url' => 'https://api.openai.com/v1/chat/completions',
            'api_key' => $key,
            'timeout' => $timeout,
        ];
    }

    if ($provider === 'gemini') {
        $key = getenv('GEMINI_API_KEY') ?: '';
        return [
            'provider' => $provider,
            'model' => $model,
            'url' => 'https://generativelanguage.googleapis.com/v1beta/openai/chat/completions',
            'api_key' => $key,
            'timeout' => $timeout,
        ];
    }

    if ($provider === 'groq') {
        $key = getenv('GROQ_API_KEY') ?: '';
        return [
            'provider' => $provider,
            'model' => $model,
            'url' => 'https://api.groq.com/openai/v1/chat/completions',
            'api_key' => $key,
            'timeout' => $timeout,
        ];
    }

    return [
        'provider' => 'ollama',
        'model' => $model,
        'url' => (getenv('OLLAMA_URL') ?: 'http://localhost:11434') . '/api/generate',
        'api_key' => '',
        'timeout' => 300,
    ];
}

function llm_request_openai_compatible($messages, $opts = []) {
    $cfg = llm_get_provider_config($opts);
    if ($cfg['provider'] === 'ollama') {
        return [
            'ok' => false,
            'error' => 'Tool calling is not supported for ollama in this PHP path. Use OpenAI, Gemini, or Groq.',
        ];
    }

    if (!$cfg['api_key']) {
        return [
            'ok' => false,
            'error' => strtoupper($cfg['provider']) . ' API key not configured.',
        ];
    }

    $payload = [
        'model' => $cfg['model'],
        'messages' => $messages,
        'temperature' => $opts['temperature'] ?? 0.2,
        'max_tokens' => $opts['max_tokens'] ?? 1200,
    ];

    if (isset($opts['tools']) && is_array($opts['tools']) && count($opts['tools']) > 0) {
        $payload['tools'] = $opts['tools'];
        $payload['tool_choice'] = $opts['tool_choice'] ?? 'auto';
        $payload['parallel_tool_calls'] = $opts['parallel_tool_calls'] ?? false;
    }

    $ch = curl_init($cfg['url']);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => json_encode($payload),
        CURLOPT_HTTPHEADER => [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $cfg['api_key'],
        ],
        CURLOPT_TIMEOUT => $cfg['timeout'],
        CURLOPT_CONNECTTIMEOUT => 10,
    ]);

    $result = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    curl_close($ch);

    if ($result === false) {
        return [
            'ok' => false,
            'error' => $cfg['provider'] . ' request failed: ' . $curlError,
        ];
    }

    $decoded = json_decode($result, true);
    if (!is_array($decoded)) {
        return [
            'ok' => false,
            'error' => $cfg['provider'] . ' returned invalid JSON.',
            'http_code' => $httpCode,
            'raw' => substr((string)$result, 0, 800),
        ];
    }

    if ($httpCode < 200 || $httpCode >= 400) {
        $apiError = $decoded['error']['message'] ?? ($decoded['message'] ?? 'Unknown provider error');
        return [
            'ok' => false,
            'error' => $cfg['provider'] . ' API error (HTTP ' . $httpCode . '): ' . $apiError,
            'http_code' => $httpCode,
            'raw' => $decoded,
        ];
    }

    $message = $decoded['choices'][0]['message'] ?? null;
    if (!is_array($message)) {
        return [
            'ok' => false,
            'error' => 'No message in provider response.',
            'raw' => $decoded,
        ];
    }

    $toolCalls = [];
    if (isset($message['tool_calls']) && is_array($message['tool_calls'])) {
        foreach ($message['tool_calls'] as $tc) {
            if (!isset($tc['function']['name'])) {
                continue;
            }

            $toolCalls[] = [
                'id' => $tc['id'] ?? ('call_' . uniqid()),
                'name' => $tc['function']['name'],
                'arguments' => $tc['function']['arguments'] ?? '{}',
                'raw' => $tc,
            ];
        }
    }

    $assistantMessage = [
        'role' => 'assistant',
        'content' => $message['content'] ?? null,
    ];
    if (count($toolCalls) > 0) {
        $assistantMessage['tool_calls'] = array_map(function ($tc) {
            return $tc['raw'];
        }, $toolCalls);
    }

    return [
        'ok' => true,
        'content' => llm_extract_message_text($message['content'] ?? ''),
        'tool_calls' => $toolCalls,
        'assistant_message' => $assistantMessage,
        'raw' => $decoded,
    ];
}

function llm_extract_message_text($content) {
    if (is_string($content)) {
        return trim($content);
    }

    if (!is_array($content)) {
        return '';
    }

    $parts = [];
    foreach ($content as $part) {
        if (is_array($part) && isset($part['text'])) {
            $parts[] = $part['text'];
        }
    }

    return trim(implode("\n", $parts));
}

function llm_ask($prompt, $opts = []) {
    $provider = strtolower(getenv('LLM_PROVIDER') ?: 'gemini');

    if ($provider === 'ollama') {
        return llm_ask_ollama($prompt, $opts);
    }

    $messages = [];
    $systemPrompt = $opts['system_prompt'] ?? null;
    if (!empty($systemPrompt)) {
        $messages[] = ['role' => 'system', 'content' => $systemPrompt];
    }
    $messages[] = ['role' => 'user', 'content' => $prompt];

    $res = llm_request_openai_compatible($messages, [
        'provider' => $provider,
        'model' => $opts['model'] ?? null,
        'temperature' => $opts['temperature'] ?? 0.2,
        'max_tokens' => $opts['max_tokens'] ?? 1200,
        'timeout' => $opts['timeout'] ?? null,
    ]);

    if (!$res['ok']) {
        return $res['error'] ?? 'LLM request failed.';
    }

    return $res['content'] ?? '';
}

function llm_ask_ollama($prompt, $opts = []) {
    $ollamaUrl = getenv('OLLAMA_URL') ?: 'http://localhost:11434';
    $model = $opts['model'] ?? 'mistral';
    $temperature = $opts['temperature'] ?? 0.2;
    $systemPrompt = $opts['system_prompt'] ?? 'You are a helpful assistant. Provide concise, accurate answers.';

    $payload = [
        'model' => $model,
        'prompt' => $prompt,
        'system' => $systemPrompt,
        'stream' => false,
        'options' => ['temperature' => $temperature, 'top_k' => 40, 'top_p' => 0.9],
    ];

    $ch = curl_init($ollamaUrl . '/api/generate');
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => json_encode($payload),
        CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
        CURLOPT_TIMEOUT => 300,
    ]);

    $result = curl_exec($ch);
    if ($result === false) {
        $err = curl_error($ch);
        curl_close($ch);
        return 'Ollama request failed: ' . $err . '. Make sure Ollama is running (ollama serve)';
    }
    curl_close($ch);

    $decoded = json_decode($result, true);
    if (!$decoded) {
        return 'Invalid response from Ollama.';
    }

    return trim($decoded['response'] ?? 'No response from model. Check Ollama is running.');
}

function ananya_tool_schema($name, $description, $properties, $required = []) {
    return [
        'type' => 'function',
        'function' => [
            'name' => $name,
            'description' => $description,
            'parameters' => [
                'type' => 'object',
                'properties' => $properties,
                'required' => $required,
            ],
        ],
    ];
}

function ananya_chat_tools() {
    $pWord = ['word' => ['type' => 'string', 'description' => 'Input word or text']];
    $pLanguage = ['language' => ['type' => 'string', 'description' => 'Language like english/telugu/hindi/gujarati/malayalam']];

    return [
        ananya_tool_schema('reverse_text', 'Reverse a word.', $pWord + $pLanguage, ['word']),
        ananya_tool_schema('get_text_length', 'Get the length of a word.', $pWord + $pLanguage, ['word']),
        ananya_tool_schema('randomize_text', 'Shuffle logical characters in a word.', $pWord + $pLanguage, ['word']),
        ananya_tool_schema('split_text', 'Split text using a delimiter.', $pWord + ['delimiter' => ['type' => 'string']] + $pLanguage, ['word']),
        ananya_tool_schema('replace_in_text', 'Replace substring in text.', $pWord + ['search' => ['type' => 'string'], 'replace_with' => ['type' => 'string']] + $pLanguage, ['word', 'search']),

        ananya_tool_schema('get_logical_characters', 'Get logical characters of a word.', $pWord + $pLanguage, ['word']),
        ananya_tool_schema('get_base_characters', 'Get base characters of a word.', $pWord + $pLanguage, ['word']),
        ananya_tool_schema('get_code_points', 'Get Unicode code points for a word.', $pWord + $pLanguage, ['word']),
        ananya_tool_schema('get_character_at_position', 'Get logical character at index.', $pWord + ['index' => ['type' => 'integer']] + $pLanguage, ['word']),

        ananya_tool_schema('check_palindrome', 'Check if a word is palindrome.', $pWord + $pLanguage, ['word']),
        ananya_tool_schema('check_anagram', 'Check if two words are anagrams.', ['word1' => ['type' => 'string'], 'word2' => ['type' => 'string']] + $pLanguage, ['word1', 'word2']),
        ananya_tool_schema('can_make_word', 'Check if target_word can be formed from source_word.', ['source_word' => ['type' => 'string'], 'target_word' => ['type' => 'string']] + $pLanguage, ['source_word', 'target_word']),
        ananya_tool_schema('can_make_all_words', 'Check if all comma-separated words can be formed.', ['source_word' => ['type' => 'string'], 'words' => ['type' => 'string']] + $pLanguage, ['source_word', 'words']),
        ananya_tool_schema('get_word_strength', 'Get word strength value.', $pWord + $pLanguage, ['word']),
        ananya_tool_schema('get_word_weight', 'Get word weight value.', $pWord + $pLanguage, ['word']),
        ananya_tool_schema('get_word_level', 'Get word level value.', $pWord + $pLanguage, ['word']),
        ananya_tool_schema('detect_language', 'Detect language from text.', ['text' => ['type' => 'string']], ['text']),
        ananya_tool_schema('check_intersecting', 'Check if two words intersect.', ['word1' => ['type' => 'string'], 'word2' => ['type' => 'string']] + $pLanguage, ['word1', 'word2']),
        ananya_tool_schema('get_intersecting_rank', 'Get intersecting rank between two words.', ['word1' => ['type' => 'string'], 'word2' => ['type' => 'string']] + $pLanguage, ['word1', 'word2']),
        ananya_tool_schema('check_ladder_words', 'Check if words are ladder words.', ['word1' => ['type' => 'string'], 'word2' => ['type' => 'string']] + $pLanguage, ['word1', 'word2']),
        ananya_tool_schema('check_head_tail_words', 'Check if words are head-tail words.', ['word1' => ['type' => 'string'], 'word2' => ['type' => 'string']] + $pLanguage, ['word1', 'word2']),
        ananya_tool_schema('parse_to_logical_chars', 'Parse word to logical chars.', $pWord + $pLanguage, ['word']),

        ananya_tool_schema('check_starts_with', 'Check if a word starts with prefix.', $pWord + ['prefix' => ['type' => 'string']] + $pLanguage, ['word', 'prefix']),
        ananya_tool_schema('check_ends_with', 'Check if a word ends with suffix.', $pWord + ['suffix' => ['type' => 'string']] + $pLanguage, ['word', 'suffix']),
        ananya_tool_schema('compare_words', 'Compare two words.', ['word1' => ['type' => 'string'], 'word2' => ['type' => 'string']] + $pLanguage, ['word1', 'word2']),
        ananya_tool_schema('check_equals', 'Check if two words are equal.', ['word1' => ['type' => 'string'], 'word2' => ['type' => 'string']] + $pLanguage, ['word1', 'word2']),
        ananya_tool_schema('check_reverse_equals', 'Check if one word equals reverse of another.', ['word1' => ['type' => 'string'], 'word2' => ['type' => 'string']] + $pLanguage, ['word1', 'word2']),
        ananya_tool_schema('find_index_of', 'Find index of search in a word.', $pWord + ['search' => ['type' => 'string']] + $pLanguage, ['word', 'search']),

        ananya_tool_schema('check_contains_char', 'Check if word contains char.', $pWord + ['char' => ['type' => 'string']] + $pLanguage, ['word', 'char']),
        ananya_tool_schema('check_contains_string', 'Check if word contains substring.', $pWord + ['substring' => ['type' => 'string']] + $pLanguage, ['word', 'substring']),
        ananya_tool_schema('check_is_consonant', 'Check if character is consonant.', ['character' => ['type' => 'string']] + $pLanguage, ['character']),
        ananya_tool_schema('check_is_vowel', 'Check if character is vowel.', ['character' => ['type' => 'string']] + $pLanguage, ['character']),
        ananya_tool_schema('check_contains_space', 'Check if word contains a space.', $pWord + $pLanguage, ['word']),

        ananya_tool_schema('get_length_no_spaces', 'Get text length after removing spaces.', $pWord + $pLanguage, ['word']),
    ];
}

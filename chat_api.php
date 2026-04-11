<?php
header('Content-Type: application/json; charset=utf-8');
// chat_api.php - receives question and performs in-process LLM + tool orchestration

require_once __DIR__ . '/includes/api_reference.php';
require_once __DIR__ . '/includes/llm_handler.php';
llm_bootstrap_env_once(__DIR__);

$MODERATION_BLOCKLISTS = [];
$moderationConfigPath = __DIR__ . '/includes/moderation_blocklists.php';
if (file_exists($moderationConfigPath)) {
    $loadedModerationBlocklists = require $moderationConfigPath;
    if (is_array($loadedModerationBlocklists)) {
        $MODERATION_BLOCKLISTS = $loadedModerationBlocklists;
    }
}

$THEME_BLOCKLISTS = [];
$themeConfigPath = __DIR__ . '/includes/theme_blocklists.php';
if (file_exists($themeConfigPath)) {
    $loadedThemeBlocklists = require $themeConfigPath;
    if (is_array($loadedThemeBlocklists)) {
        $THEME_BLOCKLISTS = $loadedThemeBlocklists;
    }
}

// Tool-loop settings
$CHAT_MAX_TOOL_ITERS = (int)(getenv('CHAT_MAX_TOOL_ITERS') ?: 5);
$MCP_SERVER_URL = getenv('MCP_SERVER_URL') ?: 'http://localhost:8000/chat';
$MCP_TIMEOUT = (int)(getenv('MCP_TIMEOUT') ?: 20);

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

function is_generation_request($question) {
    $q = strtolower(trim((string)$question));
    if ($q === '') return false;

    // Puzzle-generation prompts should not be forced into API-call JSON extraction.
    if (preg_match('/\b(word\s*find|word\s*search|wordsearch|crossword|puzzle)\b/u', $q)) {
        return true;
    }

    if (preg_match('/(క్రాస్[\p{L}\p{M}]*|పజిల్|వర్డ్\s*ఫైండ్|వర్డ్\s*సెర్చ్|రూపొందించండి|సృష్టించండి|తయారు\s*చేయండి)/u', $question)) {
        return true;
    }

    if (preg_match('/\b(create|generate|make)\b/u', $q) && preg_match('/\bwords?\b/u', $q)) {
        return true;
    }

    return false;
}

function is_crossword_request($question) {
    $q = strtolower(trim((string)$question));
    if ($q === '') {
        return false;
    }

    return preg_match('/\bcrossword\b/u', $q) === 1
        || preg_match('/క్రాస్[\p{L}\p{M}]*/u', $question) === 1;
}

function is_word_find_request($question) {
    $q = strtolower(trim((string)$question));
    if ($q === '') {
        return false;
    }

    if (preg_match('/\b(word\s*find|word\s*search|wordsearch)\b/u', $q) === 1) {
        return true;
    }

    // A "puzzle" request that is not a crossword is treated as a word find puzzle.
    if (preg_match('/\bpuzzle\b/u', $q) === 1 && !preg_match('/\bcrossword\b/u', $q)) {
        return true;
    }

    return preg_match('/(వర్డ్\s*ఫైండ్|వర్డ్\s*సెర్చ్|పద\s*శోధన|పజిల్)/u', $question) === 1;
}

function extract_theme_from_question($question) {
    $q = trim((string)$question);
    if ($q === '') {
        return 'general words';
    }

    $patterns = [
        '/\b(?:a|an)\s+([a-zA-Z][a-zA-Z-]{1,40})\s+themed\b/i',
        '/\b([a-zA-Z][a-zA-Z-]{1,40})\s+themed\b/i',
        '/\b(?:about|related to|on|for)\s+([a-zA-Z\s-]{2,80})/i',
        '/\bwith\s+(?:an?|the)\s+([a-zA-Z\s-]{2,80})\s+theme\b/i',
        '/\b([a-zA-Z\s-]{2,80})\s+theme\b/i',
        '/\bthemed\s+(?:around|on)\s+([a-zA-Z\s-]{2,80})/i',
        '/([\p{L}\p{M}\s-]{2,80})\s+గురించి/u',
        '/([\p{L}\p{M}\s-]{2,80})\s+థీమ్/u',
    ];

    foreach ($patterns as $pattern) {
        if (preg_match($pattern, $q, $m)) {
            $theme = trim($m[1]);
            $theme = preg_replace('/\b(words?|puzzle|crossword|word\s*find)\b/i', '', $theme);
            $theme = preg_replace('/\b(i|want|need|make|create|generate|a|an|the|themed)\b/i', '', $theme);
            $theme = trim(preg_replace('/\s+/', ' ', $theme));
            if ($theme !== '') {
                return $theme;
            }
        }
    }

    return 'general words';
}

function canonicalize_theme($theme) {
    $t = trim(mb_strtolower($theme, 'UTF-8'));
    if ($t === '') {
        return 'general words';
    }

    $aliases = [
        'dog' => ['dog', 'dogs', 'canine', 'puppy', 'puppies', 'కుక్క', 'కుక్కలు', 'శునకం', 'శునకాలు'],
        'cat' => ['cat', 'cats', 'feline', 'kitten', 'kittens', 'పిల్లి', 'పిల్లులు'],
        'fruit' => ['fruit', 'fruits', 'apple', 'banana', 'orange', 'mango', 'grape', 'grapes', 'pineapple', 'papaya', 'melon', 'kiwi', 'pear', 'peach', 'plum', 'పండు', 'పండ్లు', 'మామిడి', 'సేపు', 'అరటి', 'ద్రాక్ష'],
    ];

    foreach ($aliases as $canonical => $variants) {
        foreach ($variants as $variant) {
            if (mb_strpos($t, mb_strtolower($variant, 'UTF-8'), 0, 'UTF-8') !== false) {
                return $canonical;
            }
        }
    }

    return $theme;
}

function parse_word_find_request($question) {
    $q = trim((string)$question);
    if ($q === '') return null;

    if (is_crossword_request($q) || !is_word_find_request($q)) {
        return null;
    }

    $count = 10;
    if (preg_match('/\b(\d{1,2})\s+words?\b/i', $q, $m)) {
        $count = (int)$m[1];
    } elseif (preg_match('/\bwith\s+(\d{1,2})\b/i', $q, $m2)) {
        $count = (int)$m2[1];
    } elseif (preg_match('/\bcount\s*[=:]\s*(\d{1,2})\b/i', $q, $m3)) {
        $count = (int)$m3[1];
    }
    $count = max(3, min(20, $count));

    // Parse explicit grid dimensions, e.g. "16 x 12" or "16x12". Default 16 cols x 12 rows.
    $gridCols = 16;
    $gridRows = 12;
    if (preg_match('/\b(\d{1,2})\s*[x×]\s*(\d{1,2})\b/i', $q, $gm)) {
        $gridCols = max(8, min(30, (int)$gm[1]));
        $gridRows = max(6, min(30, (int)$gm[2]));
    }

    $theme = canonicalize_theme(extract_theme_from_question($q));

    return [
        'count'    => $count,
        'theme'    => $theme,
        'gridCols' => $gridCols,
        'gridRows' => $gridRows,
    ];
}

function parse_crossword_request($question) {
    $q = trim((string)$question);
    if ($q === '') return null;

    if (!is_crossword_request($q)) {
        return null;
    }

    $count = 10;
    if (preg_match('/\b(\d{1,2})\s+words?\b/i', $q, $m)) {
        $count = (int)$m[1];
    } elseif (preg_match('/\bwith\s+(\d{1,2})\b/i', $q, $m2)) {
        $count = (int)$m2[1];
    }
    $count = max(3, min(20, $count));

    $theme = canonicalize_theme(extract_theme_from_question($q));

    return [
        'count' => $count,
        'theme' => $theme,
    ];
}

function normalize_supported_language($language) {
    $lang = strtolower(trim((string)$language));
    $supported = ['english', 'telugu', 'hindi', 'gujarati', 'malayalam'];
    return in_array($lang, $supported, true) ? $lang : 'english';
}

function is_indic_language($language) {
    return in_array(normalize_supported_language($language), ['telugu', 'hindi', 'gujarati', 'malayalam'], true);
}

function language_name_for_prompt($language) {
    $lang = normalize_supported_language($language);
    $names = [
        'english' => 'English',
        'telugu' => 'Telugu',
        'hindi' => 'Hindi',
        'gujarati' => 'Gujarati',
        'malayalam' => 'Malayalam',
    ];

    return $names[$lang] ?? 'English';
}

function normalize_word_for_grid($word, $language = 'english') {
    $language = normalize_supported_language($language);
    $text = trim((string)$word);
    if ($language !== 'english') {
        $text = preg_replace('/[^\p{L}\p{M}]/u', '', $text);
        return $text;
    }

    $upper = strtoupper($text);
    $upper = preg_replace('/[^A-Z]/', '', $upper);
    return $upper;
}

function normalize_text_for_moderation($text, $language = 'english') {
    $language = normalize_supported_language($language);
    $value = mb_strtolower(trim((string)$text), 'UTF-8');

    if ($value === '') {
        return '';
    }

    if ($language === 'english') {
        $value = strtr($value, [
            '0' => 'o',
            '1' => 'i',
            '3' => 'e',
            '4' => 'a',
            '5' => 's',
            '7' => 't',
            '@' => 'a',
            '$' => 's',
            '!' => 'i',
        ]);
        return preg_replace('/[^a-z]/', '', $value);
    }

    return preg_replace('/[^\p{L}\p{M}]/u', '', $value);
}

function inappropriate_terms_for_language($language = 'english') {
    global $MODERATION_BLOCKLISTS;
    $language = normalize_supported_language($language);

    $commonEnglish = [
        'fuck', 'shit', 'bitch', 'asshole', 'bastard', 'dick',
        'pussy', 'porn', 'nude', 'boob', 'penis', 'vagina',
        'rape', 'cum', 'sex'
    ];

    $configured = is_array($MODERATION_BLOCKLISTS) ? $MODERATION_BLOCKLISTS : [];
    $languageSpecific = [];
    if (isset($configured[$language]) && is_array($configured[$language])) {
        $languageSpecific = $configured[$language];
    }
    $englishConfigured = [];
    if (isset($configured['english']) && is_array($configured['english'])) {
        $englishConfigured = $configured['english'];
    }

    $merged = array_merge($commonEnglish, $englishConfigured, $languageSpecific);
    $clean = [];
    foreach ($merged as $term) {
        $v = trim((string)$term);
        if ($v !== '') {
            $clean[] = $v;
        }
    }

    return array_values(array_unique($clean));
}

function is_inappropriate_text($text, $language = 'english') {
    $language = normalize_supported_language($language);
    $normalizedByLang = normalize_text_for_moderation($text, $language);
    $normalizedEnglish = normalize_text_for_moderation($text, 'english');
    $terms = inappropriate_terms_for_language($language);

    foreach ($terms as $term) {
        $needleByLang = normalize_text_for_moderation($term, $language);
        $needleEnglish = normalize_text_for_moderation($term, 'english');

        if ($needleByLang === '' && $needleEnglish === '') {
            continue;
        }

        if ($needleByLang !== '' && $normalizedByLang !== '' && strpos($normalizedByLang, $needleByLang) !== false) {
            return true;
        }
        if ($needleEnglish !== '' && $normalizedEnglish !== '' && strpos($normalizedEnglish, $needleEnglish) !== false) {
            return true;
        }
    }

    return false;
}

function is_inappropriate_text_any_language($text) {
    $supported = ['english', 'telugu', 'hindi', 'gujarati', 'malayalam'];
    foreach ($supported as $lang) {
        if (is_inappropriate_text($text, $lang)) {
            return true;
        }
    }

    return false;
}

function disallowed_theme_keywords_for_language($language = 'english') {
    global $THEME_BLOCKLISTS;

    $language = normalize_supported_language($language);
    $configured = is_array($THEME_BLOCKLISTS) ? $THEME_BLOCKLISTS : [];

    $defaultEnglish = [
        'recreational drugs', 'drug abuse', 'narcotic', 'marijuana', 'cannabis',
        'weed', 'hashish', 'cocaine', 'heroin', 'meth', 'methamphetamine',
        'opium', 'lsd', 'pcp'
    ];

    $englishConfigured = [];
    if (isset($configured['english']) && is_array($configured['english'])) {
        $englishConfigured = $configured['english'];
    }

    $languageSpecific = [];
    if (isset($configured[$language]) && is_array($configured[$language])) {
        $languageSpecific = $configured[$language];
    }

    $merged = array_merge($defaultEnglish, $englishConfigured, $languageSpecific);
    $clean = [];
    foreach ($merged as $keyword) {
        $k = trim((string)$keyword);
        if ($k !== '') {
            $clean[] = $k;
        }
    }

    return array_values(array_unique($clean));
}

function is_disallowed_theme_or_topic($text, $language = 'english') {
    $language = normalize_supported_language($language);
    $normalizedByLang = normalize_text_for_moderation($text, $language);
    $normalizedEnglish = normalize_text_for_moderation($text, 'english');
    $keywords = disallowed_theme_keywords_for_language($language);

    foreach ($keywords as $keyword) {
        $needleByLang = normalize_text_for_moderation($keyword, $language);
        $needleEnglish = normalize_text_for_moderation($keyword, 'english');

        if ($needleByLang === '' && $needleEnglish === '') {
            continue;
        }

        if ($needleByLang !== '' && $normalizedByLang !== '' && strpos($normalizedByLang, $needleByLang) !== false) {
            return true;
        }

        if ($needleEnglish !== '' && $normalizedEnglish !== '' && strpos($normalizedEnglish, $needleEnglish) !== false) {
            return true;
        }
    }

    return false;
}

function is_disallowed_theme_or_topic_any_language($text) {
    $supported = ['english', 'telugu', 'hindi', 'gujarati', 'malayalam'];
    foreach ($supported as $lang) {
        if (is_disallowed_theme_or_topic($text, $lang)) {
            return true;
        }
    }

    return false;
}

function is_contextually_harmful_targeting($text, $language = 'english') {
    $language = normalize_supported_language($language);
    $raw = mb_strtolower(trim((string)$text), 'UTF-8');
    if ($raw === '') {
        return false;
    }

    // Educational framing should remain allowed.
    if (preg_match('/\b(prevent|prevention|awareness|anti[-\s]?bully|stop\s+bully|kindness|respect|handle\s+bully|de[-\s]?escalat|self\s*control)\b/u', $raw)) {
        return false;
    }

    // Direct person-targeted humiliation patterns (e.g., "how Jenny smells bad").
    if (preg_match('/\bhow\s+[\p{L}]+\s+(smells?\s+bad|is\s+(ugly|stupid|dumb|gross|fat|worthless))\b/u', $raw)) {
        return true;
    }

    // Violent/abusive intent paired with a personal target context.
    $hasHarmIntent = preg_match('/\b(beat\s*up|hurt|attack|assault|harass|bully|humiliat|shame|insult|mock|slap|punch|kick|threaten)\b/u', $raw) === 1;
    $hasTargetContext = preg_match('/\b(class|classmate|student|kid|boy|girl|that\s+[\p{L}]+|from\s+class|him|her|them|person)\b/u', $raw) === 1;
    if ($hasHarmIntent && $hasTargetContext) {
        return true;
    }

    // Name-calling frames around a person.
    $hasInsultWord = preg_match('/\b(stupid|idiot|loser|freak|freakazoid|moron)\b/u', $raw) === 1;
    if ($hasInsultWord && $hasTargetContext) {
        return true;
    }

    return false;
}

function moderate_outbound_answer($answer, $language = 'english') {
    $text = trim((string)$answer);
    if ($text === '') {
        return $answer;
    }

    if (
        is_inappropriate_text($text, $language)
        || is_inappropriate_text_any_language($text)
        || is_disallowed_theme_or_topic($text, $language)
        || is_disallowed_theme_or_topic_any_language($text)
        || is_contextually_harmful_targeting($text, $language)
    ) {
        return 'I cannot provide puzzle content for that request. Please choose a classroom-safe theme.';
    }

    return $answer;
}

function split_logical_units_via_ananya_api($word, $language = 'english') {
    static $cache = [];

    $text = trim((string)$word);
    if ($text === '') {
        return [];
    }

    $cacheKey = $language . '|' . $text;
    if (isset($cache[$cacheKey])) {
        return $cache[$cacheKey];
    }

    $baseUrl = build_local_api_base_url();
    $url = rtrim($baseUrl, '/') . '/analysis/parse-to-logical-chars?' . http_build_query([
        'string' => $text,
        'language' => $language,
    ]);

    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => (int)(getenv('LOCAL_API_TIMEOUT') ?: 15),
        CURLOPT_CONNECTTIMEOUT => 5,
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    // curl_close($ch);

    if ($response === false || $httpCode < 200 || $httpCode >= 400) {
        return [];
    }

    $decoded = json_decode($response, true);
    if (!is_array($decoded)) {
        return [];
    }

    $raw = $decoded['result'] ?? ($decoded['data'] ?? null);
    $units = [];

    if (is_array($raw)) {
        foreach ($raw as $item) {
            if (is_string($item) && $item !== '') {
                $units[] = $item;
            }
        }
    } elseif (is_string($raw) && trim($raw) !== '') {
        $parts = preg_split('/[\s,|]+/u', trim($raw));
        foreach ($parts as $part) {
            if ($part !== '') {
                $units[] = $part;
            }
        }
    }

    $cache[$cacheKey] = $units;
    return $units;
}

function split_word_units($word, $language = 'english') {
    $language = normalize_supported_language($language);
    $text = (string)$word;
    if ($text === '') {
        return [];
    }

    if ($language !== 'english') {
        // For Indic puzzle generation, prefer Ananya API logical-char parsing.
        $apiUnits = split_logical_units_via_ananya_api($text, $language);
        if (!empty($apiUnits)) {
            return $apiUnits;
        }

        if (preg_match_all('/\X/u', $text, $m) && !empty($m[0])) {
            return $m[0];
        }
        return preg_split('//u', $text, -1, PREG_SPLIT_NO_EMPTY);
    }

    return str_split($text);
}

function sanitize_word_list($words, $maxCount, $language = 'english', $maxLen = 16) {
    $seen = [];
    $clean = [];
    foreach ($words as $w) {
        $nw = normalize_word_for_grid($w, $language);
        if ($nw === '' || is_inappropriate_text($nw, $language)) {
            continue;
        }
        $len = count(split_word_units($nw, $language));
        if ($len < 3 || $len > $maxLen) {
            continue;
        }
        if (isset($seen[$nw])) {
            continue;
        }
        $seen[$nw] = true;
        $clean[] = $nw;
        if (count($clean) >= $maxCount) {
            break;
        }
    }
    return $clean;
}

function fallback_theme_words($theme, $language = 'english') {
    $language = normalize_supported_language($language);
    $t = strtolower(canonicalize_theme($theme));

    if ($language === 'telugu') {
        if ($t === 'fruit') {
            return ['మామిడి', 'సేపు', 'అరటి', 'ద్రాక్ష', 'నారింజ', 'పపయా', 'జామ', 'పుచ్చకాయ', 'దానిమ్మ', 'పెరు', 'బత్తాయి', 'సీతాఫలం'];
        }
        if (preg_match('/\bdog|dogs|canine|puppy|puppies\b/', $t)) {
            return ['కుక్క', 'పిల్లకుక్క', 'శునకం', 'మొరుగు', 'తోక', 'పంజా', 'కాలర్', 'బెల్ట్', 'కెనెల్', 'వఫ్', 'ముక్కు', 'రోమాలు'];
        }
        if (preg_match('/\bcat|cats|feline|kitten\b/', $t)) {
            return ['పిల్లి', 'పిల్లిపిల్ల', 'మ్యావ్', 'మీసాలు', 'పంజా', 'తోక', 'రోమాలు', 'గోర్లు'];
        }

        return ['పర్వతం', 'నది', 'మేఘం', 'ఆకాశం', 'పుస్తకం', 'పాట', 'చెట్టు', 'పువ్వు', 'సముద్రం', 'వెలుగు'];
    }

    if ($language === 'hindi') {
        if ($t === 'fruit') {
            return ['सेब', 'केला', 'आम', 'अंगूर', 'संतरा', 'अनार', 'पपीता', 'अमरूद', 'नाशपाती', 'तरबूज'];
        }
        if (preg_match('/\bdog|dogs|canine|puppy|puppies\b/', $t)) {
            return ['कुत्ता', 'पिल्ला', 'भौंक', 'पूंछ', 'पंजा', 'कालर', 'घर', 'मित्र'];
        }
        if (preg_match('/\bcat|cats|feline|kitten\b/', $t)) {
            return ['बिल्ली', 'बच्चा', 'म्याऊँ', 'पंजा', 'मूंछ', 'पूंछ', 'नींद', 'नाखून'];
        }
        return ['पर्वत', 'नदी', 'बादल', 'आकाश', 'पुस्तक', 'पेड़', 'फूल', 'समुद्र', 'रोशनी', 'हवा'];
    }

    if ($language === 'gujarati') {
        if ($t === 'fruit') {
            return ['સફરજન', 'કેળું', 'કેરી', 'દ્રાક્ષ', 'સંતરો', 'દાડમ', 'પપૈયું', 'જામફળ', 'નાશપતી', 'તરબૂચ'];
        }
        if (preg_match('/\bdog|dogs|canine|puppy|puppies\b/', $t)) {
            return ['કૂતરો', 'પિલ્લું', 'ભૂંક', 'પૂંછડી', 'પંજો', 'કોલર', 'ઘર', 'મિત્ર'];
        }
        if (preg_match('/\bcat|cats|feline|kitten\b/', $t)) {
            return ['બિલાડી', 'બચ્ચું', 'મિયાંઉ', 'પંજો', 'મૂછ', 'પૂંછડી', 'ઊંઘ', 'નખ'];
        }
        return ['પર્વત', 'નદી', 'વાદળ', 'આકાશ', 'પુસ્તક', 'ઝાડ', 'ફૂલ', 'સમુદ્ર', 'પ્રકાશ', 'પવન'];
    }

    if ($language === 'malayalam') {
        if ($t === 'fruit') {
            return ['ആപ്പിൾ', 'വാഴപ്പഴം', 'മാമ്പഴം', 'മുന്തിരി', 'ഓറഞ്ച്', 'മാതളം', 'പപ്പായ', 'പേര', 'നാഷ്പതി', 'തണ്ണിമത്തൻ'];
        }
        if (preg_match('/\bdog|dogs|canine|puppy|puppies\b/', $t)) {
            return ['നായ', 'കുഞ്ഞ്', 'കുരയ്‌ക്കുക', 'വാൽ', 'കാൽപ്പാദം', 'കോളർ', 'വീട്', 'സുഹൃത്ത്'];
        }
        if (preg_match('/\bcat|cats|feline|kitten\b/', $t)) {
            return ['പൂച്ച', 'കുഞ്ഞ്', 'മ്യാവ്', 'കാൽപ്പാദം', 'മീശ', 'വാൽ', 'ഉറക്കം', 'നഖം'];
        }
        return ['പർവ്വതം', 'നദി', 'മേഘം', 'ആകാശം', 'പുസ്തകം', 'മരം', 'പൂവ്', 'സമുദ്രം', 'വെളിച്ചം', 'കാറ്റ്'];
    }

    if ($t === 'fruit') {
        return ['APPLE', 'BANANA', 'MANGO', 'ORANGE', 'GRAPE', 'PAPAYA', 'PEACH', 'PEAR', 'PLUM', 'MELON', 'KIWI', 'BERRY', 'CHERRY', 'GUAVA', 'LEMON', 'LIME', 'FIG', 'DATE', 'APRICOT', 'PINEAPPLE'];
    }

    if (preg_match('/\bdog|dogs|canine|puppy|puppies\b/', $t)) {
        return ['DOG', 'PUPPY', 'CANINE', 'LEASH', 'PAWS', 'BARK', 'TAIL', 'FUR', 'KENNEL', 'BEAGLE', 'COLLIE', 'RETRIEVER', 'HOUND', 'BONE', 'FETCH', 'WOOF', 'SNOUT', 'WHISKER', 'SHEPHERD', 'TERRIER'];
    }

    if (preg_match('/\bcat|cats|feline|kitten\b/', $t)) {
        return ['CAT', 'KITTEN', 'FELINE', 'PAWS', 'MEOW', 'WHISKER', 'LITTER', 'CLAWS', 'PURR', 'TABBY', 'SIAMESE', 'TAIL', 'FUR', 'NAP', 'SCRATCH'];
    }

    return ['ALPHA', 'BRAVO', 'CHARLIE', 'DELTA', 'ECHO', 'FOXTROT', 'GAMMA', 'HARBOR', 'ISLAND', 'JUNGLE', 'KAPPA', 'LAGOON', 'MEADOW', 'NEBULA', 'ORBIT', 'PLANET', 'QUARTZ', 'RIVER', 'SUMMIT', 'THUNDER'];
}

function localized_theme_label($theme, $language = 'english') {
    $language = normalize_supported_language($language);
    $canonical = strtolower(canonicalize_theme($theme));
    if ($language === 'telugu') {
        if ($canonical === 'dog') return 'కుక్కలు';
        if ($canonical === 'cat') return 'పిల్లులు';
        if ($canonical === 'fruit') return 'పండ్లు';
    } elseif ($language === 'hindi') {
        if ($canonical === 'dog') return 'कुत्ते';
        if ($canonical === 'cat') return 'बिल्लियाँ';
        if ($canonical === 'fruit') return 'फल';
    } elseif ($language === 'gujarati') {
        if ($canonical === 'dog') return 'કૂતરા';
        if ($canonical === 'cat') return 'બિલાડીઓ';
        if ($canonical === 'fruit') return 'ફળો';
    } elseif ($language === 'malayalam') {
        if ($canonical === 'dog') return 'നായകൾ';
        if ($canonical === 'cat') return 'പൂച്ചകൾ';
        if ($canonical === 'fruit') return 'ഫലങ്ങൾ';
    }
    return $theme;
}

function parse_words_from_llm_text($text) {
    $items = [];
    $lines = preg_split('/\r?\n/', (string)$text);
    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '') continue;
        $line = preg_replace('/^[-*\d.\)\s]+/', '', $line);
        $parts = preg_split('/[,;]+/', $line);
        foreach ($parts as $p) {
            $p = trim($p);
            if ($p !== '') {
                $items[] = $p;
            }
        }
    }
    return $items;
}

function request_theme_words_from_llm($theme, $count, $llm_provider, $llm_model, $language = 'english') {
    $language = normalize_supported_language($language);
    $languageName = language_name_for_prompt($language);

    $opts = [
        'temperature' => 0.2,
        'max_tokens' => 220,
        'system_prompt' => 'Return only a plain list of single ' . $languageName . ' words, one per line, no numbering, no punctuation, no extra text. Avoid profanity, slurs, sexual terms, and other inappropriate words.',
    ];

    if ($llm_provider !== '') {
        $opts['provider'] = $llm_provider;
    }
    if ($llm_model !== '') {
        $opts['model'] = $llm_model;
    }

    if ($language === 'telugu') {
        $prompt = $theme . " అనే థీమ్‌కు సంబంధించిన " . ($count + 8) . " ఒక్కో తెలుగు పదాలు ఇవ్వండి.";
    } else {
        $prompt = "Give " . ($count + 8) . " single-word " . $languageName . " terms related to this theme: " . $theme . ".";
    }
    $resp = llm_ask($prompt, $opts);

    // llm_ask returns plain text on success and error text on failure.
    if (!is_string($resp) || trim($resp) === '') {
        error_log('WordFind LLM candidates: empty response. provider=' . ($llm_provider ?: 'default') . ', model=' . ($llm_model ?: 'default') . ', theme=' . $theme . ', language=' . $language);
        return [[], false, false, ''];
    }
    if (stripos($resp, 'API error') !== false || stripos($resp, 'not configured') !== false || stripos($resp, 'request failed') !== false) {
        error_log('WordFind LLM candidates: provider error. provider=' . ($llm_provider ?: 'default') . ', model=' . ($llm_model ?: 'default') . ', theme=' . $theme . ', language=' . $language . ', resp=' . substr(trim($resp), 0, 240));
        return [[], true, true, trim((string)$resp)];
    }

    $parsed = parse_words_from_llm_text($resp);
    error_log('WordFind LLM candidates: parsed=' . count($parsed) . ', provider=' . ($llm_provider ?: 'default') . ', model=' . ($llm_model ?: 'default') . ', theme=' . $theme . ', language=' . $language);
    return [$parsed, true, false, ''];
}

function can_place_word($grid, $nRows, $nCols, $units, $row, $col, $dr, $dc) {
    $len = count($units);
    for ($i = 0; $i < $len; $i++) {
        $r = $row + ($dr * $i);
        $c = $col + ($dc * $i);
        if ($r < 0 || $r >= $nRows || $c < 0 || $c >= $nCols) {
            return false;
        }
        $cell = $grid[$r][$c];
        if ($cell !== '' && $cell !== $units[$i]) {
            return false;
        }
    }
    return true;
}

function place_word_in_grid(&$grid, $units, $row, $col, $dr, $dc) {
    $len = count($units);
    for ($i = 0; $i < $len; $i++) {
        $r = $row + ($dr * $i);
        $c = $col + ($dc * $i);
        $grid[$r][$c] = $units[$i];
    }
}

function crossword_can_place($grid, $size, $word, $row, $col, $orientation) {
    $len = strlen($word);
    $dr = $orientation === 'down' ? 1 : 0;
    $dc = $orientation === 'across' ? 1 : 0;
    $hasIntersection = false;

    for ($i = 0; $i < $len; $i++) {
        $r = $row + ($dr * $i);
        $c = $col + ($dc * $i);
        if ($r < 0 || $r >= $size || $c < 0 || $c >= $size) {
            return false;
        }

        $cell = $grid[$r][$c];
        if ($cell !== '' && $cell !== $word[$i]) {
            return false;
        }
        if ($cell === $word[$i] && $cell !== '') {
            $hasIntersection = true;
        }

        if ($orientation === 'across') {
            if ($cell === '') {
                if (($r > 0 && $grid[$r - 1][$c] !== '') || ($r < $size - 1 && $grid[$r + 1][$c] !== '')) {
                    return false;
                }
            }
        } else {
            if ($cell === '') {
                if (($c > 0 && $grid[$r][$c - 1] !== '') || ($c < $size - 1 && $grid[$r][$c + 1] !== '')) {
                    return false;
                }
            }
        }
    }

    $beforeR = $row - $dr;
    $beforeC = $col - $dc;
    $afterR = $row + ($dr * $len);
    $afterC = $col + ($dc * $len);

    if ($beforeR >= 0 && $beforeR < $size && $beforeC >= 0 && $beforeC < $size && $grid[$beforeR][$beforeC] !== '') {
        return false;
    }
    if ($afterR >= 0 && $afterR < $size && $afterC >= 0 && $afterC < $size && $grid[$afterR][$afterC] !== '') {
        return false;
    }

    // Require intersection for all but the first placed word.
    $gridHasLetters = false;
    for ($r = 0; $r < $size && !$gridHasLetters; $r++) {
        for ($c = 0; $c < $size; $c++) {
            if ($grid[$r][$c] !== '') {
                $gridHasLetters = true;
                break;
            }
        }
    }

    if ($gridHasLetters && !$hasIntersection) {
        return false;
    }

    return true;
}

function crossword_place_word(&$grid, $word, $row, $col, $orientation) {
    $len = strlen($word);
    $dr = $orientation === 'down' ? 1 : 0;
    $dc = $orientation === 'across' ? 1 : 0;
    for ($i = 0; $i < $len; $i++) {
        $r = $row + ($dr * $i);
        $c = $col + ($dc * $i);
        $grid[$r][$c] = $word[$i];
    }
}

function score_crossword_layout($width, $height, $filledCells, $placedCount, $acrossCount, $downCount) {
    $area = max(1, $width * $height);
    $density = $filledCells / $area;
    $shape = min($width, $height) / max($width, $height);
    $balance = 1 - (abs($acrossCount - $downCount) / max(1, $placedCount));

    return ($placedCount * 1000)
        + ($density * 250)
        + ($shape * 150)
        + ($balance * 75)
        - ($area * 0.35);
}

function build_crossword_puzzle($words) {
    $words = array_values($words);
    if (count($words) < 3) {
        return null;
    }

    usort($words, function($a, $b) {
        return strlen($b) <=> strlen($a);
    });

    $longest = 0;
    foreach ($words as $w) {
        $longest = max($longest, strlen($w));
    }

    $baseSize = max(9, min(21, $longest + max(4, (int)ceil(count($words) / 2) + 2)));

    $bestPuzzle = null;

    for ($grow = 0; $grow < 6; $grow++) {
        $size = min(24, $baseSize + $grow);
        for ($attempt = 0; $attempt < 60; $attempt++) {
            $grid = array_fill(0, $size, array_fill(0, $size, ''));
            $placed = [];

            $first = $words[0];
            $startRow = intdiv($size, 2);
            $startCol = max(0, intdiv($size - strlen($first), 2));
            crossword_place_word($grid, $first, $startRow, $startCol, 'across');
            $placed[] = ['word' => $first, 'row' => $startRow, 'col' => $startCol, 'orientation' => 'across'];

            for ($wi = 1; $wi < count($words); $wi++) {
                $word = $words[$wi];
                $candidates = [];

                foreach ($placed as $pw) {
                    $existing = $pw['word'];
                    for ($i = 0; $i < strlen($word); $i++) {
                        for ($j = 0; $j < strlen($existing); $j++) {
                            if ($word[$i] !== $existing[$j]) {
                                continue;
                            }

                            $orientation = $pw['orientation'] === 'across' ? 'down' : 'across';
                            if ($orientation === 'down') {
                                $row = $pw['row'] - $i;
                                $col = $pw['col'] + $j;
                            } else {
                                $row = $pw['row'] + $j;
                                $col = $pw['col'] - $i;
                            }
                            $candidates[] = [$row, $col, $orientation];
                        }
                    }
                }

                shuffle($candidates);
                $didPlace = false;
                foreach ($candidates as $cand) {
                    [$row, $col, $orientation] = $cand;
                    if (!crossword_can_place($grid, $size, $word, $row, $col, $orientation)) {
                        continue;
                    }
                    crossword_place_word($grid, $word, $row, $col, $orientation);
                    $placed[] = ['word' => $word, 'row' => $row, 'col' => $col, 'orientation' => $orientation];
                    $didPlace = true;
                    break;
                }

                if (!$didPlace) {
                    // Try to seed a new crossing backbone only if enough words already placed.
                    if (count($placed) < max(3, count($words) - 2)) {
                        continue 2;
                    }
                }
            }

            if (count($placed) < max(3, count($words) - 1)) {
                continue;
            }

            $minR = $size; $maxR = 0; $minC = $size; $maxC = 0;
            for ($r = 0; $r < $size; $r++) {
                for ($c = 0; $c < $size; $c++) {
                    if ($grid[$r][$c] !== '') {
                        $minR = min($minR, $r);
                        $maxR = max($maxR, $r);
                        $minC = min($minC, $c);
                        $maxC = max($maxC, $c);
                    }
                }
            }

            $rows = [];
            for ($r = $minR; $r <= $maxR; $r++) {
                $cells = [];
                for ($c = $minC; $c <= $maxC; $c++) {
                    $cells[] = $grid[$r][$c] === '' ? '#' : $grid[$r][$c];
                }
                $rows[] = implode(' ', $cells);
            }

            $numberMap = [];
            $numbers = [];
            $nextNum = 1;
            for ($r = $minR; $r <= $maxR; $r++) {
                for ($c = $minC; $c <= $maxC; $c++) {
                    if ($grid[$r][$c] === '') continue;
                    $startsAcross = ($c === $minC || $grid[$r][$c - 1] === '') && ($c < $size - 1 && $grid[$r][$c + 1] !== '');
                    $startsDown = ($r === $minR || $grid[$r - 1][$c] === '') && ($r < $size - 1 && $grid[$r + 1][$c] !== '');
                    if ($startsAcross || $startsDown) {
                        $key = $r . ':' . $c;
                        $numberMap[$key] = $nextNum;
                        $nextNum++;
                    }
                }
            }

            $across = [];
            $down = [];
            foreach ($placed as $p) {
                $numKey = $p['row'] . ':' . $p['col'];
                if (!isset($numberMap[$numKey])) {
                    continue;
                }
                $entry = [
                    'number' => $numberMap[$numKey],
                    'word' => $p['word'],
                    'length' => strlen($p['word']),
                    'row' => $p['row'] - $minR + 1,
                    'col' => $p['col'] - $minC + 1,
                ];
                if ($p['orientation'] === 'across') {
                    $across[] = $entry;
                } else {
                    $down[] = $entry;
                }
            }

            usort($across, fn($a, $b) => $a['number'] <=> $b['number']);
            usort($down, fn($a, $b) => $a['number'] <=> $b['number']);

            $width = $maxC - $minC + 1;
            $height = $maxR - $minR + 1;
            $filledCells = 0;
            foreach ($rows as $rowText) {
                foreach (preg_split('/\s+/', trim($rowText)) as $cell) {
                    if ($cell !== '#') {
                        $filledCells++;
                    }
                }
            }

            $puzzle = [
                'rows' => $rows,
                'width' => $width,
                'height' => $height,
                'across' => $across,
                'down' => $down,
                'words' => array_map(fn($p) => $p['word'], $placed),
                'filled_cells' => $filledCells,
            ];

            $puzzle['score'] = score_crossword_layout(
                $width,
                $height,
                $filledCells,
                count($puzzle['words']),
                count($across),
                count($down)
            );

            if ($bestPuzzle === null || $puzzle['score'] > $bestPuzzle['score']) {
                $bestPuzzle = $puzzle;
            }

            if (count($puzzle['words']) >= count($words)) {
                return $puzzle;
            }
        }
    }

    return $bestPuzzle;
}

function word_letter_overlap_score($a, $b) {
    $seen = [];
    $score = 0;
    for ($i = 0; $i < strlen($a); $i++) {
        $ch = $a[$i];
        if (strpos($b, $ch) !== false && !isset($seen[$ch])) {
            $seen[$ch] = true;
            $score++;
        }
    }
    return $score;
}

function order_crossword_words($words, $targetCount) {
    $pool = array_values($words);
    if (count($pool) <= 1) {
        return array_slice($pool, 0, $targetCount);
    }

    $scores = [];
    foreach ($pool as $i => $word) {
        $total = 0;
        foreach ($pool as $j => $other) {
            if ($i === $j) continue;
            $total += word_letter_overlap_score($word, $other);
        }
        $scores[$i] = $total;
    }

    arsort($scores);
    $ordered = [];
    $used = [];

    $seedIndex = array_key_first($scores);
    $ordered[] = $pool[$seedIndex];
    $used[$seedIndex] = true;

    while (count($ordered) < min($targetCount, count($pool))) {
        $bestIndex = null;
        $bestScore = -1;
        foreach ($pool as $i => $word) {
            if (isset($used[$i])) continue;
            $score = 0;
            foreach ($ordered as $picked) {
                $score += word_letter_overlap_score($word, $picked);
            }
            if ($score > $bestScore) {
                $bestScore = $score;
                $bestIndex = $i;
            }
        }

        if ($bestIndex === null) {
            break;
        }

        $ordered[] = $pool[$bestIndex];
        $used[$bestIndex] = true;
    }

    return $ordered;
}

function build_best_crossword_puzzle($words, $requestedCount) {
    $pool = array_values($words);
    if (count($pool) < 3) {
        return null;
    }

    $best = null;
    $maxTake = min(count($pool), max($requestedCount, 8));
    $ordered = order_crossword_words($pool, $maxTake);

    for ($take = min($requestedCount, count($ordered)); $take >= 5; $take--) {
        $candidates = [];
        $base = array_slice($ordered, 0, $take);
        $candidates[] = $base;

        for ($i = 0; $i < 20; $i++) {
            $shuffled = $base;
            shuffle($shuffled);
            usort($shuffled, function ($a, $b) use ($base) {
                $oa = 0;
                $ob = 0;
                foreach ($base as $w) {
                    if ($w !== $a) $oa += word_letter_overlap_score($a, $w);
                    if ($w !== $b) $ob += word_letter_overlap_score($b, $w);
                }
                return $ob <=> $oa ?: (strlen($b) <=> strlen($a));
            });
            $candidates[] = $shuffled;
        }

        foreach ($candidates as $candidateSet) {
            $puzzle = build_crossword_puzzle($candidateSet);
            if ($puzzle === null) {
                continue;
            }

            if (
                $best === null
                || count($puzzle['words']) > count($best['words'])
                || (count($puzzle['words']) === count($best['words']) && (($puzzle['score'] ?? 0) > ($best['score'] ?? 0)))
            ) {
                $best = $puzzle;
            }

            if (count($puzzle['words']) >= $take && (($puzzle['score'] ?? 0) > 0)) {
                return $puzzle;
            }
        }
    }

    return $best;
}

function format_crossword_clue($theme, $word) {
    $theme = trim($theme);
    if ($theme === '') {
        return 'Related term';
    }
    return ucfirst($theme) . ' term';
}

function crossword_rows_to_cells($rows) {
    $cells = [];
    foreach ($rows as $row) {
        $cells[] = preg_split('/\s+/', trim($row));
    }
    return $cells;
}

function render_crossword_number_grid($puzzle) {
    $cells = crossword_rows_to_cells($puzzle['rows']);
    $numberMap = [];

    foreach (array_merge($puzzle['across'], $puzzle['down']) as $entry) {
        $key = $entry['row'] . ':' . $entry['col'];
        $numberMap[$key] = $entry['number'];
    }

    $lines = [];
    foreach ($cells as $rIdx => $row) {
        $parts = [];
        foreach ($row as $cIdx => $cell) {
            $rowNum = $rIdx + 1;
            $colNum = $cIdx + 1;
            $key = $rowNum . ':' . $colNum;

            if ($cell === '#') {
                $parts[] = '   ';
            } elseif (isset($numberMap[$key])) {
                $label = substr(str_pad((string)$numberMap[$key], 2, ' ', STR_PAD_LEFT), -2);
                $parts[] = '[' . $label . ']';
            } else {
                $parts[] = '[ ]';
            }
        }
        $lines[] = implode(' ', $parts);
    }

    return implode("\n", $lines);
}

function render_crossword_solution_grid($puzzle) {
    $cells = crossword_rows_to_cells($puzzle['rows']);
    if (empty($cells)) {
        return '';
    }
    $lines = [];

    foreach ($cells as $row) {
        $content = [];
        foreach ($row as $cell) {
            $content[] = $cell === '#' ? '   ' : '[' . $cell . ']';
        }
        $lines[] = implode(' ', $content);
    }

    return implode("\n", $lines);
}

function format_puzzle_word_count_line($actual, $requested, $language = 'english') {
    $actual = (int)$actual;
    $requested = (int)$requested;

    if ($language === 'telugu') {
        if ($actual === $requested) {
            return 'పదాలు: ' . $actual;
        }
        return 'పదాలు: ' . $actual . ' / కోరినవి ' . $requested;
    }

    if ($actual === $requested) {
        return 'Words: ' . $actual;
    }
    return 'Words: ' . $actual . ' of ' . $requested . ' requested';
}

function format_crossword_response($theme, $count, $puzzle, $usedLlmCandidates) {
    $lines = [];
    $lines[] = 'Crossword Puzzle';
    $lines[] = 'Theme: ' . $theme;
    $lines[] = format_puzzle_word_count_line(count($puzzle['words']), $count, 'english');
    $lines[] = 'Grid: ' . $puzzle['height'] . 'x' . $puzzle['width'];
    $lines[] = 'Legend: [ ] = fillable cell, [n] = clue start, blank space = blocked cell';
    $lines[] = '';
    $lines[] = 'Puzzle Grid:';
    $lines[] = render_crossword_number_grid($puzzle);
    $lines[] = '';
    $lines[] = 'Across:';
    foreach ($puzzle['across'] as $entry) {
        $lines[] = '- ' . $entry['number'] . '. ' . format_crossword_clue($theme, $entry['word']) . ' (' . $entry['length'] . ')';
    }

    $lines[] = '';
    $lines[] = 'Down:';
    foreach ($puzzle['down'] as $entry) {
        $lines[] = '- ' . $entry['number'] . '. ' . format_crossword_clue($theme, $entry['word']) . ' (' . $entry['length'] . ')';
    }

    $lines[] = '';
    $lines[] = 'Answer key:';
    foreach ($puzzle['across'] as $entry) {
        $lines[] = '- ' . $entry['number'] . ' Across: ' . $entry['word'] . ' at (' . $entry['row'] . ',' . $entry['col'] . ')';
    }
    foreach ($puzzle['down'] as $entry) {
        $lines[] = '- ' . $entry['number'] . ' Down: ' . $entry['word'] . ' at (' . $entry['row'] . ',' . $entry['col'] . ')';
    }

    $lines[] = '';
    $lines[] = 'Solution Grid:';
    $lines[] = render_crossword_solution_grid($puzzle);

    $lines[] = '';
    $lines[] = 'Generation mode: deterministic crossword builder' . ($usedLlmCandidates ? ' + LLM word suggestions.' : '.');
    $lines[] = '';
    $lines[] = 'LLM consulted - ' . ($usedLlmCandidates ? 'Yes' : 'No');

    return implode("\n", $lines);
}

function generate_crossword_answer($question, $llm_provider, $llm_model, $language = 'english') {
    $req = parse_crossword_request($question);
    if ($req === null) {
        return null;
    }

    $theme = $req['theme'];
    $count = $req['count'];
    $lang  = strtolower((string)($language ?? 'english')) ?: 'english';

    if (
        is_inappropriate_text($theme, $lang)
        || is_inappropriate_text_any_language($theme)
        || is_inappropriate_text_any_language($question)
        || is_disallowed_theme_or_topic($theme, $lang)
        || is_disallowed_theme_or_topic_any_language($theme)
        || is_disallowed_theme_or_topic($question, $lang)
        || is_disallowed_theme_or_topic_any_language($question)
        || is_contextually_harmful_targeting($theme, $lang)
        || is_contextually_harmful_targeting($question, $lang)
    ) {
        return [
            'ok' => false,
            'answer' => 'That theme is not allowed for puzzle generation. Please use a classroom-safe, non-sensitive theme.',
        ];
    }

    // The deterministic crossword builder uses byte-level strlen()/indexing and
    // only works correctly with ASCII (English) words.  For non-English scripts
    // (e.g. Telugu) skip the builder and fall through to the LLM fallback so
    // that characters are handled properly.
    $useBuilder = ($lang === 'english');

    [$llmWordsRaw, $usedLlm] = request_theme_words_from_llm($theme, $count, $llm_provider, $llm_model, $lang);
    $fallbackWordsRaw = fallback_theme_words($theme, $lang);
    $words = sanitize_word_list(array_merge($llmWordsRaw, $fallbackWordsRaw), max($count + 4, 12), $lang);

    $puzzle = null;
    if ($useBuilder) {
        $candidateWords = array_slice($words, 0, max($count + 4, 12));
        $puzzle = build_best_crossword_puzzle($candidateWords, $count);
    }

    if ($puzzle === null) {
        return [
            'ok' => false,
            'answer' => 'I could not construct a valid crossword with the deterministic builder for that request. Please try a broader classroom-safe theme or fewer words.',
        ];
    }

    return [
        'ok' => true,
        'answer' => format_crossword_response($theme, $count, $puzzle, $usedLlm),
    ];
}

function fill_empty_cells(&$grid, $nRows, $nCols, $language = 'english') {
    $language = normalize_supported_language($language);
    $teluguPool = ['అ', 'ఆ', 'ఇ', 'ఈ', 'ఉ', 'ఊ', 'ఎ', 'ఏ', 'ఒ', 'ఓ', 'క', 'గ', 'చ', 'జ', 'ట', 'డ', 'త', 'ద', 'న', 'ప', 'బ', 'మ', 'య', 'ర', 'ల', 'వ', 'స', 'హ'];
    $hindiPool = ['अ', 'आ', 'इ', 'ई', 'उ', 'ए', 'ओ', 'क', 'ग', 'च', 'ज', 'ट', 'ड', 'त', 'द', 'न', 'प', 'ब', 'म', 'य', 'र', 'ल', 'व', 'स', 'ह'];
    $gujaratiPool = ['અ', 'આ', 'ઇ', 'ઈ', 'ઉ', 'એ', 'ઓ', 'ક', 'ગ', 'ચ', 'જ', 'ટ', 'ડ', 'ત', 'દ', 'ન', 'પ', 'બ', 'મ', 'ય', 'ર', 'લ', 'વ', 'સ', 'હ'];
    $malayalamPool = ['അ', 'ആ', 'ഇ', 'ഈ', 'ഉ', 'ഏ', 'ഓ', 'ക', 'ഗ', 'ച', 'ജ', 'ട', 'ഡ', 'ത', 'ദ', 'ന', 'പ', 'ബ', 'മ', 'യ', 'ര', 'ല', 'വ', 'സ', 'ഹ'];

    $poolByLanguage = [
        'telugu' => $teluguPool,
        'hindi' => $hindiPool,
        'gujarati' => $gujaratiPool,
        'malayalam' => $malayalamPool,
    ];

    for ($r = 0; $r < $nRows; $r++) {
        for ($c = 0; $c < $nCols; $c++) {
            if ($grid[$r][$c] === '') {
                if (isset($poolByLanguage[$language])) {
                    $pool = $poolByLanguage[$language];
                    $grid[$r][$c] = $pool[random_int(0, count($pool) - 1)];
                } else {
                    $grid[$r][$c] = chr(ord('A') + random_int(0, 25));
                }
            }
        }
    }
}

function direction_to_label($dr, $dc) {
    $map = [
        '0,1' => 'E',
        '0,-1' => 'W',
        '1,0' => 'S',
        '-1,0' => 'N',
        '1,1' => 'SE',
        '1,-1' => 'SW',
        '-1,1' => 'NE',
        '-1,-1' => 'NW',
    ];
    $key = $dr . ',' . $dc;
    return $map[$key] ?? '?';
}

function build_word_find_puzzle($words, $language = 'english', $gridCols = 16, $gridRows = 12) {
    $words = array_values($words);
    if (count($words) < 3) {
        return null;
    }

    usort($words, function($a, $b) use ($language) {
        return count(split_word_units($b, $language)) <=> count(split_word_units($a, $language));
    });

    $dirs = [
        [0, 1], [0, -1], [1, 0], [-1, 0],
        [1, 1], [1, -1], [-1, 1], [-1, -1],
    ];

    for ($grow = 0; $grow < 4; $grow++) {
        $nCols = min(30, $gridCols + $grow);
        $nRows = min(30, $gridRows + $grow);

        for ($attempt = 0; $attempt < 30; $attempt++) {
            // Rectangular grid: $nRows rows x $nCols columns.
            $grid = array_fill(0, $nRows, array_fill(0, $nCols, ''));
            $placements = [];
            $ok = true;

            foreach ($words as $word) {
                $units = split_word_units($word, $language);
                $placed = false;
                for ($tries = 0; $tries < 300; $tries++) {
                    $dir = $dirs[random_int(0, count($dirs) - 1)];
                    $dr = $dir[0];
                    $dc = $dir[1];
                    $row = random_int(0, $nRows - 1);
                    $col = random_int(0, $nCols - 1);

                    if (!can_place_word($grid, $nRows, $nCols, $units, $row, $col, $dr, $dc)) {
                        continue;
                    }

                    place_word_in_grid($grid, $units, $row, $col, $dr, $dc);
                    $endRow = $row + ($dr * (count($units) - 1));
                    $endCol = $col + ($dc * (count($units) - 1));
                    $placements[] = [
                        'word' => $word,
                        'start' => [$row + 1, $col + 1],
                        'end' => [$endRow + 1, $endCol + 1],
                        'dir' => direction_to_label($dr, $dc),
                    ];
                    $placed = true;
                    break;
                }

                if (!$placed) {
                    $ok = false;
                    break;
                }
            }

            if ($ok) {
                fill_empty_cells($grid, $nRows, $nCols, $language);
                $rows = [];
                for ($r = 0; $r < $nRows; $r++) {
                    $rows[] = implode(' ', $grid[$r]);
                }

                return [
                    'size' => max($nCols, $nRows),
                    'rows' => $rows,
                    'placements' => $placements,
                    'words' => $words,
                ];
            }
        }
    }

    return null;
}

function text_display_width($text) {
    if (function_exists('mb_strwidth')) {
        return mb_strwidth((string)$text, 'UTF-8');
    }
    return strlen((string)$text);
}

function pad_cell_center($text, $width) {
    $text = (string)$text;
    $w = text_display_width($text);
    if ($w >= $width) {
        return $text;
    }

    $total = $width - $w;
    $left = intdiv($total, 2);
    $right = $total - $left;
    return str_repeat(' ', $left) . $text . str_repeat(' ', $right);
}

function render_word_find_grid($rows) {
    $matrix = [];
    $cellWidth = 1;

    foreach ($rows as $row) {
        $cells = preg_split('/\s+/u', trim((string)$row));
        $cells = array_values(array_filter($cells, fn($c) => $c !== ''));
        if (empty($cells)) {
            continue;
        }
        foreach ($cells as $c) {
            $cellWidth = max($cellWidth, text_display_width($c));
        }
        $matrix[] = $cells;
    }

    if (empty($matrix)) {
        return '';
    }

    $cols = count($matrix[0]);
    $lines = [];

    foreach ($matrix as $row) {
        $parts = [];
        for ($i = 0; $i < $cols; $i++) {
            $cell = $row[$i] ?? '';
            $parts[] = pad_cell_center($cell, $cellWidth);
        }
        $lines[] = implode(' ', $parts);
    }

    return implode("\n", $lines);
}

function word_find_grid_dimensions($rows) {
    $height = 0;
    $width = 0;
    foreach ($rows as $row) {
        $cells = preg_split('/\s+/u', trim((string)$row));
        $cells = array_values(array_filter($cells, fn($c) => $c !== ''));
        if (empty($cells)) {
            continue;
        }
        $height++;
        $width = max($width, count($cells));
    }
    return [$width, $height];
}

function format_word_find_response($theme, $count, $puzzle, $usedLlmCandidates, $language = 'english') {
    $lines = [];
    [$gridWidth, $gridHeight] = word_find_grid_dimensions($puzzle['rows']);
    if ($gridWidth <= 0 || $gridHeight <= 0) {
        $gridWidth = (int)($puzzle['size'] ?? 0);
        $gridHeight = (int)($puzzle['size'] ?? 0);
    }

    if ($language === 'telugu') {
        $lines[] = 'పద శోధన పజిల్';
        $lines[] = 'థీమ్: ' . $theme;
        $lines[] = format_puzzle_word_count_line(count($puzzle['words']), $count, 'telugu');
        $lines[] = 'గ్రిడ్: ' . $gridWidth . 'x' . $gridHeight;
    } else {
        $lines[] = 'Word Find Puzzle';
        $lines[] = 'Theme: ' . $theme;
        $lines[] = format_puzzle_word_count_line(count($puzzle['words']), $count, 'english');
        $lines[] = 'Grid: ' . $gridWidth . 'x' . $gridHeight;
    }
    $lines[] = '';
    $lines[] = render_word_find_grid($puzzle['rows']);

    $lines[] = '';
    $lines[] = $language === 'telugu' ? 'ఈ పదాలను కనుగొనండి:' : 'Find these words:';
    foreach ($puzzle['words'] as $w) {
        $lines[] = '- ' . $w;
    }

    $lines[] = '';
    $lines[] = $language === 'telugu' ? 'జవాబు సూచిక:' : 'Answer key:';
    foreach ($puzzle['placements'] as $p) {
        $lines[] = '- ' . $p['word'] . ': (' . $p['start'][0] . ',' . $p['start'][1] . ') -> (' . $p['end'][0] . ',' . $p['end'][1] . ') ' . $p['dir'];
    }

    $lines[] = '';
    $lines[] = $language === 'telugu'
        ? ('జనరేషన్ మోడ్: deterministic grid builder' . ($usedLlmCandidates ? ' + LLM word suggestions.' : '.'))
        : ('Generation mode: deterministic grid builder' . ($usedLlmCandidates ? ' + LLM word suggestions.' : '.'));
    $lines[] = '';
    $lines[] = $usedLlmCandidates ? 'LLM consulted - Yes' : 'LLM consulted - No';

    return implode("\n", $lines);
}

function generate_word_find_answer($question, $llm_provider, $llm_model, $language = 'english') {
    $language = normalize_supported_language($language);
    $req = parse_word_find_request($question);
    if ($req === null) {
        return null;
    }

    $theme = $req['theme'];
    $count = $req['count'];

    if (
        is_inappropriate_text($theme, $language)
        || is_inappropriate_text_any_language($theme)
        || is_inappropriate_text_any_language($question)
        || is_disallowed_theme_or_topic($theme, $language)
        || is_disallowed_theme_or_topic_any_language($theme)
        || is_disallowed_theme_or_topic($question, $language)
        || is_disallowed_theme_or_topic_any_language($question)
        || is_contextually_harmful_targeting($theme, $language)
        || is_contextually_harmful_targeting($question, $language)
    ) {
        return [
            'ok' => false,
            'answer' => 'That theme is not allowed for puzzle generation. Please use a classroom-safe, non-sensitive theme.',
        ];
    }

    $canonicalTheme = strtolower(canonicalize_theme($theme));
    $fallbackWordsRaw = fallback_theme_words($theme, $language);
    $useStrictCuratedTheme = in_array($canonicalTheme, ['dog', 'cat'], true);

    $llmWordsRaw = [];
    $usedLlm = false;
    $llmProviderError = false;
    $llmProviderErrorMessage = '';
    if (!$useStrictCuratedTheme) {
        $llmResult = request_theme_words_from_llm($theme, $count, $llm_provider, $llm_model, $language);
        $llmWordsRaw = is_array($llmResult[0] ?? null) ? $llmResult[0] : [];
        $usedLlm = !empty($llmResult[1]);
        $llmProviderError = !empty($llmResult[2]);
        $llmProviderErrorMessage = trim((string)($llmResult[3] ?? ''));
    }

    // If the caller explicitly selected a provider/model and it failed, surface
    // the provider error instead of silently switching to fallback vocabulary.
    if (!$useStrictCuratedTheme && $llm_provider !== '' && $llmProviderError) {
        $safeError = $llmProviderErrorMessage !== '' ? $llmProviderErrorMessage : 'LLM provider request failed.';
        return [
            'ok' => false,
            'answer' => $safeError,
        ];
    }

    error_log('WordFind source counts: llm_raw=' . count($llmWordsRaw) . ', fallback_raw=' . count($fallbackWordsRaw) . ', theme=' . $theme . ', language=' . $language . ', provider=' . ($llm_provider ?: 'default') . ', model=' . ($llm_model ?: 'default'));

    // Keep LLM suggestions primary for general themes. Use curated fallback only to fill gaps.
    if ($useStrictCuratedTheme) {
        $words = sanitize_word_list($fallbackWordsRaw, $count, $language, $req['gridCols']);
    } else {
        $words = sanitize_word_list($llmWordsRaw, $count, $language, $req['gridCols']);
        if (count($words) < $count) {
            $words = sanitize_word_list(array_merge($words, $fallbackWordsRaw), $count, $language, $req['gridCols']);
        }
    }

    if (count($words) < $count) {
        // Add deterministic fillers to reach requested count if needed.
        if ($language === 'telugu') {
            $extras = ['ఆకాశం', 'పర్వతం', 'పాట', 'చెట్టు', 'పువ్వు', 'నది', 'చంద్రుడు', 'సముద్రం', 'వెలుగు', 'గాలి'];
        } elseif ($language === 'hindi') {
            $extras = ['आकाश', 'पर्वत', 'गीत', 'पेड़', 'फूल', 'नदी', 'चाँद', 'समुद्र', 'प्रकाश', 'हवा'];
        } elseif ($language === 'gujarati') {
            $extras = ['આકાશ', 'પર્વત', 'ગીત', 'ઝાડ', 'ફૂલ', 'નદી', 'ચંદ્ર', 'સમુદ્ર', 'પ્રકાશ', 'પવન'];
        } elseif ($language === 'malayalam') {
            $extras = ['ആകാശം', 'പർവ്വതം', 'ഗാനം', 'മരം', 'പൂവ്', 'നദി', 'ചന്ദ്രൻ', 'സമുദ്രം', 'വെളിച്ചം', 'കാറ്റ്'];
        } else {
            $extras = ['ALPHA', 'BRAVO', 'CHARLIE', 'DELTA', 'ECHO', 'FOXTROT', 'GAMMA', 'OMEGA', 'SIGMA', 'THETA'];
        }
        $words = sanitize_word_list(array_merge($words, $extras), $count, $language, $req['gridCols']);
    }

    error_log('WordFind final words count=' . count($words) . ', requested=' . $count . ', theme=' . $theme . ', language=' . $language);

    $puzzle = build_word_find_puzzle($words, $language, $req['gridCols'], $req['gridRows']);
    if ($puzzle === null) {
        return [
            'ok' => false,
            'answer' => 'I could not construct a valid word-find grid for that request. Please try fewer words or a broader theme.\n\nLLM consulted - Yes',
        ];
    }

    return [
        'ok' => true,
        'answer' => format_word_find_response(localized_theme_label($theme, $language), $count, $puzzle, $usedLlm, $language),
    ];
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
    // curl_close($ch);

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
$language = normalize_supported_language($_POST['language'] ?? ($data['language'] ?? 'english'));
$llm_provider = strtolower(trim($_POST['llm_provider'] ?? ($data['llm_provider'] ?? '')));
$llm_model = trim($_POST['llm_model'] ?? ($data['llm_model'] ?? ''));

if (!in_array($llm_provider, ['gemini', 'openai', 'groq'], true)) {
    $llm_provider = '';
}

if ($llm_provider !== '' && function_exists('llm_provider_has_api_key') && !llm_provider_has_api_key($llm_provider)) {
    echo json_encode([
        'error' => strtoupper($llm_provider) . ' API key not configured.',
        'llm_consulted' => false,
    ]);
    exit;
}

if ($llm_model !== '' && !preg_match('/^[A-Za-z0-9._:-]{1,120}$/', $llm_model)) {
    $llm_model = '';
}

if(!$question) {
    echo json_encode(['error' => 'Missing question parameter']);
    exit;
}

$isGenerationRequest = is_generation_request($question);
if (
    $isGenerationRequest
    && (
        is_inappropriate_text($question, $language)
        || is_inappropriate_text_any_language($question)
        || is_disallowed_theme_or_topic($question, $language)
        || is_disallowed_theme_or_topic_any_language($question)
        || is_contextually_harmful_targeting($question, $language)
    )
) {
    echo json_encode([
        'question' => $question,
        'language' => $language,
        'answer' => 'That theme is not allowed for puzzle generation. Please use a classroom-safe, non-sensitive theme.',
        'api_doc_name' => null,
        'source' => 'fallback',
        'llm_consulted' => true,
    ]);
    exit;
}

// ── Forward to MCP server (intent classification + tool calling handled there) ──

// If the user explicitly selected an LLM/provider from the UI, use direct fallback path.
// MCP server currently uses its own runtime provider configuration.
$useDirectLlm = ($llm_provider !== '' || $llm_model !== '');

$mcp_result = null;
if (!$useDirectLlm && !$isGenerationRequest) {
    $mcp_result = call_mcp_server($MCP_SERVER_URL, $question, $language, $MCP_TIMEOUT, $llm_provider, $llm_model);
}

if($mcp_result !== null) {
    // MCP server responded successfully
    if (is_array($mcp_result)) {
        $mcp_result['llm_consulted'] = true;
        if (!empty($mcp_result['answer'])) {
            $mcp_result['answer'] = moderate_outbound_answer($mcp_result['answer'], $language);
        }
        if (!empty($mcp_result['answer']) && stripos($mcp_result['answer'], 'LLM consulted') === false) {
            $mcp_result['answer'] .= "\n\nLLM consulted - Yes";
        }

        $effective_llm_provider = $llm_provider ?: strtolower(getenv('LLM_PROVIDER') ?: 'gemini');
        $effective_llm_model = $llm_model ?: trim((string)(getenv('LLM_MODEL') ?: ''));
        if ($effective_llm_model === '' || strtolower($effective_llm_model) === 'auto') {
            $effective_llm_model = llm_default_model_for_provider($effective_llm_provider);
        }

        if (empty($mcp_result['llm_provider'])) {
            $mcp_result['llm_provider'] = $effective_llm_provider;
        }

        if (empty($mcp_result['llm_model'])) {
            $mcp_result['llm_model'] = $effective_llm_model;
        }
    }
    echo json_encode($mcp_result);
    exit;
}

$fallbackReason = $useDirectLlm
    ? 'Direct LLM selected from UI (skipped MCP server).'
    : 'MCP server unavailable or returned invalid response.';
error_log('Chat API: falling back to legacy single-shot flow. Reason: ' . $fallbackReason);

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

$fallbackOpts = [
    'temperature' => 0.0,
    'system_prompt' => 'You output ONLY valid JSON. No explanations.',
];

if ($llm_provider !== '') {
    $fallbackOpts['provider'] = $llm_provider;
}

if ($llm_model !== '') {
    $fallbackOpts['model'] = $llm_model;
}

$effective_llm_provider = $llm_provider ?: strtolower(getenv('LLM_PROVIDER') ?: 'gemini');
$effective_llm_model = $llm_model ?: trim((string)(getenv('LLM_MODEL') ?: ''));
if ($effective_llm_model === '' || strtolower($effective_llm_model) === 'auto') {
    $effective_llm_model = llm_default_model_for_provider($effective_llm_provider);
}

if ($isGenerationRequest) {
    if (is_crossword_request($question)) {
        $crossword = generate_crossword_answer($question, $llm_provider, $llm_model, strtolower((string)$language));
        if (is_array($crossword)) {
            if (empty($crossword['ok'])) {
                echo json_encode([
                    'error' => $crossword['answer'] ?? 'I could not construct a crossword for that request.',
                    'question' => $question,
                    'language' => $language,
                    'llm_provider' => $effective_llm_provider,
                    'llm_model' => $effective_llm_model,
                    'source' => 'fallback',
                    'llm_consulted' => true,
                ]);
                exit;
            }

            echo json_encode([
                'question' => $question,
                'language' => $language,
                'llm_provider' => $effective_llm_provider,
                'llm_model' => $effective_llm_model,
                'answer' => $crossword['answer'] ?? 'I could not construct a crossword for that request.\n\nLLM consulted - Yes',
                'api_doc_name' => null,
                'source' => 'fallback',
                'llm_consulted' => true,
            ]);
            exit;
        }
    }

    $wordFind = generate_word_find_answer($question, $llm_provider, $llm_model, strtolower((string)$language));
    if (is_array($wordFind)) {
        if (empty($wordFind['ok'])) {
            echo json_encode([
                'error' => $wordFind['answer'] ?? 'I could not construct a valid word-find grid for that request.',
                'question' => $question,
                'language' => $language,
                'llm_provider' => $effective_llm_provider,
                'llm_model' => $effective_llm_model,
                'source' => 'fallback',
                'llm_consulted' => true,
            ]);
            exit;
        }

        echo json_encode([
            'question' => $question,
            'language' => $language,
            'llm_provider' => $effective_llm_provider,
            'llm_model' => $effective_llm_model,
            'answer' => $wordFind['answer'] ?? 'I could not construct a valid word-find grid for that request. Please try a broader classroom-safe theme.',
            'api_doc_name' => null,
            'source' => 'fallback',
            'llm_consulted' => true,
        ]);
        exit;
    }

    echo json_encode([
        'question' => $question,
        'language' => $language,
        'llm_provider' => $effective_llm_provider,
        'llm_model' => $effective_llm_model,
        'answer' => 'I could not construct a valid puzzle with the deterministic builder for that request. Please try a broader classroom-safe theme or fewer words.',
        'api_doc_name' => null,
        'source' => 'fallback',
        'llm_consulted' => true,
    ]);
    exit;
}

$resp = llm_ask($prompt, $fallbackOpts);

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

    $strictOpts = [
        'temperature' => 0.0,
        'system_prompt' => 'Output ONLY valid JSON.',
    ];

    if ($llm_provider !== '') {
        $strictOpts['provider'] = $llm_provider;
    }

    if ($llm_model !== '') {
        $strictOpts['model'] = $llm_model;
    }

    $resp = llm_ask($strict_prompt, $strictOpts);

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

$resp = moderate_outbound_answer($resp, $language);

echo json_encode([
    'question' => $question,
    'language' => $language,
    'llm_provider' => $effective_llm_provider,
    'llm_model' => $effective_llm_model,
    'answer' => $resp,
    'api_doc_name' => $api_doc_label,
    'source' => 'fallback',
    'llm_consulted' => true,
]);

// ── Helper: call the Python MCP server ─────────────────────────────
function call_mcp_server($url, $question, $language, $timeout, $llm_provider = '', $llm_model = '') {
    $payloadData = [
        'question' => $question,
        'language' => $language,
    ];

    if (!empty($llm_provider)) {
        $payloadData['llm_provider'] = $llm_provider;
    }

    if (!empty($llm_model)) {
        $payloadData['llm_model'] = $llm_model;
    }

    $payload = json_encode($payloadData);

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

    if($result === false || $httpCode < 200 || $httpCode >= 500) {
        // MCP server is down or errored — trigger fallback
        error_log("MCP server unreachable ($url): $err (HTTP $httpCode)");
        // curl_close($ch);
        return null;
    }

    $decoded = json_decode($result, true);
    if(!is_array($decoded)) {
        error_log("MCP server returned invalid JSON");
        // curl_close($ch);
        return null;
    }

    // curl_close($ch);
    return $decoded;
}

// End of file

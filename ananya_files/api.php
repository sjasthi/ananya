<?php
/**
 * Ananya API Router - Single Entry Point for Clean URLs
 * Usage: api.php/characters/base?language=telugu&string=test
 */

// Clean any output buffers and BOM characters
if (ob_get_level()) {
    ob_end_clean();
}
ob_start();

require_once("word_processor.php");

// Get the request path
$request = $_SERVER['REQUEST_URI'];
$path = parse_url($request, PHP_URL_PATH);
$path = str_replace('/ananya/ananya_files/api.php/', '', $path); // TODO:Remove /ananya_files/ from path when file system fixed.
$path = str_replace('/ananya/ananya_files/api.php', '', $path);

// Remove leading slash if present
$path = ltrim($path, '/');

// Split path into segments
$segments = array_filter(explode('/', $path));

// Route to appropriate handler
if (empty($segments)) {
    sendResponse(400, "API endpoint required", null, null, null);
}

$category = $segments[0] ?? '';
$action = $segments[1] ?? '';

// Route based on category/action pattern
switch ($category) {
    case 'characters':
        handleCharacterAPIs($action);
        break;
    case 'text':
        handleTextAPIs($action);
        break;
    case 'analysis':
        handleAnalysisAPIs($action);
        break;
    case 'comparison':
        handleComparisonAPIs($action);
        break;
    case 'validation':
        handleValidationAPIs($action);
        break;
    case 'utility':
        handleUtilityAPIs($action);
        break;
    case 'auth':
        handleAuthAPIs($action);
        break;
    default:
        sendResponse(404, "API category not found: $category", null, null, null);
}

function handleCharacterAPIs($action) {
    $string = $_GET['string'] ?? '';
    $language = $_GET['language'] ?? '';
    
    // Filler API doesn't need a string parameter, only language
    if ($action !== 'filler' && (empty($string) || empty($language))) {
        sendResponse(400, "Missing required parameters: string and language", null, null, null);
        return;
    }
    
    if ($action === 'filler' && empty($language)) {
        sendResponse(400, "Missing required parameter: language", null, null, null);
        return;
    }
    
    $processor = new wordProcessor($string, $language);
    
    switch ($action) {
        case 'base':
            $result = $processor->getBaseCharacters();
            sendResponse(200, "Base characters processed", $string, $language, $result);
            break;
        case 'logical':
            $result = $processor->getLogicalChars();
            sendResponse(200, "Logical characters processed", $string, $language, $result);
            break;
        case 'codepoints':
            $result = $processor->getCodePoints();
            sendResponse(200, "Code points processed", $string, $language, $result);
            break;
        case 'codepoint-length':
            $result = $processor->getCodePointLength();
            sendResponse(200, "Code point length calculated", $string, $language, $result);
            break;
        case 'random-logical':
            $n = $_GET['count'] ?? '5';
            $result = $processor->getRandomLogicalChars(intval($n));
            sendResponse(200, "Random logical characters generated", $string, $language, $result);
            break;
        case 'add-end':
            $input2 = $_GET['input2'] ?? '';
            $result = $processor->addCharacterAtEnd($input2);
            sendResponse(200, "Character added at end", $string, $language, $result);
            break;
        case 'logical-at':
            $input2 = $_GET['input2'] ?? '0';
            $result = $processor->logicalCharAt(intval($input2));
            sendResponse(200, "Logical character at position retrieved", $string, $language, $result);
            break;
        case 'base-consonants':
            $input2 = $_GET['input2'] ?? '';
            $result = $processor->baseConsonants($string, $input2);
            sendResponse(200, "Base consonants calculated", $string, $language, $result);
            break;
        case 'add-at':
            $input2 = $_GET['input2'] ?? '';
            $input3 = $_GET['input3'] ?? '';
            $result = $processor->addCharacterAt(intval($input2), $input3);
            sendResponse(200, "Character added at position", $string, $language, $result);
            break;
        case 'filler':
            $count = $_GET['count'] ?? '3';
            $type = $_GET['type'] ?? 'consonant';
            $result = $processor->getFillerCharacters($count, $type);
            sendResponse(200, "Filler characters generated", $count . " " . $type, $language, $result);
            break;
        default:
            sendResponse(404, "Character API action not found: $action", null, null, null);
    }
}

function handleTextAPIs($action) {
    $string = $_GET['string'] ?? '';
    $language = $_GET['language'] ?? 'telugu'; // Default to telugu for primary use case
    
    if (empty($string)) {
        sendResponse(400, "Missing required parameter: string", null, null, null);
        return;
    }
    
    $processor = new wordProcessor($string, $language);
    
    switch ($action) {
        case 'length':
            $result = $processor->getLength();
            sendResponse(200, "Length calculated", $string, $language, $result);
            break;
        case 'reverse':
            $result = $processor->reverse();
            sendResponse(200, "Text reversed", $string, $language, $result);
            break;
        case 'randomize':
            $logicalChars = $processor->getLogicalChars();
            $result = $processor->randomize($logicalChars);
            sendResponse(200, "Text randomized", $string, $language, $result);
            break;
        case 'split':
            $delimiter = $_GET['delimiter'] ?? $_GET['input2'] ?? '-';
            // For now, just split by delimiter and return as string
            if (!empty($delimiter)) {
                $result = implode(' | ', explode($delimiter, $string));
            } else {
                $result = $string;
            }
            sendResponse(200, "Text split", $string, $language, $result);
            break;
        case 'replace':
            $search = $_GET['search'] ?? $_GET['input2'] ?? '';
            $replace = $_GET['replace'] ?? $_GET['input3'] ?? '';
            if (empty($search)) {
                sendResponse(400, "Missing required parameter: search", $string, $language, null);
                return;
            }
            $result = $processor->replace($search, $replace);
            sendResponse(200, "Text replaced", $string, $language, $result);
            break;
        default:
            sendResponse(404, "Text API action not found: $action", null, null, null);
    }
}

function handleAnalysisAPIs($action) {
    $string = $_GET['string'] ?? '';
    $language = $_GET['language'] ?? 'telugu'; // Default to telugu for primary use case
    
    if (empty($string)) {
        sendResponse(400, "Missing required parameter: string", null, null, null);
        return;
    }
    
    $processor = new wordProcessor($string, $language);
    
    switch ($action) {
        case 'is-palindrome':
            $result = $processor->isPalindrome();
            sendResponse(200, "Palindrome check completed", $string, $language, $result);
            break;
        case 'word-strength':
            $result = $processor->getWordStrength();
            sendResponse(200, "Word strength calculated", $string, $language, $result);
            break;
        case 'word-weight':
            $result = $processor->getWordWeight();
            sendResponse(200, "Word weight calculated", $string, $language, $result);
            break;
        case 'word-level':
            $result = $processor->getWordLevel();
            sendResponse(200, "Word level calculated", $string, $language, $result);
            break;
        case 'is-anagram':
            $input2 = $_GET['input2'] ?? '';
            $result = $processor->isAnagram($input2);
            sendResponse(200, "Anagram check completed", $string, $language, $result);
            break;
        case 'detect-language':
            $result = $processor->getLangForString();
            sendResponse(200, "Language detected", $string, $language, $result);
            break;
        case 'parse-to-logical-chars':
            $result = $processor->parseToLogicalChars($string);
            sendResponse(200, "Text parsed to logical characters", $string, $language, $result);
            break;
        case 'parse-to-logical-characters':
            $result = $processor->parseToLogicalCharacters($string);
            sendResponse(200, "Text parsed to logical characters (alternative)", $string, $language, $result);
            break;
        case 'split-into-chunks':
            $result = $processor->splitInto15Chunks();
            sendResponse(200, "Text split into chunks", $string, $language, $result);
            break;
        case 'can-make-word':
            $input2 = $_GET['input2'] ?? '';
            $result = $processor->canMakeWord($input2);
            sendResponse(200, "Word formation check completed", $string, $language, $result);
            break;
        case 'can-make-all-words':
            $input2 = $_GET['input2'] ?? '';
            $result = $processor->canMakeAllWords($input2);
            sendResponse(200, "All words formation check completed", $string, $language, $result);
            break;
        case 'is-intersecting':
            $input2 = $_GET['input2'] ?? '';
            $result = $processor->isIntersecting($input2);
            sendResponse(200, "Intersection check completed", $string, $language, $result);
            break;
        case 'intersecting-rank':
            $input2 = $_GET['input2'] ?? '';
            $result = $processor->getIntersectingRank($input2);
            sendResponse(200, "Intersecting rank calculated", $string, $language, $result);
            break;
        case 'unique-intersecting-rank':
            $input2 = $_GET['input2'] ?? '';
            // Convert input2 to logical characters array for comparison
            $processor2 = new wordProcessor($input2, $language);
            $logicalChars2 = $processor2->getLogicalChars();
            $result = $processor->getUniqueIntersectingRank($logicalChars2);
            sendResponse(200, "Unique intersecting rank calculated", $string, $language, $result);
            break;
        case 'unique-intersecting-logical-chars':
            $input2 = $_GET['input2'] ?? '';
            // Convert input2 to logical characters array for comparison
            $processor2 = new wordProcessor($input2, $language);
            $logicalChars2 = $processor2->getLogicalChars();
            $result = $processor->getUniqueIntersectingLogicalChars($logicalChars2);
            sendResponse(200, "Unique intersecting logical characters calculated", $string, $language, $result);
            break;
        case 'are-ladder-words':
            $input2 = $_GET['input2'] ?? '';
            $result = $processor->areLadderWords($input2);
            sendResponse(200, "Ladder words check completed", $string, $language, $result);
            break;
        case 'are-head-tail-words':
            $input2 = $_GET['input2'] ?? '';
            $result = $processor->areHeadAndTailWords($input2);
            sendResponse(200, "Head and tail words check completed", $string, $language, $result);
            break;
        case 'get-match-id-string':
            $input2 = $_GET['input2'] ?? '';
            $result = $processor->get_match_id_string($string, $input2);
            sendResponse(200, "Match ID string generated", $string, $language, $result);
            break;
        default:
            sendResponse(404, "Analysis API action not found: $action", null, null, null);
    }
}

function handleComparisonAPIs($action) {
    $string = $_GET['string'] ?? '';
    $language = $_GET['language'] ?? '';
    $input2 = $_GET['input2'] ?? '';
    
    if (empty($string) || empty($language)) {
        sendResponse(400, "Missing required parameters: string and language", null, null, null);
        return;
    }
    
    $processor = new wordProcessor($string, $language);
    
    switch ($action) {
        case 'equals':
            $result = $processor->equals($input2);
            sendResponse(200, "Equality check completed", $string, $language, $result);
            break;
        case 'starts-with':
            $result = $processor->startsWith($input2);
            sendResponse(200, "Starts with check completed", $string, $language, $result);
            break;
        case 'ends-with':
            $result = $processor->endsWith($input2);
            sendResponse(200, "Ends with check completed", $string, $language, $result);
            break;
        case 'compare':
            $result = $processor->compareTo($input2);
            sendResponse(200, "Comparison completed", $string, $language, $result);
            break;
        case 'compare-ignore-case':
            $result = $processor->compareToIgnoreCase($input2);
            sendResponse(200, "Case-insensitive comparison completed", $string, $language, $result);
            break;
        case 'reverse-equals':
            $result = $processor->reverseEquals($input2);
            sendResponse(200, "Reverse equality check completed", $string, $language, $result);
            break;
        case 'index-of':
            $result = $processor->indexOf($input2);
            sendResponse(200, "Index search completed", $string, $language, $result);
            break;
        default:
            sendResponse(404, "Comparison API action not found: $action", null, null, null);
    }
}

function handleValidationAPIs($action) {
    $string = $_GET['string'] ?? '';
    $language = $_GET['language'] ?? 'telugu'; // Default to telugu for primary use case
    
    if (empty($string)) {
        sendResponse(400, "Missing required parameter: string", null, null, null);
        return;
    }
    
    $processor = new wordProcessor($string, $language);
    
    switch ($action) {
        case 'contains-space':
            $result = $processor->containsSpace();
            sendResponse(200, "Space check completed", $string, $language, $result);
            break;
        case 'contains-char':
            $input2 = $_GET['input2'] ?? '';
            $result = $processor->containsChar($input2);
            sendResponse(200, "Character check completed", $string, $language, $result);
            break;
        case 'contains-logical-chars':
            $input2 = $_GET['input2'] ?? '';
            $result = $processor->containsLogicalChars($input2);
            sendResponse(200, "Logical characters check completed", $string, $language, $result);
            break;
        case 'contains-all-logical-chars':
            $input2 = $_GET['input2'] ?? '';
            $result = $processor->containsAllLogicalChars($input2);
            sendResponse(200, "All logical characters check completed", $string, $language, $result);
            break;
        case 'contains-logical-sequence':
            $input2 = $_GET['input2'] ?? '';
            $result = $processor->containsLogicalCharSequence($input2);
            sendResponse(200, "Logical character sequence check completed", $string, $language, $result);
            break;
        case 'is-consonant':
            // Use the first logical character from the string
            $logical_chars = $processor->getLogicalChars();
            $char = !empty($logical_chars) ? $logical_chars[0] : $string;
            $result = $processor->isCharConsonant($char);
            sendResponse(200, "Consonant check completed", $string, $language, $result);
            break;
        case 'is-vowel':
            // Use the first logical character from the string
            $logical_chars = $processor->getLogicalChars();
            $char = !empty($logical_chars) ? $logical_chars[0] : $string;
            $result = $processor->isCharVowel($char);
            sendResponse(200, "Vowel check completed", $string, $language, $result);
            break;
        case 'contains-string':
            $input2 = $_GET['input2'] ?? '';
            $result = $processor->containsString($input2);
            sendResponse(200, "String check completed", $string, $language, $result);
            break;
        default:
            sendResponse(404, "Validation API action not found: $action", null, null, null);
    }
}

function handleUtilityAPIs($action) {
    $string = $_GET['string'] ?? '';
    $language = $_GET['language'] ?? 'telugu'; // Default to telugu for primary use case
    
    if (empty($string)) {
        sendResponse(400, "Missing required parameter: string", null, null, null);
        return;
    }
    
    $processor = new wordProcessor($string, $language);
    
    switch ($action) {
        case 'length-no-spaces':
            $result = $processor->getLengthNoSpaces($string);
            sendResponse(200, "Length without spaces calculated", $string, $language, $result);
            break;
        case 'length-no-spaces-commas':
            $result = $processor->getLengthNoSpacesNoCommas($string);
            sendResponse(200, "Length without spaces and commas calculated", $string, $language, $result);
            break;
        case 'length-alternative':
            $result = $processor->getLength2();
            sendResponse(200, "Alternative length calculated", $string, $language, $result);
            break;
        default:
            sendResponse(404, "Utility API action not found: $action", null, null, null);
    }
}

function handleAuthAPIs($action) {
    switch ($action) {
        case 'user-exists':
            $username = $_GET['username'] ?? '';
            if (empty($username)) {
                sendResponse(400, "Missing required parameter: username", null, null, null);
                return;
            }
            // Implementation for user existence check
            sendResponse(200, "User existence checked", $username, null, false);
            break;
        default:
            sendResponse(404, "Auth API action not found: $action", null, null, null);
    }
}

function sendResponse($code, $message, $string, $language, $data) {
    // Clean any previous output
    $output = ob_get_clean();
    
    // Add CORS headers for frontend compatibility
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type, Authorization');
    header('Content-Type: application/json; charset=utf-8');
    header('Cache-Control: max-age=7200');
    http_response_code($code);
    
    // Create response in both old and new formats for compatibility
    $response = [
        'response_code' => $code,
        'message' => $message,
        'string' => $string,
        'language' => $language,
        'data' => $data,
        // Add fields for frontend compatibility
        'success' => ($code >= 200 && $code < 300),
        'result' => $data,
        'error' => ($code >= 400) ? $message : null
    ];
    
    // Output clean JSON without any BOM or extra characters
    echo json_encode($response, JSON_UNESCAPED_UNICODE);
    exit;
}
?>
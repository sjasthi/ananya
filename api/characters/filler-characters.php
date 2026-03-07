<?php

error_reporting(E_ALL);
ini_set('display_errors', '0');
ini_set('log_errors', '1');

// Change to project root so relative paths in word_processor.php work correctly
chdir(dirname(__FILE__) . '/../../');

require_once("word_processor.php");

// Initialize variables
$count = '';
$language = '';
$type = '';

if (isset($_GET['count']) && isset($_GET['language']) && isset($_GET['type'])) {
    $count = $_GET['count'];
    $language = $_GET['language'];
    $type = $_GET['type'];
} else if (isset($_GET['input1']) && isset($_GET['input2']) && isset($_GET['input3'])) {
    $count = $_GET['input1'];
    $language = $_GET['input2'];
    $type = $_GET['input3'];
}

// Validate inputs
$allowed_languages = array('english', 'telugu', 'hindi', 'gujarati', 'malayalam');
$allowed_types = array('vowel', 'consonant', 'all');

if (!isset($count) || $count === '') {
    invalidResponse("Invalid or Empty Count");
} else if (!is_numeric($count) || intval($count) <= 0 || intval($count) > 10000) {
    invalidResponse("Count must be an integer between 1 and 10000");
} else if (!isset($language) || $language === '') {
    invalidResponse("Invalid or Empty Language");
} else if (!in_array(strtolower($language), $allowed_languages)) {
    invalidResponse("Unsupported language. Supported: English, Telugu, Hindi, Gujarati, Malayalam");
} else if ($type !== '' && !in_array(strtolower($type), $allowed_types)) {
    invalidResponse("Invalid Type. Supported: vowel, consonant, all");
} else {
    $processor = new wordProcessor("", $language);
    $fillerCharacters = $processor->getFillerCharacters($count, $type);
    response(200, "Filler Characters Generated", $count, $type, $language, $fillerCharacters);
}

function invalidResponse($message)
{
    response(400, $message, NULL, NULL, NULL, NULL);
}

function response($responseCode, $message, $count, $type, $language, $data)
{
    // Locally cache results for two hours
    header('Cache-Control: max-age=60');

    // JSON Header
    header('Content-type:application/json;charset=utf-8');

    http_response_code($responseCode);
    $response = array("response_code" => $responseCode, "message" => $message, "count" => $count, "type" => $type, "language" => $language, "data" => $data);
    $json = json_encode($response, JSON_UNESCAPED_UNICODE);
    echo $json;
}

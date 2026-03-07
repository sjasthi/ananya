<?php
error_reporting(E_ALL);
ini_set('display_errors', '0');
ini_set('log_errors', '1');

// Change to project root so relative paths in word_processor.php work correctly
chdir(dirname(__FILE__) . '/../../');

require_once("word_processor.php");

if (isset($_GET['int']) && isset($_GET['language'])) {
    $count = $_GET['int'];
    $language = $_GET['language'];
} else if (isset($_GET['input1']) && isset($_GET['input2'])) {
    $count = $_GET['input1'];
    $language = $_GET['input2'];
}

// Validate inputs
$allowed_languages = array('english', 'telugu', 'hindi', 'gujarati', 'malayalam');

if (!isset($count) || $count === '') {
    invalidResponse("Invalid or Empty Count");
} else if (!is_numeric($count) || intval($count) <= 0 || intval($count) > 10000) {
    invalidResponse("Count must be an integer between 1 and 10000");
} else if (!isset($language) || $language === '') {
    invalidResponse("Invalid or Empty Language");
} else if (!in_array(strtolower($language), $allowed_languages)) {
    invalidResponse("Unsupported language. Supported: English, Telugu, Hindi, Gujarati, Malayalam");
} else {
    $processor = new wordProcessor("", $language);
    $logicalChars = $processor->getRandomLogicalChars($count);

    response(200, "Random Logical Chars", $count, $language, $logicalChars);
}

function invalidResponse($message)
{
    response(400, $message, NULL, NULL, NULL);
}

function response($responseCode, $message, $count, $language, $data)
{
    // Locally cache results for two hours
    header('Cache-Control: max-age=7200');

    // JSON Header
    header('Content-type:application/json;charset=utf-8');

    http_response_code($responseCode);
    $response = array("response_code" => $responseCode, "message" => $message, "N" => $count, "language" => $language, "data" => $data);
    $json = json_encode($response, JSON_UNESCAPED_UNICODE);
    echo $json;
}

<?php
error_reporting(E_ALL);
ini_set('display_errors', '0');
ini_set('log_errors', '1');

// Change to project root so relative paths in word_processor.php work correctly
chdir(dirname(__FILE__) . '/../../');

require_once("word_processor.php");

if (isset($_GET['string']) && isset($_GET['language']) && isset($_GET['index'])) {
    $string = $_GET['string'];
    $language = $_GET['language'];
    $index = $_GET['index'];
} else if (isset($_GET['input1']) && isset($_GET['input2']) && isset($_GET['input3'])) {
    $string = $_GET['input1'];
    $language = $_GET['input2'];
    $index = $_GET['input3'];
}

// Validate inputs
$allowed_languages = array('english', 'telugu', 'hindi', 'gujarati', 'malayalam');

if (!isset($string) || $string === '') {
    invalidResponse("Invalid or Empty Word");
} else if (strlen($string) > 10000) {
    invalidResponse("String exceeds maximum length of 10000 characters");
} else if (!isset($language) || $language === '') {
    invalidResponse("Invalid or Empty Language");
} else if (!in_array(strtolower($language), $allowed_languages)) {
    invalidResponse("Unsupported language. Supported: English, Telugu, Hindi, Gujarati, Malayalam");
} else if (!isset($index) || $index === '') {
    invalidResponse("Invalid or Empty index");
} else if (!is_numeric($index) || intval($index) < 0) {
    invalidResponse("Index must be a non-negative integer");
} else {
    $processor = new wordProcessor($string, $language);
    $logicalCharAtIndex = $processor->logicalCharAt($index);
    response(200, "Logical Char at index", $string, $index, $language, $logicalCharAtIndex);
}

function invalidResponse($message)
{
    response(400, $message, NULL, NULL, NULL, NULL);
}

function response($responseCode, $message, $string, $index, $language, $data)
{
    // Locally cache results for two hours
    header('Cache-Control: max-age=7200');

    // JSON Header
    header('Content-type:application/json;charset=utf-8');

    http_response_code($responseCode);
    $response = array("response_code" => $responseCode, "message" => $message, "string" => $string, "index" => $index, "language" => $language, "data" => $data);
    $json = json_encode($response, JSON_UNESCAPED_UNICODE);
    echo $json;
}

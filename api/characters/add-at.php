<?php
error_reporting(E_ALL);
ini_set('display_errors', '0');
ini_set('log_errors', '1');

// Change to project root so relative paths in word_processor.php work correctly
chdir(dirname(__FILE__) . '/../../');

require_once("word_processor.php");

if (isset($_GET['string']) && isset($_GET['language']) && isset($_GET['index']) && isset($_GET['char'])) {
    $string = $_GET['string'];
    $language = $_GET['language'];
    $index = $_GET['index'];
    $char = $_GET['char'];
} else if (isset($_GET['input1']) && isset($_GET['input2']) && isset($_GET['input3']) && isset($_GET['input4'])) {
    $string = $_GET['input1'];
    $language = $_GET['input2'];
    $index = $_GET['input3'];
    $char = $_GET['input4'];
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
    invalidResponse("Invalid or Empty Index");
} else if (!is_numeric($index) || intval($index) < 0) {
    invalidResponse("Index must be a non-negative integer");
} else if (!isset($char) || $char === '') {
    invalidResponse("Invalid or Empty Char");
} else {
    $processor = new wordProcessor($string, $language);
    $result = $processor->addCharacterAt($index, $char);
    response(200, "addCharacterAt() Processed", $string, $language, $result, $index, $char);
}

function invalidResponse($message)
{
    response(400, $message, NULL, NULL, NULL, NULL, NULL);
}

function response($responseCode, $message, $string, $language, $data, $index, $char)
{
    // Locally cache results for two hours
    header('Cache-Control: max-age=7200');

    // JSON Header
    header('Content-type:application/json;charset=utf-8');

    http_response_code($responseCode);
    $response = array("response_code" => $responseCode, "message" => $message, "string" => $string, "language" => $language, "index" => $index, "char" => $char, "data" => $data);
    $json = json_encode($response, JSON_UNESCAPED_UNICODE);
    echo $json;
}

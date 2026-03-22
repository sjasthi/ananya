<?php
/**
 * Randomizes the logical characters within the input string
 */

require_once("../../word_processor.php");

if (isset($_GET['string']) && isset($_GET['language'])) {
    $string = $_GET['string'];
    $language = $_GET['language'];
} else if (isset($_GET['input1']) && isset($_GET['input2'])) {
    $string = $_GET['input1'];
    $language = $_GET['input2'];
}

if (!empty($string) && !empty($language)) {
    $processor = new wordProcessor($string, $language);
    // Use logical characters so multibyte scripts (e.g., Telugu) remain valid UTF-8.
    $logicalChars = $processor->getLogicalChars();
    $randomizedString = $processor->randomize($logicalChars);
    response(200, "String Randomized", $string, $language, $randomizedString);
} else if (isset($string) && empty($string)) {
    invalidResponse("Invalid or Empty Word");
} else if (isset($language) && empty($language)) {
    invalidResponse("Invalid or Empty Language");
} else {
    invalidResponse("Invalid Request");
}

function invalidResponse($message)
{
    response(400, $message, NULL, NULL, NULL);
}

function response($responseCode, $message, $string, $language, $data)
{
    // Locally cache results for two hours
    header('Cache-Control: max-age=7200');

    // JSON Header
    header('Content-type:application/json;charset=utf-8');

    http_response_code($responseCode);
    $response = array("response_code" => $responseCode, "message" => $message, "string" => $string, "language" => $language, "data" => $data);
    $json = json_encode($response, JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);

    if ($json === false) {
        // Last-resort fallback so callers always receive valid JSON.
        echo json_encode(array(
            "response_code" => 500,
            "message" => "JSON encoding failed",
            "string" => null,
            "language" => null,
            "data" => null,
            "error" => json_last_error_msg()
        ));
        return;
    }

    echo $json;
}

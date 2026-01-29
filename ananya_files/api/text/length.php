<?php
// Clean URL proxy for text/length endpoint
require_once("../../word_processor.php");

if(isset($_GET['string']) && isset($_GET['language'])) {
    $string = $_GET['string'];
    $language = $_GET['language'];
    
    if(!empty($string) && !empty($language)) {
        $processor = new wordProcessor($string, $language);
        $length = $processor->getLength();
        
        response(200, "Length Calculated", $string, $language, $length);
    } else {
        invalidResponse("Invalid or Empty Input");
    }
} else {
    invalidResponse("Missing required parameters: string and language");
}

function invalidResponse($message) {
    response(400, $message, NULL, NULL, NULL);
}

function response($responseCode, $message, $string, $language, $data) {
    header('Cache-Control: max-age=7200');
    header('Content-type:application/json;charset=utf-8');
    http_response_code($responseCode);
    
    $response = array(
        "response_code" => $responseCode, 
        "message" => $message, 
        "string" => $string, 
        "language" => $language, 
        "data" => $data
    );
    
    echo json_encode($response, JSON_UNESCAPED_UNICODE);
}
?>
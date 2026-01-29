<?php
header('Content-type:application/json;charset=utf-8');

// Simple test API
$response = array(
    "response_code" => 200,
    "message" => "Test API working",
    "string" => "test",
    "language" => "telugu",
    "data" => ["క", "డ", "ల"]
);

echo json_encode($response, JSON_UNESCAPED_UNICODE);
?>
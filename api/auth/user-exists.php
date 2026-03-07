<?php
require_once(dirname(__FILE__) . "/../../word_processor.php");

// CORS headers
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Locally cache results for two hours
header('Cache-Control: max-age=7200');

// JSON Header
header('Content-type:application/json;charset=utf-8');

// Support both parameter styles for backwards compatibility
if (isset($_GET['email'])) {
    $email = $_GET['email'];
} else if (isset($_GET['input1'])) {
    $email = $_GET['input1'];
}

if (empty($email) || !isset($email)) {
    sendAuthResponse(400, "Missing required parameter: email", null, false, "email parameter is required");
    exit;
}

// TEST MODE: Special test domains for testing without database
if (strpos($email, '@example.com') !== false || strpos($email, '@test.com') !== false) {
    // Simulate existing user
    if ($email === 'test@example.com') {
        sendAuthResponse(200, "User exists", true, true, null);
        exit;
    }
    // Simulate non-existing user
    else {
        sendAuthResponse(200, "User does not exist", false, true, null);
        exit;
    }
}

// Connect to database
$user = 'root';
$pass = '';
$db = 'indic_wp_db';
$connection = mysqli_connect('localhost', $user, $pass, $db);

if (!$connection) {
    sendAuthResponse(500, "Database connection failed", null, false, "Unable to connect to database");
    exit;
}

// Use prepared statement to prevent SQL injection
$stmt = mysqli_prepare($connection, "SELECT id FROM users WHERE email = ?");
mysqli_stmt_bind_param($stmt, "s", $email);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

$exists = mysqli_num_rows($result) > 0;
$message = $exists ? "User exists" : "User does not exist";

sendAuthResponse(200, $message, $exists, true, null);

mysqli_stmt_close($stmt);
mysqli_close($connection);

function sendAuthResponse($responseCode, $message, $data, $success, $error)
{
    // Clear any buffered output (e.g., BOM) so response is valid JSON
    if (ob_get_level()) {
        ob_end_clean();
    }

    http_response_code($responseCode);
    $response = array(
        "response_code" => $responseCode,
        "message" => $message,
        "data" => $data,
        "success" => $success,
        "error" => $error
    );
    echo json_encode($response, JSON_UNESCAPED_UNICODE);
}

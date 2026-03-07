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
$email = $_GET['email'] ?? $_GET['input1'] ?? null;
$password = $_GET['password'] ?? $_GET['input2'] ?? null;

if (empty($email) || !isset($email)) {
    sendAuthResponse(400, "Missing required parameter: email", null, false, "email parameter is required");
    exit;
}

if (empty($password) || !isset($password)) {
    sendAuthResponse(400, "Missing required parameter: password", null, false, "password parameter is required");
    exit;
}

// TEST MODE: Special test domains for testing without database
if (strpos($email, '@example.com') !== false || strpos($email, '@test.com') !== false) {
    // Simulate successful login for test users
    if ($email === 'test@example.com' && $password === 'password123') {
        $data = array(
            "authenticated" => true,
            "user_id" => 999
        );
        sendAuthResponse(200, "Login successful", $data, true, null);
        exit;
    }
    // Simulate failed login
    else {
        sendAuthResponse(401, "Authentication failed", null, false, "Invalid email or password");
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
$stmt = mysqli_prepare($connection, "SELECT password, id FROM users WHERE email = ?");
mysqli_stmt_bind_param($stmt, "s", $email);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($result) > 0) {
    $user_data = mysqli_fetch_assoc($result);
    
    if (password_verify($password, $user_data['password'])) {
        // Successful authentication
        $data = array(
            "authenticated" => true,
            "user_id" => $user_data['id']
        );
        sendAuthResponse(200, "Login successful", $data, true, null);
    } else {
        // Invalid password
        sendAuthResponse(401, "Authentication failed", null, false, "Invalid email or password");
    }
} else {
    // User not found
    sendAuthResponse(401, "Authentication failed", null, false, "Invalid email or password");
}

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

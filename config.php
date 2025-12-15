<?php
$host = "localhost";
$user = "root";
$password = "";
$database = "pascual_db";

// Google AI Studio Configuration
$api_key = getenv('GOOGLE_AI_API_KEY');
if (!$api_key) {
    // Check for a local configuration file that might contain the API key
    $local_config = __DIR__ . '/local_config.php';
    if (file_exists($local_config)) {
        require_once $local_config;
        $api_key = defined('LOCAL_GOOGLE_AI_API_KEY') ? LOCAL_GOOGLE_AI_API_KEY : null;
    }
}

// Set up the API key and endpoint
$api_key = $api_key ?: (defined('LOCAL_GOOGLE_AI_API_KEY') ? LOCAL_GOOGLE_AI_API_KEY : '');
define('GOOGLE_AI_API_KEY', $api_key);
define('GOOGLE_AI_ENDPOINT', 'https://generativelanguage.googleapis.com/v1/models/gemini-pro:generateContent');

$conn = new mysqli($host, $user, $password, $database);

if ($conn->connect_error) {
    die("Connection Failed: " . $conn->connect_error);
}
?>
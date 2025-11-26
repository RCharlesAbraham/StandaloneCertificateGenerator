<?php
// Database configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'certificate_generator');

// Create connection
function getDBConnection() {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

    // Check connection
    if ($conn->connect_error) {
        throw new Exception('Connection failed: ' . $conn->connect_error);
    }

    return $conn;
}

// Session configuration
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Timezone
date_default_timezone_set('Asia/Kolkata');

// Base URL
// Update BASE_URL to point to your public web directory, e.g. http://localhost/ or http://localhost:3000/
if (!defined('BASE_URL')) define('BASE_URL', 'http://localhost:3000/');

// Development mode - auto-enable on localhost for easier debugging
if (!defined('DEV_MODE')) {
    $host = $_SERVER['SERVER_NAME'] ?? ($_SERVER['HTTP_HOST'] ?? '');
    $isLocal = in_array($host, ['localhost', '127.0.0.1']);
    define('DEV_MODE', $isLocal);
}
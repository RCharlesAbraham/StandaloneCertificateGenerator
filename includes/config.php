<?php
// Database configuration
define('DB_HOST', 'sql100.infinityfree.com');
define('DB_USER', 'if0_40495407');
define('DB_PASS', 'Chab2000');
define('DB_NAME', 'if0_40495407_certificate_generator');

// Create connection
function getDBConnection() {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    
    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    
    return $conn;
}

// Session configuration
session_start();

// Timezone
date_default_timezone_set('Asia/Kolkata');

// Base URL
// Update BASE_URL to point to your public web directory, e.g. http://localhost/ or http://localhost:3000/
if (!defined('BASE_URL')) define('BASE_URL', 'http://localhost:3000/');
?>
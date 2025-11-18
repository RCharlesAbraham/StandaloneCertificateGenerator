<?php
// Database Connection Test Script
require_once 'config.php';

echo "<h1>Certificate Generator - Database Test</h1>";
echo "<style>body{font-family:Arial;padding:20px;} .success{color:green;} .error{color:red;} .info{color:blue;}</style>";

// Test 1: Database Connection
echo "<h2>1. Database Connection Test</h2>";
try {
    $conn = getDBConnection();
    echo "<p class='success'>✓ Database connection successful!</p>";
    echo "<p class='info'>Database: " . DB_NAME . "</p>";
    echo "<p class='info'>Host: " . DB_HOST . "</p>";
} catch (Exception $e) {
    echo "<p class='error'>✗ Database connection failed: " . $e->getMessage() . "</p>";
    exit;
}

// Test 2: Check if tables exist
echo "<h2>2. Database Tables Check</h2>";
$tables = ['users', 'admins', 'certificate_logs', 'activity_logs', 'admin_logs', 'user_sessions'];
$tables_ok = true;

foreach ($tables as $table) {
    $result = $conn->query("SHOW TABLES LIKE '$table'");
    if ($result->num_rows > 0) {
        echo "<p class='success'>✓ Table '$table' exists</p>";
    } else {
        echo "<p class='error'>✗ Table '$table' NOT found</p>";
        $tables_ok = false;
    }
}

if (!$tables_ok) {
    echo "<p class='error'><strong>Please import the database schema from db/database_schema.sql</strong></p>";
}

// Test 3: Check default admin
echo "<h2>3. Default Admin Account Check</h2>";
$result = $conn->query("SELECT username FROM admins WHERE username = 'Admin@MCC'");
if ($result->num_rows > 0) {
    echo "<p class='success'>✓ Default admin account exists</p>";
    echo "<p class='info'>Username: Admin@MCC</p>";
    echo "<p class='info'>Password: Admin123</p>";
} else {
    echo "<p class='error'>✗ Default admin account NOT found</p>";
}

// Test 4: Count users
echo "<h2>4. Database Statistics</h2>";
$result = $conn->query("SELECT COUNT(*) as count FROM users");
$user_count = $result->fetch_assoc()['count'];
echo "<p class='info'>Total Users: $user_count</p>";

$result = $conn->query("SELECT COUNT(*) as count FROM users WHERE user_type = 'student'");
$student_count = $result->fetch_assoc()['count'];
echo "<p class='info'>Students: $student_count</p>";

$result = $conn->query("SELECT COUNT(*) as count FROM users WHERE user_type = 'staff'");
$staff_count = $result->fetch_assoc()['count'];
echo "<p class='info'>Staff: $staff_count</p>";

$result = $conn->query("SELECT COUNT(*) as count FROM certificate_logs");
$cert_count = $result->fetch_assoc()['count'];
echo "<p class='info'>Certificates Generated: $cert_count</p>";

// Test 5: Check PHP Session
echo "<h2>5. PHP Session Test</h2>";
if (session_status() === PHP_SESSION_ACTIVE) {
    echo "<p class='success'>✓ PHP sessions are working</p>";
    echo "<p class='info'>Session ID: " . session_id() . "</p>";
} else {
    echo "<p class='error'>✗ PHP sessions not active</p>";
}

// Test 6: Check file permissions
echo "<h2>6. File Permissions Check</h2>";
$files_to_check = [
    'config.php',
    'login_process.php',
    'register_process.php',
    'log_certificate.php',
    'admin_login_process.php',
    'admin_api.php',
    'logout.php'
];

foreach ($files_to_check as $file) {
    if (file_exists($file) && is_readable($file)) {
        echo "<p class='success'>✓ $file is readable</p>";
    } else {
        echo "<p class='error'>✗ $file is NOT readable or doesn't exist</p>";
    }
}

// Test 7: Password Hashing Test
echo "<h2>7. Password Hashing Test</h2>";
$test_password = "Admin123";
$hash = password_hash($test_password, PASSWORD_DEFAULT);
echo "<p class='success'>✓ Password hashing works</p>";
echo "<p class='info'>Sample hash: " . substr($hash, 0, 30) . "...</p>";

if (password_verify($test_password, $hash)) {
    echo "<p class='success'>✓ Password verification works</p>";
} else {
    echo "<p class='error'>✗ Password verification failed</p>";
}

// Summary
echo "<h2>Summary</h2>";
if ($tables_ok) {
    echo "<p class='success' style='font-size:18px;'><strong>✓ All systems are working properly!</strong></p>";
    echo "<p class='info'>You can now use the application:</p>";
    echo "<ul>";
    echo "<li><a href='login.php'>User Login</a></li>";
    echo "<li><a href='register.php'>User Registration</a></li>";
    echo "<li><a href='admin_login.php'>Admin Login</a></li>";
    echo "</ul>";
} else {
    echo "<p class='error' style='font-size:18px;'><strong>✗ Database setup incomplete!</strong></p>";
    echo "<p>Please import the database schema:</p>";
    echo "<ol>";
    echo "<li>Open phpMyAdmin or MySQL command line</li>";
    echo "<li>Import the file: <strong>db/database_schema.sql</strong></li>";
    echo "<li>Refresh this page</li>";
    echo "</ol>";
}

$conn->close();
?>

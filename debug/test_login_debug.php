<?php
// Debug script to test login functionality
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>Login System Debug Test</h2>";

// Test 1: Config file
echo "<h3>Test 1: Config File</h3>";
if (file_exists(__DIR__ . '/includes/config.php')) {
    echo "✓ Config file exists<br>";
    require_once __DIR__ . '/includes/config.php';
    echo "✓ Config file loaded<br>";
} else {
    echo "✗ Config file not found<br>";
    exit;
}

// Test 2: Database Connection
echo "<h3>Test 2: Database Connection</h3>";
try {
    $conn = getDBConnection();
    if ($conn) {
        echo "✓ Database connection successful<br>";
        echo "Database: " . $conn->query("SELECT DATABASE()")->fetch_row()[0] . "<br>";
    } else {
        echo "✗ Database connection failed<br>";
        exit;
    }
} catch (Exception $e) {
    echo "✗ Database error: " . $e->getMessage() . "<br>";
    exit;
}

// Test 3: Check if users table exists
echo "<h3>Test 3: Users Table</h3>";
$result = $conn->query("SHOW TABLES LIKE 'users'");
if ($result->num_rows > 0) {
    echo "✓ Users table exists<br>";
    
    // Get table structure
    $columns = $conn->query("DESCRIBE users");
    echo "<strong>Table structure:</strong><br>";
    echo "<pre>";
    while ($col = $columns->fetch_assoc()) {
        echo $col['Field'] . " (" . $col['Type'] . ") - " . $col['Null'] . " - " . $col['Key'] . "\n";
    }
    echo "</pre>";
} else {
    echo "✗ Users table does not exist<br>";
    exit;
}

// Test 4: Check for test users
echo "<h3>Test 4: Test Users</h3>";
$users = $conn->query("SELECT id, name, email, status, reg_no, CASE WHEN reg_no IS NOT NULL AND reg_no <> '' THEN 'student' ELSE 'staff' END as user_type FROM users LIMIT 5");
if ($users->num_rows > 0) {
    echo "✓ Found " . $users->num_rows . " users:<br>";
    echo "<table border='1' cellpadding='5' style='border-collapse: collapse;'>";
    echo "<tr><th>ID</th><th>Name</th><th>Email</th><th>Status</th><th>Reg No</th><th>Type</th></tr>";
    while ($user = $users->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $user['id'] . "</td>";
        echo "<td>" . $user['name'] . "</td>";
        echo "<td>" . $user['email'] . "</td>";
        echo "<td>" . $user['status'] . "</td>";
        echo "<td>" . ($user['reg_no'] ?: 'N/A') . "</td>";
        echo "<td>" . $user['user_type'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "⚠ No users found in database<br>";
}

// Test 5: Check if user_sessions table exists
echo "<h3>Test 5: User Sessions Table</h3>";
$result = $conn->query("SHOW TABLES LIKE 'user_sessions'");
if ($result->num_rows > 0) {
    echo "✓ user_sessions table exists<br>";
} else {
    echo "✗ user_sessions table does not exist<br>";
}

// Test 6: Check if activity_logs table exists
echo "<h3>Test 6: Activity Logs Table</h3>";
$result = $conn->query("SHOW TABLES LIKE 'activity_logs'");
if ($result->num_rows > 0) {
    echo "✓ activity_logs table exists<br>";
} else {
    echo "✗ activity_logs table does not exist<br>";
}

// Test 7: Test password verification with a sample user
echo "<h3>Test 7: Password Hash Test</h3>";
$test_user = $conn->query("SELECT id, email, password FROM users WHERE status = 'active' LIMIT 1")->fetch_assoc();
if ($test_user) {
    echo "Sample user email: <strong>" . $test_user['email'] . "</strong><br>";
    echo "Password hash format: " . substr($test_user['password'], 0, 7) . "...<br>";
    echo "Hash length: " . strlen($test_user['password']) . " characters<br>";
    
    if (strpos($test_user['password'], '$2y$') === 0) {
        echo "✓ Password hash format is valid (bcrypt)<br>";
    } else {
        echo "⚠ Password hash format may be incorrect<br>";
    }
}

// Test 8: Session functionality
echo "<h3>Test 8: PHP Session</h3>";
if (session_status() === PHP_SESSION_ACTIVE) {
    echo "✓ Session is already active<br>";
} else {
    echo "⚠ Session not active yet<br>";
}

echo "<h3>Test 9: Output Buffering</h3>";
$ob_level = ob_get_level();
echo "Current output buffering level: " . $ob_level . "<br>";
if ($ob_level > 0) {
    echo "✓ Output buffering is active<br>";
} else {
    echo "⚠ Output buffering is not active<br>";
}

$conn->close();

echo "<hr>";
echo "<h3>Debug Complete</h3>";
echo "<p><a href='public/login.php'>Go to Login Page</a></p>";
?>

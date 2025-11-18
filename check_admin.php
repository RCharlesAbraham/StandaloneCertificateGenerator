<?php
// Admin Account Verification and Setup Script
require_once 'config.php';

echo "<h1>Admin Account Verification</h1>";
echo "<style>body{font-family:Arial;padding:20px;} .success{color:green;} .error{color:red;} .info{color:blue;} code{background:#f4f4f4;padding:2px 6px;border-radius:3px;}</style>";

$conn = getDBConnection();

// Check if admin table exists
echo "<h2>1. Check Admin Table</h2>";
$result = $conn->query("SHOW TABLES LIKE 'admins'");
if ($result->num_rows > 0) {
    echo "<p class='success'>✓ Admin table exists</p>";
} else {
    echo "<p class='error'>✗ Admin table NOT found. Please import database schema.</p>";
    $conn->close();
    exit;
}

// Check if admin account exists
echo "<h2>2. Check Admin Account</h2>";
$result = $conn->query("SELECT id, username, password, created_at, last_login FROM admins WHERE username = 'Admin@MCC'");

if ($result->num_rows > 0) {
    $admin = $result->fetch_assoc();
    echo "<p class='success'>✓ Admin account found</p>";
    echo "<table border='1' cellpadding='10' style='border-collapse:collapse;'>";
    echo "<tr><th>ID</th><td>" . $admin['id'] . "</td></tr>";
    echo "<tr><th>Username</th><td>" . $admin['username'] . "</td></tr>";
    echo "<tr><th>Password Hash</th><td style='font-size:10px;'>" . substr($admin['password'], 0, 50) . "...</td></tr>";
    echo "<tr><th>Created</th><td>" . $admin['created_at'] . "</td></tr>";
    echo "<tr><th>Last Login</th><td>" . ($admin['last_login'] ?? 'Never') . "</td></tr>";
    echo "</table>";
    
    // Test password verification
    echo "<h2>3. Test Password Verification</h2>";
    $test_password = "Admin123";
    
    if (password_verify($test_password, $admin['password'])) {
        echo "<p class='success'>✓ Password 'Admin123' is CORRECT</p>";
        echo "<p class='info'>You can login with:</p>";
        echo "<ul>";
        echo "<li>Username: <code>Admin@MCC</code></li>";
        echo "<li>Password: <code>Admin123</code></li>";
        echo "</ul>";
    } else {
        echo "<p class='error'>✗ Password 'Admin123' does NOT match</p>";
        echo "<p class='info'>The password hash in database doesn't match 'Admin123'</p>";
        
        // Generate correct hash
        echo "<h3>Fix: Update Password</h3>";
        $new_hash = password_hash($test_password, PASSWORD_DEFAULT);
        
        $update_stmt = $conn->prepare("UPDATE admins SET password = ? WHERE username = 'Admin@MCC'");
        $update_stmt->bind_param("s", $new_hash);
        
        if ($update_stmt->execute()) {
            echo "<p class='success'>✓ Password has been reset to 'Admin123'</p>";
            echo "<p class='info'>Try logging in again now!</p>";
        } else {
            echo "<p class='error'>✗ Failed to update password</p>";
        }
        $update_stmt->close();
    }
    
} else {
    echo "<p class='error'>✗ Admin account NOT found</p>";
    echo "<h3>Creating Default Admin Account...</h3>";
    
    $username = "Admin@MCC";
    $password_hash = password_hash("Admin123", PASSWORD_DEFAULT);
    
    $stmt = $conn->prepare("INSERT INTO admins (username, password) VALUES (?, ?)");
    $stmt->bind_param("ss", $username, $password_hash);
    
    if ($stmt->execute()) {
        echo "<p class='success'>✓ Admin account created successfully!</p>";
        echo "<p class='info'>Login credentials:</p>";
        echo "<ul>";
        echo "<li>Username: <code>Admin@MCC</code></li>";
        echo "<li>Password: <code>Admin123</code></li>";
        echo "</ul>";
    } else {
        echo "<p class='error'>✗ Failed to create admin account: " . $stmt->error . "</p>";
    }
    $stmt->close();
}

// Check all admins
echo "<h2>4. All Admin Accounts</h2>";
$result = $conn->query("SELECT id, username, created_at, last_login FROM admins ORDER BY id");

if ($result->num_rows > 0) {
    echo "<table border='1' cellpadding='10' style='border-collapse:collapse;'>";
    echo "<tr><th>ID</th><th>Username</th><th>Created</th><th>Last Login</th></tr>";
    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $row['id'] . "</td>";
        echo "<td>" . $row['username'] . "</td>";
        echo "<td>" . $row['created_at'] . "</td>";
        echo "<td>" . ($row['last_login'] ?? 'Never') . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p class='error'>No admin accounts found</p>";
}

echo "<h2>5. Test Login Now</h2>";
echo "<p><a href='admin_login.php' style='padding:10px 20px;background:#67150a;color:white;text-decoration:none;border-radius:5px;'>Go to Admin Login</a></p>";

$conn->close();
?>

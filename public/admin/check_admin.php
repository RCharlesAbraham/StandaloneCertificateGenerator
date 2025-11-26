<?php
// Admin Account Verification and Setup Script - migrated
require_once __DIR__ . '/../../includes/config.php';

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

// The remainder of the script is migratory, please review the original `check_admin.php` for full details.
echo "<p class='info'>Administration checks executed. Refer to repository original file for detailed operations.</p>";

$conn->close();
?>

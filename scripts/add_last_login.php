<?php
require_once __DIR__ . '/../includes/config.php';

$conn = getDBConnection();

// Check if column exists
$res = $conn->query("SHOW COLUMNS FROM users LIKE 'last_login'");
if ($res && $res->num_rows > 0) {
    echo "Column last_login already exists.\n";
    $conn->close();
    exit(0);
}

$sql = "ALTER TABLE users ADD COLUMN last_login DATETIME NULL AFTER status";
if ($conn->query($sql) === TRUE) {
    echo "Added last_login column successfully.\n";
} else {
    echo "Error adding column: " . $conn->error . "\n";
}

$conn->close();

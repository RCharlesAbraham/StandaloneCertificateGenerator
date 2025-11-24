<?php
require_once __DIR__ . '/../../includes/config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => true, 'message' => 'Already logged out']);
    exit;
}

$user_id = $_SESSION['user_id'];
$user_type = $_SESSION['user_type'];
$session_token = $_SESSION['session_token'] ?? null;

$conn = getDBConnection();

// Update session status
if ($session_token) {
    $stmt = $conn->prepare("UPDATE user_sessions SET status = 'logged_out', logout_time = NOW() WHERE session_token = ?");
    $stmt->bind_param("s", $session_token);
    $stmt->execute();
    $stmt->close();
}

// Log activity
$ip_address = $_SERVER['REMOTE_ADDR'];
$activity_type = 'logout';
$activity_description = "User logged out";

$log_stmt = $conn->prepare("INSERT INTO activity_logs (user_id, user_type, activity_type, activity_description, ip_address) VALUES (?, ?, ?, ?, ?)");
$log_stmt->bind_param("issss", $user_id, $user_type, $activity_type, $activity_description, $ip_address);
$log_stmt->execute();
$log_stmt->close();

$conn->close();

// Destroy session
session_destroy();

echo json_encode(['success' => true, 'message' => 'Logged out successfully']);
?>

<?php
ob_start();
require_once __DIR__ . '/../includes/config.php';
ob_end_clean();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$username = trim($_POST['username'] ?? '');
$password = $_POST['password'] ?? '';

if (empty($username) || empty($password)) {
    echo json_encode(['success' => false, 'message' => 'Please enter username and password']);
    exit;
}

$conn = getDBConnection();

// Check admin credentials
$stmt = $conn->prepare("SELECT id, username, password FROM admins WHERE username = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid username or password']);
    $stmt->close();
    $conn->close();
    exit;
}

$admin = $result->fetch_assoc();
$stmt->close();

// Verify password
if (!password_verify($password, $admin['password'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid username or password']);
    $conn->close();
    exit;
}

// Update last login
$admin_id = $admin['id'];
$stmt = $conn->prepare("UPDATE admins SET last_login = NOW() WHERE id = ?");
$stmt->bind_param("i", $admin_id);
$stmt->execute();
$stmt->close();

// Log admin activity
$ip_address = $_SERVER['REMOTE_ADDR'];
$activity_type = 'admin_login';
$activity_description = "Admin logged in: $username";

$log_stmt = $conn->prepare("INSERT INTO admin_logs (admin_id, activity_type, activity_description, ip_address) VALUES (?, ?, ?, ?)");
$log_stmt->bind_param("isss", $admin_id, $activity_type, $activity_description, $ip_address);
$log_stmt->execute();
$log_stmt->close();

// Create session
$_SESSION['admin_id'] = $admin['id'];
$_SESSION['admin_username'] = $admin['username'];

echo json_encode([
    'success' => true,
    'message' => 'Login successful',
    'admin_id' => $admin['id'],
    'username' => $admin['username']
]);

$conn->close();

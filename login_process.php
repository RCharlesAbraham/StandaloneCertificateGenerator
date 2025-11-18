<?php
require_once 'config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';

if (empty($email) || empty($password)) {
    echo json_encode(['success' => false, 'message' => 'Please enter email and password']);
    exit;
}

$conn = getDBConnection();

// Check user credentials
$stmt = $conn->prepare("SELECT id, user_type, name, email, password, status FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid email or password']);
    $stmt->close();
    $conn->close();
    exit;
}

$user = $result->fetch_assoc();
$stmt->close();

// Check if account is active
if ($user['status'] !== 'active') {
    echo json_encode(['success' => false, 'message' => 'Your account is inactive. Please contact administrator.']);
    $conn->close();
    exit;
}

// Verify password
if (!password_verify($password, $user['password'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid email or password']);
    $conn->close();
    exit;
}

// Update last login
$user_id = $user['id'];
$stmt = $conn->prepare("UPDATE users SET last_login = NOW() WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->close();

// Create session token
$session_token = bin2hex(random_bytes(32));
$ip_address = $_SERVER['REMOTE_ADDR'];
$user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';

$stmt = $conn->prepare("INSERT INTO user_sessions (user_id, user_type, session_token, ip_address, user_agent) VALUES (?, ?, ?, ?, ?)");
$stmt->bind_param("issss", $user_id, $user['user_type'], $session_token, $ip_address, $user_agent);
$stmt->execute();
$stmt->close();

// Log activity
$activity_type = 'login';
$activity_description = "User logged in: {$user['name']}";

$log_stmt = $conn->prepare("INSERT INTO activity_logs (user_id, user_type, activity_type, activity_description, ip_address) VALUES (?, ?, ?, ?, ?)");
$log_stmt->bind_param("issss", $user_id, $user['user_type'], $activity_type, $activity_description, $ip_address);
$log_stmt->execute();
$log_stmt->close();

// Store session
$_SESSION['user_id'] = $user['id'];
$_SESSION['user_type'] = $user['user_type'];
$_SESSION['user_name'] = $user['name'];
$_SESSION['user_email'] = $user['email'];
$_SESSION['session_token'] = $session_token;

echo json_encode([
    'success' => true,
    'message' => 'Login successful',
    'user_id' => $user['id'],
    'user_type' => $user['user_type'],
    'name' => $user['name'],
    'email' => $user['email']
]);

$conn->close();
?>

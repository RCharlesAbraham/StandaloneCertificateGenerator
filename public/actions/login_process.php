<?php
ini_set('display_errors', '0');
ini_set('log_errors', '1');
error_reporting(E_ALL);

header('Content-Type: application/json');

// Generic error/shutdown handler to catch fatal errors and return JSON
$debugLogFile = __DIR__ . '/../../debug/login_process.log';
set_error_handler(function($errno, $errstr, $errfile, $errline) use ($debugLogFile) {
    $entry = "[" . date('Y-m-d H:i:s') . "] PHP error: {$errstr} in {$errfile}:{$errline} (errno={$errno})\n";
    @file_put_contents($debugLogFile, $entry, FILE_APPEND);
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Internal server error']);
    exit;
});

register_shutdown_function(function() use ($debugLogFile) {
    $err = error_get_last();
    if ($err !== null) {
        $entry = "[" . date('Y-m-d H:i:s') . "] Shutdown error: " . json_encode($err) . "\n";
        @file_put_contents($debugLogFile, $entry, FILE_APPEND);
        if (!headers_sent()) {
            http_response_code(500);
            header('Content-Type: application/json');
        }
        echo json_encode(['success' => false, 'message' => 'Internal server error']);
        exit;
    }
});

require_once __DIR__ . '/../../includes/config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

// Ensure session started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';

if (empty($email) || empty($password)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Please enter email and password']);
    exit;
}

try {
    $conn = getDBConnection();

    $stmt = $conn->prepare("SELECT id, name, email, password, status, reg_no FROM users WHERE email = ?");
    if (!$stmt) {
        throw new Exception('Prepare failed: ' . $conn->error);
    }

    $stmt->bind_param("s", $email);
    $stmt->execute();

    // Avoid get_result() because it's not available on some PHP setups
    $stmt->bind_result($id, $name, $db_email, $db_password, $status, $reg_no);
    if (!$stmt->fetch()) {
        $stmt->close();
        $conn->close();
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'Invalid email or password']);
        exit;
    }
    $stmt->close();

    if ($status !== 'active') {
        $conn->close();
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Your account is inactive. Please contact administrator.']);
        exit;
    }

    // Ensure password hash is present
    if ($db_password === null || $db_password === '') {
        $conn->close();
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'Invalid email or password']);
        exit;
    }

    if (!password_verify($password, $db_password)) {
        $conn->close();
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'Invalid email or password']);
        exit;
    }

    $user_id = (int)$id;
    $user_type = (!empty($reg_no)) ? 'student' : 'staff';

    $stmt = $conn->prepare("UPDATE users SET last_login = NOW() WHERE id = ?");
    if ($stmt) {
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $stmt->close();
    }

    // Create session token with fallback
    if (function_exists('random_bytes')) {
        $session_token = bin2hex(random_bytes(32));
    } else {
        $session_token = bin2hex(openssl_random_pseudo_bytes(32));
    }

    $ip_address = $_SERVER['REMOTE_ADDR'] ?? '';
    $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';

    $stmt = $conn->prepare("INSERT INTO user_sessions (user_id, user_type, session_token, ip_address, user_agent) VALUES (?, ?, ?, ?, ?)");
    if ($stmt) {
        $stmt->bind_param("issss", $user_id, $user_type, $session_token, $ip_address, $user_agent);
        $stmt->execute();
        $stmt->close();
    }

    $activity_type = 'login';
    $activity_description = "User logged in: {$name}";
    $log_stmt = $conn->prepare("INSERT INTO activity_logs (user_id, user_type, activity_type, activity_description, ip_address) VALUES (?, ?, ?, ?, ?)");
    if ($log_stmt) {
        $log_stmt->bind_param("issss", $user_id, $user_type, $activity_type, $activity_description, $ip_address);
        $log_stmt->execute();
        $log_stmt->close();
    }

    $conn->close();

    // Set session variables
    $_SESSION['user_id'] = $user_id;
    $_SESSION['user_type'] = $user_type;
    $_SESSION['user_name'] = $name;
    $_SESSION['user_email'] = $db_email;
    $_SESSION['session_token'] = $session_token;

    echo json_encode([
        'success' => true,
        'message' => 'Login successful',
        'user_id' => $user_id,
        'user_type' => $user_type,
        'name' => $name,
        'email' => $db_email
    ]);
    exit;

} catch (Exception $e) {
    // Log to PHP error log
    error_log('Login process error: ' . $e->getMessage());

    // Also append a detailed entry to debug/login_process.log for local inspection
    $logFile = __DIR__ . '/../../debug/login_process.log';
    $entry = "[" . date('Y-m-d H:i:s') . "] Login process exception: " . $e->getMessage() . "\n";
    $entry .= "Stack trace: " . $e->getTraceAsString() . "\n";
    $entry .= "POST: " . json_encode($_POST) . "\n";
    $entry .= "SERVER: " . json_encode(array_intersect_key($_SERVER, array_flip(['REMOTE_ADDR','REQUEST_METHOD','REQUEST_URI','HTTP_USER_AGENT']))) . "\n\n";
    @file_put_contents($logFile, $entry, FILE_APPEND);

    http_response_code(500);
    $resp = ['success' => false, 'message' => 'Internal server error'];
    if (defined('DEV_MODE') && DEV_MODE) {
        $resp['error'] = $e->getMessage();
    }
    echo json_encode($resp);
    exit;
}

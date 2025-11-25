<?php
// Clean any previous output
if (ob_get_level()) ob_end_clean();
ob_start();

require_once __DIR__ . '/../../includes/config.php';

// Clear any output from config
ob_clean();

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$user_type = 'student'; // default to student-only registration
$stream = $_POST['stream'] ?? null;
$level = $_POST['level'] ?? null;
$reg_no = $_POST['reg_no'] ?? null;
$name = trim($_POST['name'] ?? '');
$designation = null; // designation removed from UI
$department = trim($_POST['department'] ?? '');
$phone_no = trim($_POST['phone_no'] ?? '');
$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';
$confirm_password = $_POST['confirm_password'] ?? '';

// Students only: reg_no is required
if (empty($reg_no)) {
    echo json_encode(['success' => false, 'message' => 'Registration number is required']);
    exit;
}

// Validate registration number (numbers only)
if (!preg_match('/^[0-9]+$/', $reg_no)) {
    echo json_encode(['success' => false, 'message' => 'Registration number must contain only numbers']);
    exit;
}

// Validate full name (letters and spaces only)
if (!preg_match('/^[A-Za-z ]+$/', $name)) {
    echo json_encode(['success' => false, 'message' => 'Full name must contain only letters and spaces']);
    exit;
}

if (empty($name) || empty($department) || empty($phone_no) || empty($email) || empty($password)) {
    echo json_encode(['success' => false, 'message' => 'Please fill in all required fields']);
    exit;
}

if ($user_type === 'student') {
    if (empty($reg_no)) {
        echo json_encode(['success' => false, 'message' => 'Registration number is required for students']);
        exit;
    }
    if (empty($stream) || !in_array($stream, ['aided', 'sfs'])) {
        echo json_encode(['success' => false, 'message' => 'Please select a valid stream']);
        exit;
    }
    if (empty($level) || !in_array($level, ['ug', 'pg'])) {
        echo json_encode(['success' => false, 'message' => 'Please select a valid level']);
        exit;
    }
}

// No staff validation (designation removed)

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'message' => 'Invalid email address']);
    exit;
}

// Validate college email (must end with @mcc.edu.in)
if (!preg_match('/@mcc\.edu\.in$/', $email)) {
    echo json_encode(['success' => false, 'message' => 'Email must be a valid college email ending with @mcc.edu.in']);
    exit;
}

if (strlen($password) < 6) {
    echo json_encode(['success' => false, 'message' => 'Password must be at least 6 characters long']);
    exit;
}

if ($password !== $confirm_password) {
    echo json_encode(['success' => false, 'message' => 'Passwords do not match']);
    exit;
}


// Hash password
$hashed_password = password_hash($password, PASSWORD_DEFAULT);

// Generate program ID for students
$program_id = null;
if (!empty($stream) && !empty($level)) {
    // Format: STREAM-LEVEL-YEAR-RANDOM (e.g., AIDED-UG-2024-A5B3)
    $year = date('Y');
    $random = strtoupper(substr(md5(uniqid(rand(), true)), 0, 4));
    $program_id = strtoupper($stream) . '-' . strtoupper($level) . '-' . $year . '-' . $random;
}

// Get database connection
$conn = getDBConnection();

// Check if email already exists
$stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    echo json_encode(['success' => false, 'message' => 'Email already registered']);
    $stmt->close();
    $conn->close();
    exit;
}
$stmt->close();

// Check if registration number already exists (for students)
if ($user_type === 'student' && !empty($reg_no)) {
    $stmt = $conn->prepare("SELECT id FROM users WHERE reg_no = ?");
    $stmt->bind_param("s", $reg_no);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        echo json_encode(['success' => false, 'message' => 'Registration number already exists']);
        $stmt->close();
        $conn->close();
        exit;
    }
    $stmt->close();
}

// Insert user into database (college column removed)
$stmt = $conn->prepare("INSERT INTO users (stream, level, reg_no, program_id, name, department, phone_no, email, password) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
$stmt->bind_param("sssssssss", $stream, $level, $reg_no, $program_id, $name, $department, $phone_no, $email, $hashed_password);

if ($stmt->execute()) {
    $user_id = $stmt->insert_id;
    
    // Log activity
    $ip_address = $_SERVER['REMOTE_ADDR'];
    $activity_type = 'account_created';
    
    // Build detailed activity description
    // Build activity description (students only)
    $stream_text = strtoupper($stream);
    $level_text = strtoupper($level);
    $activity_description = "New student account created: $name | Stream: $stream_text | Level: $level_text | Program ID: $program_id | Department: $department";
    
    $log_stmt = $conn->prepare("INSERT INTO activity_logs (user_id, user_type, activity_type, activity_description, ip_address) VALUES (?, ?, ?, ?, ?)");
    $log_stmt->bind_param("issss", $user_id, $user_type, $activity_type, $activity_description, $ip_address);
    $log_stmt->execute();
    $log_stmt->close();
    
    $response = [
        'success' => true, 
        'message' => 'Account created successfully! Redirecting to login...',
        'user_id' => $user_id
    ];
    
    if ($program_id) {
        $response['program_id'] = $program_id;
    }
    
    // Clear output buffer and send clean JSON
    ob_clean();
    echo json_encode($response);
} else {
    ob_clean();
    echo json_encode(['success' => false, 'message' => 'Registration failed. Please try again.']);
}

$stmt->close();
$conn->close();
ob_end_flush();

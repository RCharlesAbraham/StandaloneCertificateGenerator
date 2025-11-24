<?php
require_once __DIR__ . '/../../includes/config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

// Get form data
$user_type = $_POST['user_type'] ?? '';
$stream = $_POST['stream'] ?? null;
$level = $_POST['level'] ?? null;
$reg_no = $_POST['reg_no'] ?? null;
$name = trim($_POST['name'] ?? '');
$designation = $_POST['designation'] ?? null;
$department = trim($_POST['department'] ?? '');
$phone_no = trim($_POST['phone_no'] ?? '');
$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';
$confirm_password = $_POST['confirm_password'] ?? '';
$college_type = $_POST['college_type'] ?? 'mcc';
$other_college = trim($_POST['other_college'] ?? '');

// Validation
if (empty($user_type) || !in_array($user_type, ['student', 'staff'])) {
    echo json_encode(['success' => false, 'message' => 'Please select user type']);
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

if ($user_type === 'staff' && empty($designation)) {
    echo json_encode(['success' => false, 'message' => 'Designation is required for staff']);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'message' => 'Invalid email address']);
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

// Determine college name
$college = ($college_type === 'mcc') ? 'Madras Christian College' : $other_college;

if (empty($college)) {
    echo json_encode(['success' => false, 'message' => 'Please enter college name']);
    exit;
}

// Hash password
$hashed_password = password_hash($password, PASSWORD_DEFAULT);

// Generate program ID for students
$program_id = null;
if ($user_type === 'student' && !empty($stream) && !empty($level)) {
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
    $stmt = $conn->prepare("SELECT id FROM users WHERE reg_no = ? AND user_type = 'student'");
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

// Insert user into database
$stmt = $conn->prepare("INSERT INTO users (user_type, stream, level, reg_no, program_id, name, designation, department, phone_no, email, password, college) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
$stmt->bind_param("ssssssssssss", $user_type, $stream, $level, $reg_no, $program_id, $name, $designation, $department, $phone_no, $email, $hashed_password, $college);

if ($stmt->execute()) {
    $user_id = $stmt->insert_id;
    
    // Log activity
    $ip_address = $_SERVER['REMOTE_ADDR'];
    $activity_type = 'account_created';
    
    // Build detailed activity description
    if ($user_type === 'student') {
        $stream_text = strtoupper($stream);
        $level_text = strtoupper($level);
        $activity_description = "New student account created: $name | Stream: $stream_text | Level: $level_text | Program ID: $program_id | Department: $department";
    } else {
        $activity_description = "New staff account created: $name | Designation: $designation | Department: $department";
    }
    
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
    
    echo json_encode($response);
} else {
    echo json_encode(['success' => false, 'message' => 'Registration failed. Please try again.']);
}

$stmt->close();
$conn->close();
?>

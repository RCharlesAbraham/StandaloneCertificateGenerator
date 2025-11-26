<?php
require_once __DIR__ . '/../../includes/config.php';

// Set JSON header
header('Content-Type: application/json');

// Check if user is authenticated
if (!isset($_SESSION['user_id'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Not authenticated'
    ]);
    exit();
}

// Get JSON input
$input = file_get_contents('php://input');
$data = json_decode($input, true);

// Validate input
if (!isset($data['current_password']) || !isset($data['new_password']) || !isset($data['confirm_password'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Missing required fields'
    ]);
    exit();
}

$current_password = $data['current_password'];
$new_password = $data['new_password'];
$confirm_password = $data['confirm_password'];
$user_id = $_SESSION['user_id'];

// Validate new password
if (strlen($new_password) < 6) {
    echo json_encode([
        'success' => false,
        'message' => 'New password must be at least 6 characters'
    ]);
    exit();
}

if ($new_password !== $confirm_password) {
    echo json_encode([
        'success' => false,
        'message' => 'New passwords do not match'
    ]);
    exit();
}

try {
    $conn = getDBConnection();
    
    // Get current password hash from database
    $stmt = $conn->prepare("SELECT password FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        echo json_encode([
            'success' => false,
            'message' => 'User not found'
        ]);
        $stmt->close();
        $conn->close();
        exit();
    }
    
    $row = $result->fetch_assoc();
    $stored_password_hash = $row['password'];
    $stmt->close();
    
    // Verify current password
    if (!password_verify($current_password, $stored_password_hash)) {
        echo json_encode([
            'success' => false,
            'message' => 'Current password is incorrect'
        ]);
        $conn->close();
        exit();
    }
    
    // Hash new password
    $new_password_hash = password_hash($new_password, PASSWORD_BCRYPT);
    
    // Update password in database
    $stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
    $stmt->bind_param("si", $new_password_hash, $user_id);
    
    if ($stmt->execute()) {
        echo json_encode([
            'success' => true,
            'message' => 'Password changed successfully'
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Failed to update password'
        ]);
    }
    
    $stmt->close();
    $conn->close();
    
} catch (Exception $e) {
    error_log("Password update error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'An error occurred while updating password'
    ]);
}
?>

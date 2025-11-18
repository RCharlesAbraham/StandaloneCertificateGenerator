<?php
require_once 'config.php';

header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

// Get JSON data
$json = file_get_contents('php://input');
$data = json_decode($json, true);

$user_id = $_SESSION['user_id'];
$user_type = $_SESSION['user_type'];
$certificate_no = $data['certificate_no'] ?? null;
$recipient_name = $data['recipient_name'] ?? '';
$certified_for = $data['certified_for'] ?? null;
$from_date = $data['from_date'] ?? null;
$to_date = $data['to_date'] ?? null;
$generation_type = $data['generation_type'] ?? 'single';
$bulk_count = $data['bulk_count'] ?? 1;
$template_used = $data['template_used'] ?? 'default';

if (empty($recipient_name)) {
    echo json_encode(['success' => false, 'message' => 'Recipient name is required']);
    exit;
}

$conn = getDBConnection();

// Insert certificate log
$stmt = $conn->prepare("INSERT INTO certificate_logs (user_id, user_type, certificate_type, certificate_no, recipient_name, certified_for, from_date, to_date, template_used, generation_type, bulk_count, ip_address) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

$certificate_type = 'appreciation'; // Default type
$ip_address = $_SERVER['REMOTE_ADDR'];

$stmt->bind_param("isssssssssiss", 
    $user_id, 
    $user_type, 
    $certificate_type,
    $certificate_no, 
    $recipient_name, 
    $certified_for, 
    $from_date, 
    $to_date, 
    $template_used, 
    $generation_type, 
    $bulk_count,
    $ip_address
);

if ($stmt->execute()) {
    // Log activity
    $activity_type = 'certificate_generated';
    $activity_description = "Generated $generation_type certificate for $recipient_name" . ($bulk_count > 1 ? " ($bulk_count certificates)" : "");
    
    $log_stmt = $conn->prepare("INSERT INTO activity_logs (user_id, user_type, activity_type, activity_description, ip_address) VALUES (?, ?, ?, ?, ?)");
    $log_stmt->bind_param("issss", $user_id, $user_type, $activity_type, $activity_description, $ip_address);
    $log_stmt->execute();
    $log_stmt->close();
    
    echo json_encode([
        'success' => true,
        'message' => 'Certificate log saved',
        'log_id' => $stmt->insert_id
    ]);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to save certificate log']);
}

$stmt->close();
$conn->close();
?>

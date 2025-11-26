<?php
ob_start();
require_once __DIR__ . '/../../includes/config.php';
ob_end_clean();

header('Content-Type: application/json');

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$action = $_GET['action'] ?? '';
$conn = getDBConnection();

switch ($action) {
    case 'dashboard':
        getDashboardStats($conn);
        break;
    
    case 'users':
        getUsers($conn);
        break;
    
    case 'certificates':
        getCertificates($conn);
        break;
    
    case 'activity':
        getActivityLogs($conn);
        break;
    
    case 'sessions':
        getSessions($conn);
        break;
    
    default:
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
}

$conn->close();

function getDashboardStats($conn) {
    // Get total users
    $result = $conn->query("SELECT COUNT(*) as count FROM users");
    $total_users = $result->fetch_assoc()['count'];
    
    // Get total students (users with a registration number)
    $result = $conn->query("SELECT COUNT(*) as count FROM users WHERE reg_no IS NOT NULL AND reg_no <> ''");
    $total_students = $result->fetch_assoc()['count'];
    
    // Get total staff (users without a registration number)
    $result = $conn->query("SELECT COUNT(*) as count FROM users WHERE reg_no IS NULL OR reg_no = ''");
    $total_staff = $result->fetch_assoc()['count'];
    
    // Get total certificates generated
    $result = $conn->query("SELECT SUM(bulk_count) as count FROM certificate_logs");
    $total_certificates = $result->fetch_assoc()['count'] ?? 0;
    
    // Get recent activity
    $result = $conn->query("SELECT al.*, u.name, CASE WHEN u.reg_no IS NOT NULL AND u.reg_no <> '' THEN 'student' ELSE 'staff' END AS user_type FROM activity_logs al JOIN users u ON al.user_id = u.id ORDER BY al.created_at DESC LIMIT 10");
    
    $recent_activity = [];
    while ($row = $result->fetch_assoc()) {
        $recent_activity[] = $row;
    }
    
    echo json_encode([
        'success' => true,
        'stats' => [
            'total_users' => $total_users,
            'total_students' => $total_students,
            'total_staff' => $total_staff,
            'total_certificates' => $total_certificates
        ],
        'recent_activity' => $recent_activity
    ]);
}

function getUsers($conn) {
    $search = $_GET['search'] ?? '';
    $type = $_GET['type'] ?? '';
    
    $sql = "SELECT *, CASE WHEN reg_no IS NOT NULL AND reg_no <> '' THEN 'student' ELSE 'staff' END AS user_type FROM users WHERE 1=1";
    
    if (!empty($search)) {
        $search = $conn->real_escape_string($search);
        $sql .= " AND (name LIKE '%$search%' OR email LIKE '%$search%' OR reg_no LIKE '%$search%')";
    }
    
    if (!empty($type)) {
        $type = $conn->real_escape_string($type);
        if ($type === 'student') {
            $sql .= " AND reg_no IS NOT NULL AND reg_no <> ''";
        } elseif ($type === 'staff') {
            $sql .= " AND (reg_no IS NULL OR reg_no = '')";
        }
    }
    
    $sql .= " ORDER BY created_at DESC";
    
    $result = $conn->query($sql);
    $users = [];
    
    while ($row = $result->fetch_assoc()) {
        $users[] = $row;
    }
    
    echo json_encode([
        'success' => true,
        'users' => $users
    ]);
}

function getCertificates($conn) {
    $result = $conn->query("SELECT cl.*, u.name as user_name, CASE WHEN u.reg_no IS NOT NULL AND u.reg_no <> '' THEN 'student' ELSE 'staff' END AS user_type FROM certificate_logs cl JOIN users u ON cl.user_id = u.id ORDER BY cl.generated_at DESC LIMIT 100");
    
    $certificates = [];
    while ($row = $result->fetch_assoc()) {
        $certificates[] = $row;
    }
    
    echo json_encode([
        'success' => true,
        'certificates' => $certificates
    ]);
}

function getActivityLogs($conn) {
    $result = $conn->query("SELECT al.*, u.name as user_name, CASE WHEN u.reg_no IS NOT NULL AND u.reg_no <> '' THEN 'student' ELSE 'staff' END AS user_type FROM activity_logs al JOIN users u ON al.user_id = u.id ORDER BY al.created_at DESC LIMIT 100");
    
    $activities = [];
    while ($row = $result->fetch_assoc()) {
        $activities[] = $row;
    }
    
    echo json_encode([
        'success' => true,
        'activities' => $activities
    ]);
}

function getSessions($conn) {
    $result = $conn->query("SELECT us.*, u.name as user_name FROM user_sessions us LEFT JOIN users u ON us.user_id = u.id WHERE us.status = 'active' AND us.user_type != 'admin' ORDER BY us.last_activity DESC");
    
    $sessions = [];
    while ($row = $result->fetch_assoc()) {
        $sessions[] = $row;
    }
    
    echo json_encode([
        'success' => true,
        'sessions' => $sessions
    ]);
}

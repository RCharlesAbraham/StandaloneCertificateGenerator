<?php
// Create Demo Users Script
require_once 'config.php';

echo "<h1>Create Demo Users</h1>";
echo "<style>body{font-family:Arial;padding:20px;} .success{color:green;} .error{color:red;} .info{color:blue;} table{border-collapse:collapse;margin:15px 0;} th,td{border:1px solid #ddd;padding:10px;text-align:left;} th{background:#67150a;color:white;} code{background:#f4f4f4;padding:2px 6px;border-radius:3px;}</style>";

$conn = getDBConnection();

// Password for all demo users
$demo_password = "Demo123";
$password_hash = password_hash($demo_password, PASSWORD_DEFAULT);

echo "<h2>Creating Demo Users...</h2>";
echo "<p class='info'>All demo users will have password: <code>Demo123</code></p>";

$demo_users = [
    [
        'type' => 'student',
        'reg_no' => 'STU2024001',
        'name' => 'John Doe',
        'designation' => NULL,
        'department' => 'Computer Science',
        'phone_no' => '9876543210',
        'email' => 'john.doe@student.mcc.edu',
        'college' => 'Madras Christian College'
    ],
    [
        'type' => 'student',
        'reg_no' => 'STU2024002',
        'name' => 'Emily Johnson',
        'designation' => NULL,
        'department' => 'Physics',
        'phone_no' => '9876543220',
        'email' => 'emily.johnson@student.mcc.edu',
        'college' => 'Madras Christian College'
    ],
    [
        'type' => 'staff',
        'reg_no' => NULL,
        'name' => 'Dr. Jane Smith',
        'designation' => 'Associate Professor',
        'department' => 'Mathematics',
        'phone_no' => '9876543211',
        'email' => 'jane.smith@staff.mcc.edu',
        'college' => 'Madras Christian College'
    ],
    [
        'type' => 'staff',
        'reg_no' => NULL,
        'name' => 'Prof. Robert Williams',
        'designation' => 'Head of Department',
        'department' => 'Computer Science',
        'phone_no' => '9876543212',
        'email' => 'robert.williams@staff.mcc.edu',
        'college' => 'Madras Christian College'
    ]
];

$created = 0;
$skipped = 0;
$errors = 0;

echo "<table>";
echo "<tr><th>Type</th><th>Name</th><th>Email</th><th>Department</th><th>Status</th></tr>";

foreach ($demo_users as $user) {
    // Check if user already exists
    $check_stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $check_stmt->bind_param("s", $user['email']);
    $check_stmt->execute();
    $result = $check_stmt->get_result();
    
    if ($result->num_rows > 0) {
        echo "<tr>";
        echo "<td><span style='background:#f3e5f5;color:#7b1fa2;padding:3px 8px;border-radius:3px;font-size:12px;'>" . strtoupper($user['type']) . "</span></td>";
        echo "<td>" . $user['name'] . "</td>";
        echo "<td>" . $user['email'] . "</td>";
        echo "<td>" . $user['department'] . "</td>";
        echo "<td class='info'>Already exists</td>";
        echo "</tr>";
        $skipped++;
        $check_stmt->close();
        continue;
    }
    $check_stmt->close();
    
    // Insert new user
    $stmt = $conn->prepare("INSERT INTO users (user_type, reg_no, name, designation, department, phone_no, email, password, college, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'active')");
    
    $stmt->bind_param("sssssssss", 
        $user['type'],
        $user['reg_no'],
        $user['name'],
        $user['designation'],
        $user['department'],
        $user['phone_no'],
        $user['email'],
        $password_hash,
        $user['college']
    );
    
    if ($stmt->execute()) {
        $user_id = $stmt->insert_id;
        
        // Log activity
        $ip_address = $_SERVER['REMOTE_ADDR'];
        $activity_type = 'demo_account_created';
        $activity_description = "Demo {$user['type']} account created: {$user['name']}";
        
        $log_stmt = $conn->prepare("INSERT INTO activity_logs (user_id, user_type, activity_type, activity_description, ip_address) VALUES (?, ?, ?, ?, ?)");
        $log_stmt->bind_param("issss", $user_id, $user['type'], $activity_type, $activity_description, $ip_address);
        $log_stmt->execute();
        $log_stmt->close();
        
        echo "<tr>";
        echo "<td><span style='background:#e3f2fd;color:#1976d2;padding:3px 8px;border-radius:3px;font-size:12px;'>" . strtoupper($user['type']) . "</span></td>";
        echo "<td>" . $user['name'] . "</td>";
        echo "<td>" . $user['email'] . "</td>";
        echo "<td>" . $user['department'] . "</td>";
        echo "<td class='success'>✓ Created</td>";
        echo "</tr>";
        $created++;
    } else {
        echo "<tr>";
        echo "<td>" . strtoupper($user['type']) . "</td>";
        echo "<td>" . $user['name'] . "</td>";
        echo "<td>" . $user['email'] . "</td>";
        echo "<td>" . $user['department'] . "</td>";
        echo "<td class='error'>✗ Failed</td>";
        echo "</tr>";
        $errors++;
    }
    
    $stmt->close();
}

echo "</table>";

echo "<h2>Summary</h2>";
echo "<ul>";
echo "<li class='success'>Created: <strong>$created</strong> users</li>";
echo "<li class='info'>Skipped: <strong>$skipped</strong> users (already exist)</li>";
if ($errors > 0) {
    echo "<li class='error'>Errors: <strong>$errors</strong> users</li>";
}
echo "</ul>";

// Show all current users
echo "<h2>All Demo Users</h2>";
$result = $conn->query("SELECT id, user_type, reg_no, name, designation, department, email, college, status, created_at FROM users ORDER BY id");

if ($result->num_rows > 0) {
    echo "<table>";
    echo "<tr><th>ID</th><th>Type</th><th>Reg/Designation</th><th>Name</th><th>Department</th><th>Email</th><th>Status</th></tr>";
    
    while ($row = $result->fetch_assoc()) {
        $badge_color = $row['user_type'] === 'student' ? '#e3f2fd' : '#f3e5f5';
        $text_color = $row['user_type'] === 'student' ? '#1976d2' : '#7b1fa2';
        $status_color = $row['status'] === 'active' ? 'green' : 'red';
        
        echo "<tr>";
        echo "<td>" . $row['id'] . "</td>";
        echo "<td><span style='background:$badge_color;color:$text_color;padding:3px 8px;border-radius:3px;font-size:12px;'>" . strtoupper($row['user_type']) . "</span></td>";
        echo "<td>" . ($row['reg_no'] ?? $row['designation'] ?? '-') . "</td>";
        echo "<td>" . $row['name'] . "</td>";
        echo "<td>" . $row['department'] . "</td>";
        echo "<td>" . $row['email'] . "</td>";
        echo "<td style='color:$status_color;'>" . ucfirst($row['status']) . "</td>";
        echo "</tr>";
    }
    
    echo "</table>";
} else {
    echo "<p class='info'>No users found in database</p>";
}

echo "<h2>Login Credentials</h2>";
echo "<p class='info'>All demo users use the same password: <code>Demo123</code></p>";

echo "<h3>Student Login Examples:</h3>";
echo "<ul>";
echo "<li>Email: <code>john.doe@student.mcc.edu</code> / Password: <code>Demo123</code></li>";
echo "<li>Email: <code>emily.johnson@student.mcc.edu</code> / Password: <code>Demo123</code></li>";
echo "</ul>";

echo "<h3>Staff Login Examples:</h3>";
echo "<ul>";
echo "<li>Email: <code>jane.smith@staff.mcc.edu</code> / Password: <code>Demo123</code></li>";
echo "<li>Email: <code>robert.williams@staff.mcc.edu</code> / Password: <code>Demo123</code></li>";
echo "</ul>";

echo "<h2>Next Steps</h2>";
echo "<ol>";
echo "<li><a href='login.php'>Test User Login</a> - Login with any demo user</li>";
echo "<li><a href='admin_login.php'>Admin Login</a> - View demo users in admin panel</li>";
echo "<li><a href='admin_panel.php'>Admin Panel</a> - See all users and activity</li>";
echo "</ol>";

$conn->close();
?>

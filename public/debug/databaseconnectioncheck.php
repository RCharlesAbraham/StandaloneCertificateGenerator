<?php
/**
 * Database Connection Check & Diagnostic Tool
 * Tests all database tables, connections, and data integrity
 */

// Include database configuration
require_once __DIR__ . '/../../includes/config.php';

// Set content type
header('Content-Type: text/html; charset=UTF-8');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Database Connection Check</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            padding: 20px;
            min-height: 100vh;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
        }
        .header {
            background: white;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        .header h1 {
            color: #67150a;
            font-size: 32px;
            margin-bottom: 10px;
        }
        .header p {
            color: #666;
            font-size: 14px;
        }
        .section {
            background: white;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        .section h2 {
            color: #333;
            font-size: 20px;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 2px solid #67150a;
        }
        .test-item {
            padding: 15px;
            margin: 10px 0;
            border-radius: 8px;
            border-left: 4px solid #ddd;
        }
        .success {
            background: #e8f5e9;
            border-left-color: #4caf50;
            color: #2e7d32;
        }
        .error {
            background: #ffebee;
            border-left-color: #f44336;
            color: #c62828;
        }
        .warning {
            background: #fff3e0;
            border-left-color: #ff9800;
            color: #e65100;
        }
        .info {
            background: #e3f2fd;
            border-left-color: #2196F3;
            color: #1565c0;
        }
        .test-item strong {
            display: block;
            font-size: 16px;
            margin-bottom: 5px;
        }
        .test-item small {
            font-size: 13px;
            opacity: 0.9;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 15px 0;
        }
        table th {
            background: #67150a;
            color: white;
            padding: 12px;
            text-align: left;
            font-weight: 600;
            font-size: 13px;
        }
        table td {
            padding: 10px 12px;
            border-bottom: 1px solid #e0e0e0;
            font-size: 13px;
        }
        table tr:hover {
            background: #f5f5f5;
        }
        .badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 11px;
            font-weight: 600;
        }
        .badge-success { background: #4caf50; color: white; }
        .badge-danger { background: #f44336; color: white; }
        .badge-warning { background: #ff9800; color: white; }
        .badge-info { background: #2196F3; color: white; }
        .stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin: 20px 0;
        }
        .stat-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.2);
        }
        .stat-card h3 {
            font-size: 14px;
            opacity: 0.9;
            margin-bottom: 8px;
        }
        .stat-card .value {
            font-size: 32px;
            font-weight: bold;
        }
        pre {
            background: #f5f5f5;
            padding: 15px;
            border-radius: 6px;
            overflow-x: auto;
            font-size: 12px;
            border: 1px solid #ddd;
        }
        .code {
            font-family: 'Courier New', monospace;
            background: #f5f5f5;
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 12px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🔍 Database Connection & Diagnostic Tool</h1>
            <p>Comprehensive database health check for Certificate Generator System</p>
            <p><strong>Timestamp:</strong> <?php echo date('Y-m-d H:i:s'); ?> | <strong>PHP Version:</strong> <?php echo phpversion(); ?></p>
        </div>

        <?php
        $allTestsPassed = true;
        $totalTests = 0;
        $passedTests = 0;

        // Test 1: Database Connection
        echo '<div class="section">';
        echo '<h2>1️⃣ Database Connection Test</h2>';
        
        try {
            $conn = getDBConnection();
            $totalTests++;
            
            if ($conn && !$conn->connect_error) {
                $passedTests++;
                echo '<div class="test-item success">';
                echo '<strong>✅ Database Connection Successful</strong>';
                echo '<small>Connected to: <span class="code">' . DB_HOST . '</span> as <span class="code">' . DB_USER . '</span></small>';
                echo '</div>';
                
                // Get server info
                echo '<div class="test-item info">';
                echo '<strong>📊 Database Server Information</strong>';
                echo '<small>';
                echo 'Server: ' . $conn->server_info . '<br>';
                echo 'Host Info: ' . $conn->host_info . '<br>';
                echo 'Protocol: ' . $conn->protocol_version . '<br>';
                echo 'Character Set: ' . $conn->character_set_name();
                echo '</small>';
                echo '</div>';
            } else {
                $allTestsPassed = false;
                echo '<div class="test-item error">';
                echo '<strong>❌ Database Connection Failed</strong>';
                echo '<small>Error: ' . ($conn ? $conn->connect_error : 'Connection object not created') . '</small>';
                echo '</div>';
            }
        } catch (Exception $e) {
            $allTestsPassed = false;
            echo '<div class="test-item error">';
            echo '<strong>❌ Connection Exception</strong>';
            echo '<small>' . $e->getMessage() . '</small>';
            echo '</div>';
        }
        echo '</div>';

        // Test 2: Database Selection
        echo '<div class="section">';
        echo '<h2>2️⃣ Database Selection Test</h2>';
        
        if ($conn && !$conn->connect_error) {
            $totalTests++;
            $result = $conn->select_db(DB_NAME);
            if ($result) {
                $passedTests++;
                echo '<div class="test-item success">';
                echo '<strong>✅ Database Selected Successfully</strong>';
                echo '<small>Using database: <span class="code">' . DB_NAME . '</span></small>';
                echo '</div>';
            } else {
                $allTestsPassed = false;
                echo '<div class="test-item error">';
                echo '<strong>❌ Database Selection Failed</strong>';
                echo '<small>Error: ' . $conn->error . '</small>';
                echo '</div>';
            }
        }
        echo '</div>';

        // Test 3: Table Structure Check
        echo '<div class="section">';
        echo '<h2>3️⃣ Table Structure Verification</h2>';
        
        $requiredTables = [
            'users' => 'User accounts (students and staff)',
            'admins' => 'Administrator accounts',
            'certificate_logs' => 'Certificate generation history',
            'activity_logs' => 'User activity tracking',
            'admin_logs' => 'Admin activity tracking',
            'user_sessions' => 'Active user sessions'
        ];

        if ($conn && !$conn->connect_error) {
            echo '<table>';
            echo '<tr><th>Table Name</th><th>Description</th><th>Status</th><th>Row Count</th></tr>';
            
            foreach ($requiredTables as $table => $description) {
                $totalTests++;
                $result = $conn->query("SHOW TABLES LIKE '$table'");
                
                if ($result && $result->num_rows > 0) {
                    $passedTests++;
                    
                    // Get row count
                    $countResult = $conn->query("SELECT COUNT(*) as count FROM `$table`");
                    $count = $countResult ? $countResult->fetch_assoc()['count'] : 0;
                    
                    echo '<tr>';
                    echo '<td><strong>' . $table . '</strong></td>';
                    echo '<td>' . $description . '</td>';
                    echo '<td><span class="badge badge-success">✅ EXISTS</span></td>';
                    echo '<td>' . $count . ' rows</td>';
                    echo '</tr>';
                } else {
                    $allTestsPassed = false;
                    echo '<tr>';
                    echo '<td><strong>' . $table . '</strong></td>';
                    echo '<td>' . $description . '</td>';
                    echo '<td><span class="badge badge-danger">❌ MISSING</span></td>';
                    echo '<td>-</td>';
                    echo '</tr>';
                }
            }
            echo '</table>';
        }
        echo '</div>';

        // Test 4: Users Table Details
        echo '<div class="section">';
        echo '<h2>4️⃣ Users Table Analysis</h2>';
        
        if ($conn && !$conn->connect_error) {
            $totalTests++;
            $result = $conn->query("SELECT * FROM users LIMIT 1");
            
            if ($result) {
                $passedTests++;
                
                // Check for new columns
                $fieldsResult = $conn->query("DESCRIBE users");
                $fields = [];
                while ($row = $fieldsResult->fetch_assoc()) {
                    $fields[] = $row['Field'];
                }
                
                $requiredFields = ['id', 'user_type', 'stream', 'level', 'reg_no', 'program_id', 'name', 'department', 'email', 'password'];
                
                echo '<div class="test-item info">';
                echo '<strong>📋 Table Columns:</strong><br>';
                echo '<small>' . implode(', ', $fields) . '</small>';
                echo '</div>';
                
                // Check if new fields exist
                $hasStream = in_array('stream', $fields);
                $hasLevel = in_array('level', $fields);
                $hasProgramId = in_array('program_id', $fields);
                
                if ($hasStream && $hasLevel && $hasProgramId) {
                    echo '<div class="test-item success">';
                    echo '<strong>✅ New Registration Fields Present</strong>';
                    echo '<small>Stream, Level, and Program ID columns are available</small>';
                    echo '</div>';
                } else {
                    echo '<div class="test-item warning">';
                    echo '<strong>⚠️ Missing New Fields</strong>';
                    echo '<small>Need to run migration: <span class="code">db/migration_add_stream_level.sql</span></small>';
                    echo '</div>';
                }
                
                // Get user statistics
                $statsQuery = "SELECT 
                    COUNT(*) as total_users,
                    SUM(CASE WHEN user_type = 'student' THEN 1 ELSE 0 END) as students,
                    SUM(CASE WHEN user_type = 'staff' THEN 1 ELSE 0 END) as staff,
                    SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) as active_users
                FROM users";
                
                $statsResult = $conn->query($statsQuery);
                if ($statsResult) {
                    $stats = $statsResult->fetch_assoc();
                    
                    echo '<div class="stats">';
                    echo '<div class="stat-card">';
                    echo '<h3>Total Users</h3>';
                    echo '<div class="value">' . $stats['total_users'] . '</div>';
                    echo '</div>';
                    
                    echo '<div class="stat-card" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">';
                    echo '<h3>Students</h3>';
                    echo '<div class="value">' . $stats['students'] . '</div>';
                    echo '</div>';
                    
                    echo '<div class="stat-card" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);">';
                    echo '<h3>Staff</h3>';
                    echo '<div class="value">' . $stats['staff'] . '</div>';
                    echo '</div>';
                    
                    echo '<div class="stat-card" style="background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);">';
                    echo '<h3>Active Users</h3>';
                    echo '<div class="value">' . $stats['active_users'] . '</div>';
                    echo '</div>';
                    echo '</div>';
                }
                
                // Show recent users
                $recentUsers = $conn->query("SELECT id, user_type, name, email, created_at FROM users ORDER BY created_at DESC LIMIT 5");
                if ($recentUsers && $recentUsers->num_rows > 0) {
                    echo '<h3 style="margin-top: 20px; color: #333;">Recent Users:</h3>';
                    echo '<table>';
                    echo '<tr><th>ID</th><th>Type</th><th>Name</th><th>Email</th><th>Created</th></tr>';
                    while ($user = $recentUsers->fetch_assoc()) {
                        echo '<tr>';
                        echo '<td>' . $user['id'] . '</td>';
                        echo '<td><span class="badge badge-info">' . strtoupper($user['user_type']) . '</span></td>';
                        echo '<td>' . htmlspecialchars($user['name']) . '</td>';
                        echo '<td>' . htmlspecialchars($user['email']) . '</td>';
                        echo '<td>' . $user['created_at'] . '</td>';
                        echo '</tr>';
                    }
                    echo '</table>';
                }
            } else {
                $allTestsPassed = false;
                echo '<div class="test-item error">';
                echo '<strong>❌ Cannot Query Users Table</strong>';
                echo '<small>Error: ' . $conn->error . '</small>';
                echo '</div>';
            }
        }
        echo '</div>';

        // Test 5: Admin Table Check
        echo '<div class="section">';
        echo '<h2>5️⃣ Admin Accounts Check</h2>';
        
        if ($conn && !$conn->connect_error) {
            $totalTests++;
            $adminResult = $conn->query("SELECT id, username, created_at FROM admins");
            
            if ($adminResult) {
                $passedTests++;
                
                if ($adminResult->num_rows > 0) {
                    echo '<div class="test-item success">';
                    echo '<strong>✅ Admin Accounts Found (' . $adminResult->num_rows . ')</strong>';
                    echo '</div>';
                    
                    echo '<table>';
                    echo '<tr><th>ID</th><th>Username</th><th>Created</th></tr>';
                    while ($admin = $adminResult->fetch_assoc()) {
                        echo '<tr>';
                        echo '<td>' . $admin['id'] . '</td>';
                        echo '<td><strong>' . htmlspecialchars($admin['username']) . '</strong></td>';
                        echo '<td>' . $admin['created_at'] . '</td>';
                        echo '</tr>';
                    }
                    echo '</table>';
                } else {
                    echo '<div class="test-item warning">';
                    echo '<strong>⚠️ No Admin Accounts Found</strong>';
                    echo '<small>Run database schema to create default admin (Admin@MCC / Admin123)</small>';
                    echo '</div>';
                }
            }
        }
        echo '</div>';

        // Test 6: Certificate Logs
        echo '<div class="section">';
        echo '<h2>6️⃣ Certificate Generation Logs</h2>';
        
        if ($conn && !$conn->connect_error) {
            $totalTests++;
            $certStats = $conn->query("SELECT 
                COUNT(*) as total_certs,
                SUM(CASE WHEN generation_type = 'single' THEN 1 ELSE 0 END) as single_certs,
                SUM(CASE WHEN generation_type = 'bulk' THEN 1 ELSE 0 END) as bulk_certs,
                SUM(bulk_count) as total_generated
            FROM certificate_logs");
            
            if ($certStats) {
                $passedTests++;
                $certData = $certStats->fetch_assoc();
                
                echo '<div class="stats">';
                echo '<div class="stat-card" style="background: linear-gradient(135deg, #fa709a 0%, #fee140 100%);">';
                echo '<h3>Total Log Entries</h3>';
                echo '<div class="value">' . $certData['total_certs'] . '</div>';
                echo '</div>';
                
                echo '<div class="stat-card" style="background: linear-gradient(135deg, #30cfd0 0%, #330867 100%);">';
                echo '<h3>Single Generated</h3>';
                echo '<div class="value">' . ($certData['single_certs'] ?? 0) . '</div>';
                echo '</div>';
                
                echo '<div class="stat-card" style="background: linear-gradient(135deg, #a8edea 0%, #fed6e3 100%);">';
                echo '<h3>Bulk Sessions</h3>';
                echo '<div class="value">' . ($certData['bulk_certs'] ?? 0) . '</div>';
                echo '</div>';
                
                echo '<div class="stat-card" style="background: linear-gradient(135deg, #ff9a56 0%, #ff6a88 100%);">';
                echo '<h3>Total Certificates</h3>';
                echo '<div class="value">' . ($certData['total_generated'] ?? 0) . '</div>';
                echo '</div>';
                echo '</div>';
            }
        }
        echo '</div>';

        // Test 7: Session Management
        echo '<div class="section">';
        echo '<h2>7️⃣ Active Sessions Check</h2>';
        
        if ($conn && !$conn->connect_error) {
            $totalTests++;
            $sessionResult = $conn->query("SELECT 
                COUNT(*) as total_sessions,
                SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) as active_sessions,
                SUM(CASE WHEN status = 'logged_out' THEN 1 ELSE 0 END) as logged_out
            FROM user_sessions");
            
            if ($sessionResult) {
                $passedTests++;
                $sessionData = $sessionResult->fetch_assoc();
                
                echo '<div class="test-item info">';
                echo '<strong>📊 Session Statistics</strong><br>';
                echo '<small>';
                echo 'Total Sessions: ' . $sessionData['total_sessions'] . '<br>';
                echo 'Active: <strong>' . $sessionData['active_sessions'] . '</strong><br>';
                echo 'Logged Out: ' . $sessionData['logged_out'];
                echo '</small>';
                echo '</div>';
            }
        }
        echo '</div>';

        // Test 8: Database Configuration Display
        echo '<div class="section">';
        echo '<h2>8️⃣ Configuration Summary</h2>';
        
        echo '<div class="test-item info">';
        echo '<strong>🔧 Database Configuration:</strong>';
        echo '<pre>';
        echo 'DB_HOST: ' . DB_HOST . "\n";
        echo 'DB_USER: ' . DB_USER . "\n";
        echo 'DB_NAME: ' . DB_NAME . "\n";
        echo 'DB_PASS: ' . str_repeat('*', strlen(DB_PASS)) . ' (' . strlen(DB_PASS) . ' characters)';
        echo '</pre>';
        echo '</div>';
        
        echo '</div>';

        // Final Summary
        echo '<div class="section">';
        echo '<h2>✅ Test Summary</h2>';
        
        $percentage = $totalTests > 0 ? round(($passedTests / $totalTests) * 100) : 0;
        
        if ($percentage === 100) {
            echo '<div class="test-item success">';
            echo '<strong>🎉 ALL TESTS PASSED!</strong>';
            echo '<small>Database is fully configured and operational. ' . $passedTests . '/' . $totalTests . ' tests passed.</small>';
            echo '</div>';
        } elseif ($percentage >= 70) {
            echo '<div class="test-item warning">';
            echo '<strong>⚠️ Most Tests Passed</strong>';
            echo '<small>' . $passedTests . '/' . $totalTests . ' tests passed (' . $percentage . '%). Review warnings above.</small>';
            echo '</div>';
        } else {
            echo '<div class="test-item error">';
            echo '<strong>❌ Critical Issues Found</strong>';
            echo '<small>Only ' . $passedTests . '/' . $totalTests . ' tests passed (' . $percentage . '%). Please fix errors above.</small>';
            echo '</div>';
        }
        
        echo '</div>';

        // Close connection
        if ($conn && !$conn->connect_error) {
            $conn->close();
        }
        ?>

        <div class="section">
            <h2>🔗 Quick Links</h2>
            <div style="display: flex; gap: 10px; flex-wrap: wrap;">
                <a href="../register.php" style="padding: 10px 20px; background: #67150a; color: white; text-decoration: none; border-radius: 6px; font-weight: bold;">📝 Register</a>
                <a href="../login.php" style="padding: 10px 20px; background: #2196F3; color: white; text-decoration: none; border-radius: 6px; font-weight: bold;">🔐 Login</a>
                <a href="../test_css.php" style="padding: 10px 20px; background: #4caf50; color: white; text-decoration: none; border-radius: 6px; font-weight: bold;">🎨 CSS Test</a>
                <a href="../css_diagnostic.html" style="padding: 10px 20px; background: #ff9800; color: white; text-decoration: none; border-radius: 6px; font-weight: bold;">🔍 CSS Diagnostic</a>
                <a href="../admin/login.php" style="padding: 10px 20px; background: #9c27b0; color: white; text-decoration: none; border-radius: 6px; font-weight: bold;">👑 Admin</a>
            </div>
        </div>
    </div>
</body>
</html>
        
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registration System Test</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 1200px;
            margin: 20px auto;
            padding: 20px;
            background: #f5f5f5;
        }
        .test-container {
            background: white;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        h1 {
            color: #333;
        }
        h2 {
            color: #666;
            border-bottom: 2px solid #4CAF50;
            padding-bottom: 10px;
        }
        .test-section {
            margin: 20px 0;
        }
        .test-case {
            background: #f9f9f9;
            padding: 15px;
            margin: 10px 0;
            border-left: 4px solid #2196F3;
        }
        .success {
            border-left-color: #4CAF50;
            background: #e8f5e9;
        }
        .error {
            border-left-color: #f44336;
            background: #ffebee;
        }
        .info {
            background: #e3f2fd;
            padding: 10px;
            margin: 10px 0;
            border-radius: 4px;
        }
        button {
            background: #4CAF50;
            color: white;
            border: none;
            padding: 10px 20px;
            cursor: pointer;
            border-radius: 4px;
            margin: 5px;
        }
        button:hover {
            background: #45a049;
        }
        .db-table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        .db-table th, .db-table td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        .db-table th {
            background: #4CAF50;
            color: white;
        }
        .department-list {
            columns: 2;
            margin: 10px 0;
        }
        .department-list li {
            margin: 5px 0;
        }
    </style>
</head>
<body>
    <div class="test-container">
        <h1>🎓 Registration System Test Suite</h1>
        <p><strong>Date:</strong> <?php echo date('Y-m-d H:i:s'); ?></p>
        
        <?php
        // Include database configuration
        require_once __DIR__ . '/includes/config.php';
        
        // Test database connection
        echo "<h2>1. Database Connection Test</h2>";
        try {
            $conn = getDBConnection();
            echo '<div class="test-case success">✅ Database connection successful</div>';
        } catch (Exception $e) {
            echo '<div class="test-case error">❌ Database connection failed: ' . $e->getMessage() . '</div>';
            exit;
        }
        
        // Test table structure
        echo "<h2>2. Users Table Structure Test</h2>";
        $result = $conn->query("DESCRIBE users");
        if ($result) {
            echo '<div class="test-case success">✅ Users table exists</div>';
            echo '<table class="db-table">';
            echo '<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th></tr>';
            while ($row = $result->fetch_assoc()) {
                $highlight = in_array($row['Field'], ['stream', 'level', 'program_id']) ? ' style="background: #fff9c4;"' : '';
                echo "<tr{$highlight}>";
                echo "<td>{$row['Field']}</td>";
                echo "<td>{$row['Type']}</td>";
                echo "<td>{$row['Null']}</td>";
                echo "<td>{$row['Key']}</td>";
                echo "<td>{$row['Default']}</td>";
                echo "</tr>";
            }
            echo '</table>';
            
            // Check if new columns exist
            $conn->query("SELECT stream, level, program_id FROM users LIMIT 1");
            if ($conn->error) {
                echo '<div class="test-case error">❌ New columns (stream, level, program_id) not found. Please run migration script.</div>';
                echo '<div class="info"><strong>To migrate:</strong> Run <code>db/migration_add_stream_level.sql</code> in your MySQL database.</div>';
            } else {
                echo '<div class="test-case success">✅ All new columns present (stream, level, program_id)</div>';
            }
        } else {
            echo '<div class="test-case error">❌ Could not describe users table</div>';
        }
        
        // Department validation
        echo "<h2>3. Department Lists</h2>";
        echo '<div class="test-section">';
        
        $departments = [
            'Aided - UG' => [
                'English Language & Literature', 'Tamil Literature', 'History', 
                'Political Science', 'Economics', 'Philosophy', 'Commerce (General)',
                'Mathematics', 'Statistics', 'Physics', 'Chemistry', 
                'Plant Biology & Plant Biotechnology', 'Zoology'
            ],
            'Aided - PG' => [
                'English Language & Literature', 'Tamil Literature', 'History',
                'Political Science', 'Public Administration', 'Economics', 'Philosophy',
                'Commerce (M.Com)', 'MSW (Community Development / Medical & Psychiatry)',
                'Mathematics', 'Statistics', 'Physics', 'Chemistry',
                'Plant Biology & Plant Biotechnology', 'Zoology'
            ],
            'SFS - UG' => [
                'English Language & Literature', 'Journalism', 
                'History (Vocational – Archaeology & Museology)', 'Social Work (BSW)',
                'Commerce (General)', 'Commerce (Accounting & Finance)', 
                'Commerce (Professional Accounting)', 'Business Administration (BBA)',
                'Computer Applications (BCA)', 'Geography, Tourism & Travel Management',
                'Hospitality & Tourism', 'Mathematics', 'Physics', 'Microbiology',
                'Computer Science', 'Visual Communication', 
                'Physical Education, Health Education & Sports', 'Psychology'
            ],
            'SFS - PG' => [
                'M.A. Communication', 'MSW – Human Resource Management',
                'M.Com – Computer Oriented Business Applications', 'M.Sc. Chemistry',
                'M.Sc. Applied Microbiology', 'MCA – Computer Applications', 
                'M.Sc. Data Science'
            ]
        ];
        
        foreach ($departments as $category => $depts) {
            echo "<h3>{$category} (" . count($depts) . " departments)</h3>";
            echo '<ul class="department-list">';
            foreach ($depts as $dept) {
                echo "<li>{$dept}</li>";
            }
            echo '</ul>';
        }
        echo '</div>';
        
        // Test form validation endpoints
        echo "<h2>4. Registration Endpoint Test</h2>";
        echo '<div class="info">';
        echo '<strong>Endpoint:</strong> public/actions/register_process.php<br>';
        echo '<strong>Method:</strong> POST<br>';
        echo '<strong>Required fields for students:</strong> user_type, stream, level, reg_no, name, department, phone_no, email, password, confirm_password, college_type';
        echo '</div>';
        
        // Show sample test data
        echo '<div class="test-case">';
        echo '<strong>Sample Test Data:</strong><br>';
        echo '<pre>';
        echo json_encode([
            'user_type' => 'student',
            'stream' => 'aided',
            'level' => 'ug',
            'reg_no' => 'TEST2024001',
            'name' => 'Test Student',
            'department' => 'Computer Science',
            'phone_no' => '9876543210',
            'email' => 'test.student@example.com',
            'password' => 'Test123',
            'confirm_password' => 'Test123',
            'college_type' => 'mcc'
        ], JSON_PRETTY_PRINT);
        echo '</pre>';
        echo '</div>';
        
        // Show existing users
        echo "<h2>5. Existing Users</h2>";
        $result = $conn->query("SELECT id, user_type, stream, level, reg_no, program_id, name, department, email, created_at FROM users ORDER BY created_at DESC LIMIT 10");
        if ($result && $result->num_rows > 0) {
            echo '<table class="db-table">';
            echo '<tr><th>ID</th><th>Type</th><th>Stream</th><th>Level</th><th>Reg No</th><th>Program ID</th><th>Name</th><th>Department</th><th>Email</th><th>Created</th></tr>';
            while ($row = $result->fetch_assoc()) {
                echo "<tr>";
                echo "<td>{$row['id']}</td>";
                echo "<td>{$row['user_type']}</td>";
                echo "<td>" . strtoupper($row['stream'] ?? 'N/A') . "</td>";
                echo "<td>" . strtoupper($row['level'] ?? 'N/A') . "</td>";
                echo "<td>{$row['reg_no']}</td>";
                echo "<td><strong>{$row['program_id']}</strong></td>";
                echo "<td>{$row['name']}</td>";
                echo "<td>{$row['department']}</td>";
                echo "<td>{$row['email']}</td>";
                echo "<td>{$row['created_at']}</td>";
                echo "</tr>";
            }
            echo '</table>';
        } else {
            echo '<div class="test-case">No users found in database</div>';
        }
        
        // Quick links
        echo "<h2>6. Quick Links</h2>";
        echo '<div class="test-section">';
        echo '<button onclick="window.location.href=\'public/register.php\'">Open Registration Page</button>';
        echo '<button onclick="window.location.href=\'public/login.php\'">Open Login Page</button>';
        echo '<button onclick="window.location.href=\'public/admin/login.php\'">Open Admin Login</button>';
        echo '</div>';
        
        $conn->close();
        ?>
        
        <h2>7. Frontend Test Checklist</h2>
        <div class="test-section">
            <div class="test-case">
                <input type="checkbox" id="test1"> 
                <label for="test1">User type radio buttons (Student/Staff) work correctly</label>
            </div>
            <div class="test-case">
                <input type="checkbox" id="test2"> 
                <label for="test2">Stream radio buttons (Aided/SFS) appear for students</label>
            </div>
            <div class="test-case">
                <input type="checkbox" id="test3"> 
                <label for="test3">Level radio buttons (UG/PG) appear for students</label>
            </div>
            <div class="test-case">
                <input type="checkbox" id="test4"> 
                <label for="test4">Department dropdown updates when stream/level changes</label>
            </div>
            <div class="test-case">
                <input type="checkbox" id="test5"> 
                <label for="test5">Correct departments shown for Aided-UG</label>
            </div>
            <div class="test-case">
                <input type="checkbox" id="test6"> 
                <label for="test6">Correct departments shown for Aided-PG</label>
            </div>
            <div class="test-case">
                <input type="checkbox" id="test7"> 
                <label for="test7">Correct departments shown for SFS-UG</label>
            </div>
            <div class="test-case">
                <input type="checkbox" id="test8"> 
                <label for="test8">Correct departments shown for SFS-PG</label>
            </div>
            <div class="test-case">
                <input type="checkbox" id="test9"> 
                <label for="test9">Staff users see text input for department (not dropdown)</label>
            </div>
            <div class="test-case">
                <input type="checkbox" id="test10"> 
                <label for="test10">Form validation works (required fields, email format, password match)</label>
            </div>
            <div class="test-case">
                <input type="checkbox" id="test11"> 
                <label for="test11">Registration succeeds and program_id is generated</label>
            </div>
            <div class="test-case">
                <input type="checkbox" id="test12"> 
                <label for="test12">User can login after registration</label>
            </div>
        </div>
    </div>
</body>
</html>

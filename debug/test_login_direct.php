<?php
// Direct login test - simulates the login process
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>Direct Login Process Test</h2>";

// Clean output buffer
if (ob_get_level()) ob_end_clean();
ob_start();

require_once __DIR__ . '/includes/config.php';

echo "<h3>Step 1: Database Connection</h3>";
try {
    $conn = getDBConnection();
    echo "✓ Connected to database<br>";
} catch (Exception $e) {
    echo "✗ Connection failed: " . $e->getMessage() . "<br>";
    exit;
}

// Test with a specific email (you'll need to provide one)
echo "<h3>Step 2: User Lookup</h3>";
echo "<form method='POST'>";
echo "Email: <input type='email' name='test_email' required><br>";
echo "Password: <input type='text' name='test_password' required><br>";
echo "<button type='submit'>Test Login</button>";
echo "</form><br>";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['test_email'];
    $password = $_POST['test_password'];
    
    echo "Testing with email: <strong>$email</strong><br>";
    
    $stmt = $conn->prepare("SELECT id, name, email, password, status, reg_no FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        echo "✗ User not found<br>";
    } else {
        $user = $result->fetch_assoc();
        echo "✓ User found: " . $user['name'] . "<br>";
        echo "Status: " . $user['status'] . "<br>";
        echo "Reg No: " . ($user['reg_no'] ?: 'N/A') . "<br>";
        
        $user_type = (!empty($user['reg_no'])) ? 'student' : 'staff';
        echo "Derived User Type: " . $user_type . "<br>";
        
        echo "<h3>Step 3: Password Verification</h3>";
        echo "Password hash in DB: " . substr($user['password'], 0, 20) . "...<br>";
        echo "Testing password: <strong>$password</strong><br>";
        
        if (password_verify($password, $user['password'])) {
            echo "✓ Password matches!<br>";
            
            echo "<h3>Step 4: Login would succeed</h3>";
            echo "<pre>";
            echo "Response would be:\n";
            echo json_encode([
                'success' => true,
                'message' => 'Login successful',
                'user_id' => $user['id'],
                'user_type' => $user_type,
                'name' => $user['name'],
                'email' => $user['email']
            ], JSON_PRETTY_PRINT);
            echo "</pre>";
        } else {
            echo "✗ Password does not match<br>";
            echo "Trying to create a test hash...<br>";
            $test_hash = password_hash($password, PASSWORD_DEFAULT);
            echo "If you were to register with this password, the hash would be: " . $test_hash . "<br>";
        }
    }
    $stmt->close();
}

echo "<h3>All Users in Database</h3>";
$users = $conn->query("SELECT id, name, email, status, reg_no FROM users");
if ($users->num_rows > 0) {
    echo "<table border='1' cellpadding='5'>";
    echo "<tr><th>ID</th><th>Name</th><th>Email</th><th>Status</th><th>Reg No</th></tr>";
    while ($u = $users->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $u['id'] . "</td>";
        echo "<td>" . $u['name'] . "</td>";
        echo "<td>" . $u['email'] . "</td>";
        echo "<td>" . $u['status'] . "</td>";
        echo "<td>" . ($u['reg_no'] ?: 'N/A') . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "No users found.";
}

$conn->close();
?>

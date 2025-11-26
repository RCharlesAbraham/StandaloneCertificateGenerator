<?php
/**
 * Admin Setup Page - For Demo/Initial Setup Only
 * Creates an admin account in the database
 * DELETE THIS FILE AFTER CREATING YOUR ADMIN ACCOUNT
 */

require_once __DIR__ . '/../../includes/config.php';

$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    // Validation
    if (empty($username) || empty($email) || empty($password)) {
        $message = 'All fields are required.';
        $messageType = 'error';
    } elseif (strlen($password) < 6) {
        $message = 'Password must be at least 6 characters.';
        $messageType = 'error';
    } elseif ($password !== $confirm_password) {
        $message = 'Passwords do not match.';
        $messageType = 'error';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = 'Invalid email format.';
        $messageType = 'error';
    } else {
        try {
            $conn = getDBConnection();
            
            // Check if admin table exists, create if not
            $conn->query("CREATE TABLE IF NOT EXISTS admins (
                id INT AUTO_INCREMENT PRIMARY KEY,
                username VARCHAR(50) UNIQUE NOT NULL,
                email VARCHAR(100) UNIQUE NOT NULL,
                password VARCHAR(255) NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                last_login TIMESTAMP NULL
            )");
            
            // Check if username already exists
            $stmt = $conn->prepare("SELECT id FROM admins WHERE username = ? OR email = ?");
            $stmt->bind_param("ss", $username, $email);
            $stmt->execute();
            
            if ($stmt->get_result()->num_rows > 0) {
                $message = 'Username or email already exists.';
                $messageType = 'error';
            } else {
                // Hash password and insert
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                
                $stmt = $conn->prepare("INSERT INTO admins (username, email, password) VALUES (?, ?, ?)");
                $stmt->bind_param("sss", $username, $email, $hashedPassword);
                
                if ($stmt->execute()) {
                    $message = 'Admin account created successfully! You can now login at <a href="login.php">Admin Login</a>';
                    $messageType = 'success';
                } else {
                    $message = 'Error creating admin: ' . $conn->error;
                    $messageType = 'error';
                }
            }
            
            $stmt->close();
            $conn->close();
        } catch (Exception $e) {
            $message = 'Database error: ' . $e->getMessage();
            $messageType = 'error';
        }
    }
}

// Check existing admins
$adminCount = 0;
try {
    $conn = getDBConnection();
    $result = $conn->query("SELECT COUNT(*) as count FROM admins");
    if ($result) {
        $adminCount = $result->fetch_assoc()['count'];
    }
    $conn->close();
} catch (Exception $e) {
    // Table might not exist yet
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Setup - Certificate Generator</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .setup-container {
            background: white;
            border-radius: 16px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
            padding: 40px;
            width: 100%;
            max-width: 500px;
        }
        .setup-header {
            text-align: center;
            margin-bottom: 30px;
        }
        .setup-header h1 {
            color: #333;
            font-size: 28px;
            margin-bottom: 8px;
        }
        .setup-header p {
            color: #666;
            font-size: 14px;
        }
        .warning-box {
            background: #fff3cd;
            border: 1px solid #ffc107;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 20px;
            font-size: 13px;
            color: #856404;
        }
        .warning-box strong {
            display: block;
            margin-bottom: 5px;
        }
        .info-box {
            background: #e3f2fd;
            border: 1px solid #2196f3;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 20px;
            font-size: 13px;
            color: #1565c0;
        }
        .form-group {
            margin-bottom: 20px;
        }
        .form-group label {
            display: block;
            font-weight: 600;
            margin-bottom: 8px;
            color: #333;
        }
        .form-group input {
            width: 100%;
            padding: 12px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 14px;
            transition: all 0.3s;
        }
        .form-group input:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }
        .submit-btn {
            width: 100%;
            padding: 14px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: transform 0.2s;
        }
        .submit-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(102, 126, 234, 0.4);
        }
        .alert {
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        .alert-success {
            background: #d4edda;
            color: #155724;
            border-left: 4px solid #28a745;
        }
        .alert-success a {
            color: #155724;
            font-weight: bold;
        }
        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border-left: 4px solid #dc3545;
        }
        .links {
            margin-top: 20px;
            text-align: center;
            font-size: 13px;
        }
        .links a {
            color: #667eea;
            text-decoration: none;
            margin: 0 10px;
        }
        .links a:hover {
            text-decoration: underline;
        }
        .admin-count {
            text-align: center;
            padding: 10px;
            background: #f8f9fa;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 14px;
            color: #666;
        }
    </style>
</head>
<body>
    <div class="setup-container">
        <div class="setup-header">
            <h1>🔧 Admin Setup</h1>
            <p>Create your admin account for Certificate Generator</p>
        </div>

        <div class="warning-box">
            <strong>⚠️ Security Warning</strong>
            Delete this file (setup.php) after creating your admin account to prevent unauthorized access!
        </div>

        <?php if ($adminCount > 0): ?>
        <div class="info-box">
            <strong>ℹ️ Info:</strong> There are already <?php echo $adminCount; ?> admin account(s) in the database.
        </div>
        <?php endif; ?>

        <?php if ($message): ?>
        <div class="alert alert-<?php echo $messageType; ?>">
            <?php echo $message; ?>
        </div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <label for="username">Admin Username</label>
                <input type="text" id="username" name="username" placeholder="e.g., Admin@MCC" required 
                       value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>">
            </div>

            <div class="form-group">
                <label for="email">Email Address</label>
                <input type="email" id="email" name="email" placeholder="admin@college.edu" required
                       value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" placeholder="Minimum 6 characters" required>
            </div>

            <div class="form-group">
                <label for="confirm_password">Confirm Password</label>
                <input type="password" id="confirm_password" name="confirm_password" placeholder="Re-enter password" required>
            </div>

            <button type="submit" class="submit-btn">Create Admin Account</button>
        </form>

        <div class="links">
            <a href="login.php">← Admin Login</a>
            <a href="../login.php">User Login</a>
        </div>
    </div>
</body>
</html>

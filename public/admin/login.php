<?php
// Check if already logged in as admin
require_once __DIR__ . '/../../includes/config.php';
if (isset($_SESSION['admin_id']) && $_SESSION['admin_id']) {
    header('Location: admin_panel.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - Certificate Generator</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .login-container {
            background: white;
            border-radius: 16px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
            padding: 40px;
            width: 100%;
            max-width: 420px;
        }
        .login-header {
            text-align: center;
            margin-bottom: 30px;
        }
        .login-header h1 {
            color: #333;
            font-size: 26px;
            margin-bottom: 8px;
        }
        .login-header p {
            color: #666;
            font-size: 14px;
        }
        .form-group {
            margin-bottom: 20px;
        }
        .form-group label {
            display: block;
            font-weight: 600;
            margin-bottom: 8px;
            color: #333;
            font-size: 14px;
        }
        .form-group input {
            width: 100%;
            padding: 12px 14px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 14px;
            transition: all 0.3s;
        }
        .form-group input:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.15);
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
            transition: all 0.3s;
        }
        .submit-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(102, 126, 234, 0.4);
        }
        .submit-btn:disabled {
            opacity: 0.7;
            cursor: not-allowed;
            transform: none;
        }
        #alertBox { margin-bottom: 20px; }
        .alert {
            padding: 12px 15px;
            border-radius: 8px;
            font-size: 14px;
        }
        .alert-success {
            background: #d4edda;
            color: #155724;
            border-left: 4px solid #28a745;
        }
        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border-left: 4px solid #dc3545;
        }
        .links {
            margin-top: 25px;
            text-align: center;
            font-size: 13px;
            color: #666;
        }
        .links a {
            color: #667eea;
            text-decoration: none;
        }
        .links a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-header">
            <h1>🔐 Admin Login</h1>
            <p>Certificate Generator - Admin Panel</p>
        </div>

        <div id="alertBox"></div>

        <form id="loginForm">
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" placeholder="Enter admin username" required autofocus>
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" placeholder="Enter password" required>
            </div>

            <button type="submit" class="submit-btn" id="submitBtn">Sign In</button>
        </form>

        <div class="links">
            <a href="../login.php">← Back to User Login</a>
        </div>
    </div>

    <script>
        const form = document.getElementById('loginForm');
        const alertBox = document.getElementById('alertBox');
        const submitBtn = document.getElementById('submitBtn');

        form.addEventListener('submit', async (e) => {
            e.preventDefault();
            alertBox.innerHTML = '';
            submitBtn.disabled = true;
            submitBtn.textContent = 'Signing in...';

            const formData = new FormData(form);

            try {
                const response = await fetch('admin_login_process.php', {
                    method: 'POST',
                    body: formData
                });

                const data = await response.json();

                if (data.success) {
                    alertBox.innerHTML = '<div class="alert alert-success">✓ ' + data.message + '</div>';
                    setTimeout(() => {
                        window.location.href = 'admin_panel.php';
                    }, 1000);
                } else {
                    alertBox.innerHTML = '<div class="alert alert-error">✗ ' + data.message + '</div>';
                    submitBtn.disabled = false;
                    submitBtn.textContent = 'Sign In';
                }
            } catch (error) {
                alertBox.innerHTML = '<div class="alert alert-error">✗ Connection error. Please try again.</div>';
                submitBtn.disabled = false;
                submitBtn.textContent = 'Sign In';
                console.error('Error:', error);
            }
        });
    </script>
</body>
</html>

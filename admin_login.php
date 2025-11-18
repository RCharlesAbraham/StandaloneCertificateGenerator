<?php
// Check if already logged in as admin
require_once 'config.php';
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
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .login-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.3);
            width: 100%;
            max-width: 400px;
            padding: 40px;
        }

        .login-header {
            text-align: center;
            margin-bottom: 30px;
        }

        .login-header h1 {
            color: #67150a;
            font-size: 28px;
            margin-bottom: 10px;
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
            margin-bottom: 8px;
            color: #333;
            font-weight: 600;
            font-size: 14px;
        }

        .form-group input {
            width: 100%;
            padding: 12px;
            border: 2px solid #ddd;
            border-radius: 8px;
            font-size: 14px;
            transition: border-color 0.3s;
        }

        .form-group input:focus {
            outline: none;
            border-color: #67150a;
        }

        .login-btn {
            width: 100%;
            padding: 15px;
            background: #67150a;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.3s;
        }

        .login-btn:hover {
            background: #8a1d0f;
        }

        .alert {
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 14px;
        }

        .alert-error {
            background: #fee;
            color: #c33;
            border: 1px solid #fcc;
        }

        .back-link {
            text-align: center;
            margin-top: 20px;
        }

        .back-link a {
            color: #67150a;
            text-decoration: none;
            font-weight: 600;
        }

        .back-link a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="login-card">
        <div class="login-header">
            <h1>Admin Login</h1>
            <p>Certificate Generator Management</p>
        </div>

        <div id="alertBox"></div>

        <form id="adminLoginForm">
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" required placeholder="Enter admin username">
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required placeholder="Enter admin password">
            </div>

            <button type="submit" class="login-btn">Login</button>
        </form>

        <div class="back-link">
            <a href="index.php">← Back to Certificate Generator</a>
        </div>
    </div>

    <script>
        const loginForm = document.getElementById('adminLoginForm');
        const alertBox = document.getElementById('alertBox');

        loginForm.addEventListener('submit', (e) => {
            e.preventDefault();

            const username = document.getElementById('username').value;
            const password = document.getElementById('password').value;

            const formData = new FormData();
            formData.append('username', username);
            formData.append('password', password);

            fetch('admin_login_process.php', {
                method: 'POST',
                body: formData
            })
            .then(response => {
                console.log('Response status:', response.status);
                return response.json();
            })
            .then(data => {
                console.log('Login response:', data);
                if (data.success) {
                    sessionStorage.setItem('adminAuthenticated', 'true');
                    sessionStorage.setItem('adminUsername', username);
                    window.location.href = 'admin_panel.php';
                } else {
                    showAlert(data.message || 'Login failed');
                }
            })
            .catch(error => {
                showAlert('An error occurred. Please try again.');
                console.error('Error:', error);
            });
        });

        function showAlert(message) {
            alertBox.innerHTML = `<div class="alert alert-error">${message}</div>`;
        }
    </script>
</body>
</html>

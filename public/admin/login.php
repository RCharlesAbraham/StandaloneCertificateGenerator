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
    <link rel="stylesheet" href="../styles.css">
    <style>
        /* Inverted theme for Admin Login (maroon card, light inputs, white text) */
        body { background: #f5f5f5; }
        .admin-login-wrapper { max-width:420px; margin:60px auto; }
        .admin-login-card {
            background: #67150a; /* maroon card */
            color: #ffffff; /* white text */
            padding: 28px;
            border-radius: 10px;
            box-shadow: 0 6px 20px rgba(0,0,0,0.08);
        }
        .admin-login-card h2 { color: #ffffff; margin-top:0; }
        .admin-login-card label { color: #fff; font-weight:600; }
        .admin-login-card input[type="text"],
        .admin-login-card input[type="password"] {
            width:100%; padding:10px; border-radius:6px; border:1px solid rgba(255,255,255,0.12);
            background: #ffffff; color: #67150a; /* inputs are white with maroon text */
        }
        .admin-login-card input::placeholder { color: rgba(103,21,10,0.4); }
        .admin-login-card .btn.btn-primary {
            background: #ffffff; color: #67150a; border: none; padding: 12px 18px; border-radius:6px;
            box-shadow: none;
        }
        .admin-login-card .btn.btn-primary:hover { background: #f2f2f2; }
        #adminLoginMessage { color: #ffdede; }
        a.back-to-app { color: #fff; text-decoration:underline; }
    </style>
</head>
<body>
    <!-- Admin login form (same structure as main login, inverted colors) -->
    <div class="login-wrapper admin-login-wrapper">
        <div class="login-card admin-login-card">
            <img src="../assets/MMC-LOGO-1-229x300.png" alt="Logo" class="login-logo" />
            <div class="header" style="border-bottom:2px solid rgba(255,255,255,0.08); margin-bottom:16px; padding-bottom:12px;">
                <div style="display:flex;flex-direction:column;align-items:center;gap:6px;padding:6px 0; text-align:center;">
                    <div style="width:100%; display:block;">
                        <h2 style="margin-left: 90px;font-size:1.4rem;">Admin Login</h2>
                        <p style="margin-left: 90px;font-size:0.95rem;opacity:0.95;">Certificate Generator</p>
                    </div>
                </div>
            </div>

            <div id="adminLoginMessage" style="margin-bottom:12px; color:#ffdede; display:none;"></div>

            <form id="adminLoginForm" class="login-form">
                <div class="form-group">
                    <label for="adminUsername">Email Address</label>
                    <input type="text" name="username" id="adminUsername" required placeholder="you@example.com">
                </div>

                <div class="form-group">
                    <label for="adminPassword">Password</label>
                    <input type="password" name="password" id="adminPassword" required placeholder="Enter your password">
                </div>

                <div class="login-actions" style="align-items:center;">
                    <label style="display:flex; align-items:center; gap:8px; color:rgba(255,255,255,0.9); font-weight:500;">
                        <input type="checkbox" id="adminRemember" style=" margin-left: -160px;width:16px; height:16px;"> Remember me
                    </label>
                </div>

                <div class="login-actions" style="margin-top:16px;">
                    <button type="submit" id="adminLoginButton" class="btn btn-primary">Sign In</button>
                </div>

                <div class="login-footer" style="margin-top:18px; color:rgba(255,255,255,0.9);">
                    <p style="margin:0;">Don't have an account? <a href="../register.php" style="color:#fff; text-decoration:underline;">Sign up here</a></p>
                </div>
            </form>
        </div>
    </div>

    <script>
        (function(){
            var form = document.getElementById('adminLoginForm');
            var msg = document.getElementById('adminLoginMessage');
            var btn = document.getElementById('adminLoginButton');
            form.addEventListener('submit', function(e){
                e.preventDefault();
                msg.style.display = 'none';
                btn.disabled = true;
                btn.textContent = 'Signing in...';

                var formData = new FormData(form);

                fetch('admin_login_process.php', {
                    method: 'POST',
                    body: formData
                }).then(function(res){
                    return res.json();
                }).then(function(data){
                    if (data && data.success) {
                        // Redirect to admin panel
                        window.location.href = 'panel.php';
                    } else {
                        msg.textContent = data.message || 'Login failed';
                        msg.style.display = 'block';
                    }
                }).catch(function(err){
                    console.error('Admin login error', err);
                    msg.textContent = 'An error occurred';
                    msg.style.display = 'block';
                }).finally(function(){
                    btn.disabled = false;
                    btn.textContent = 'Login';
                });
            });
        })();
    </script>
</body>
</html>

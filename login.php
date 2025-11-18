<?php
// Check if already logged in
require_once 'config.php';
if (isset($_SESSION['user_id']) && $_SESSION['user_id']) {
    header('Location: index.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Certificate Generator</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <!-- Modal Notification -->
    <div id="notificationModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" id="modalIcon">
                    <circle cx="12" cy="12" r="10"></circle>
                    <line x1="12" y1="16" x2="12" y2="12"></line>
                    <line x1="12" y1="8" x2="12.01" y2="8"></line>
                </svg>
                <h3 id="modalTitle">Notification</h3>
                <button class="modal-close" id="modalClose">&times;</button>
            </div>
            <div class="modal-body">
                <p id="modalMessage"></p>
            </div>
            <div class="modal-footer">
                <button id="modalOk" class="btn btn-primary">OK</button>
            </div>
        </div>
    </div>

    <div class="login-wrapper">
        <div class="login-card">
            <div class="header" style="display:flex; align-items:center; gap:12px;">
                <svg width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z" />
                    <polyline points="14 2 14 8 20 8" />
                </svg>
                <div>
                    <h1 style="font-size:16px; margin:0; color:#67150a;">Certificate Generator</h1>
                    <p class="subtitle">Sign in to manage and generate certificates</p>
                </div>
            </div>

            <form id="loginForm">
                <div class="form-section">
                    <div class="form-group">
                        <label for="email">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z" />
                                <polyline points="22,6 12,13 2,6" />
                            </svg>
                            Email Address
                        </label>
                        <input type="email" id="email" name="email" placeholder="you@example.com" required style="width:100%; box-sizing:border-box;">
                    </div>

                    <div class="form-group">
                        <label for="password">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
                                <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                            </svg>
                            Password
                        </label>
                        <input type="password" id="password" name="password" placeholder="Enter your password" required style="width:100%; box-sizing:border-box;">
                    </div>

                    <div style="display:flex; justify-content:space-between; align-items:center; margin-top:6px;">
                        <label style="display:flex; align-items:center; gap:8px; font-size:13px; color:#555;">
                            <input type="checkbox" id="remember"> Remember me
                        </label>
                    </div>

                    <div class="login-actions">
                        <button type="submit" class="btn btn-primary" style="flex:1;">Sign In</button>
                    </div>
                </div>
            </form>

            <div class="login-footer">
                <small>Don't have an account? <a href="register.php" style="color:#67150a; font-weight:600; text-decoration:none;">Sign up here</a></small>
            </div>
        </div>
    </div>

    <script>
        const loginForm = document.getElementById('loginForm');
        const modalMessage = document.getElementById('modalMessage');
        const modalTitle = document.getElementById('modalTitle');
        const notificationModal = document.getElementById('notificationModal');

        // Check if already logged in
        if (sessionStorage.getItem('isAuthenticated') === 'true') {
            window.location.href = 'index.php';
        }

        loginForm.addEventListener('submit', async (e) => {
            e.preventDefault();

            const email = document.getElementById('email').value;
            const password = document.getElementById('password').value;
            const remember = document.getElementById('remember').checked;

            const formData = new FormData();
            formData.append('email', email);
            formData.append('password', password);

            try {
                const response = await fetch('login_process.php', {
                    method: 'POST',
                    body: formData
                });

                const data = await response.json();

                if (data.success) {
                    // Store session data
                    sessionStorage.setItem('isAuthenticated', 'true');
                    sessionStorage.setItem('userId', data.user_id);
                    sessionStorage.setItem('userName', data.name);
                    sessionStorage.setItem('userType', data.user_type);
                    sessionStorage.setItem('userEmail', data.email);

                    if (remember) {
                        localStorage.setItem('rememberedEmail', email);
                    }

                    // Show success modal
                    showModal('Login successful! Redirecting...', 'Success');
                    setTimeout(() => {
                        window.location.href = 'index.php';
                    }, 1500);
                } else {
                    showModal(data.message || 'Login failed', 'Error');
                }
            } catch (error) {
                console.error('Login error:', error);
                showModal('An error occurred. Please try again.', 'Error');
            }
        });

        function showModal(message, title) {
            modalMessage.textContent = message;
            modalTitle.textContent = title;
            notificationModal.classList.add('show');
            notificationModal.style.display = 'flex';

            document.getElementById('modalOk').onclick = () => {
                notificationModal.classList.remove('show');
                notificationModal.style.display = 'none';
            };

            document.getElementById('modalClose').onclick = () => {
                notificationModal.classList.remove('show');
                notificationModal.style.display = 'none';
            };
        }

        // Pre-fill email if remembered
        const rememberedEmail = localStorage.getItem('rememberedEmail');
        if (rememberedEmail) {
            document.getElementById('email').value = rememberedEmail;
            document.getElementById('remember').checked = true;
        }
    </script>
</body>
</html>

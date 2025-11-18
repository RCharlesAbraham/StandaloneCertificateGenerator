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
    <title>Create Account - Certificate Generator</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        .register-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 20px;
        }

        .register-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
            width: 100%;
            max-width: 500px;
            padding: 40px;
        }

        .register-header {
            text-align: center;
            margin-bottom: 30px;
        }

        .register-header h1 {
            color: #67150a;
            font-size: 28px;
            margin-bottom: 10px;
        }

        .register-header p {
            color: #666;
            font-size: 14px;
        }

        .user-type-selector {
            display: flex;
            gap: 15px;
            margin-bottom: 30px;
        }

        .user-type-btn {
            flex: 1;
            padding: 15px;
            border: 2px solid #ddd;
            background: white;
            border-radius: 10px;
            cursor: pointer;
            transition: all 0.3s;
            text-align: center;
            font-size: 16px;
            font-weight: 600;
        }

        .user-type-btn:hover {
            border-color: #67150a;
            transform: translateY(-2px);
        }

        .user-type-btn.active {
            border-color: #67150a;
            background: #67150a;
            color: white;
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

        .form-group input,
        .form-group select {
            width: 100%;
            padding: 12px;
            border: 2px solid #ddd;
            border-radius: 8px;
            font-size: 14px;
            transition: border-color 0.3s;
        }

        .form-group input:focus,
        .form-group select:focus {
            outline: none;
            border-color: #67150a;
        }

        .college-options {
            display: flex;
            gap: 10px;
            margin-bottom: 10px;
        }

        .college-radio {
            flex: 1;
        }

        .college-radio input[type="radio"] {
            width: auto;
            margin-right: 5px;
        }

        .college-radio label {
            display: flex;
            align-items: center;
            padding: 10px;
            border: 2px solid #ddd;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s;
        }

        .college-radio input[type="radio"]:checked + label {
            border-color: #67150a;
            background: #f8f8f8;
        }

        .hidden {
            display: none !important;
        }

        .submit-btn {
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

        .submit-btn:hover {
            background: #8a1d0f;
        }

        .submit-btn:disabled {
            background: #ccc;
            cursor: not-allowed;
        }

        .login-link {
            text-align: center;
            margin-top: 20px;
            color: #666;
        }

        .login-link a {
            color: #67150a;
            font-weight: 600;
            text-decoration: none;
        }

        .login-link a:hover {
            text-decoration: underline;
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

        .alert-success {
            background: #efe;
            color: #3c3;
            border: 1px solid #cfc;
        }

        .required {
            color: #c33;
        }
    </style>
</head>
<body>
    <div class="register-container">
        <div class="register-card">
            <div class="register-header">
                <h1>Create Account</h1>
                <p>Certificate Generator - Madras Christian College</p>
            </div>

            <div id="alertBox"></div>

            <form id="registerForm" method="POST" action="register_process.php">
                <!-- User Type Selection -->
                <div class="user-type-selector">
                    <button type="button" class="user-type-btn active" data-type="student">
                        Student
                    </button>
                    <button type="button" class="user-type-btn" data-type="staff">
                        Staff
                    </button>
                </div>

                <input type="hidden" name="user_type" id="userType" value="student">

                <!-- Student Fields -->
                <div id="studentFields">
                    <div class="form-group">
                        <label for="reg_no">Registration Number <span class="required">*</span></label>
                        <input type="text" id="reg_no" name="reg_no" placeholder="Enter your registration number">
                    </div>
                </div>

                <!-- Staff Fields -->
                <div id="staffFields" class="hidden">
                    <div class="form-group">
                        <label for="designation">Designation <span class="required">*</span></label>
                        <input type="text" id="designation" name="designation" placeholder="e.g., Assistant Professor, HOD">
                    </div>
                </div>

                <!-- Common Fields -->
                <div class="form-group">
                    <label for="name">Full Name <span class="required">*</span></label>
                    <input type="text" id="name" name="name" required placeholder="Enter your full name">
                </div>

                <div class="form-group">
                    <label for="department">Department <span class="required">*</span></label>
                    <input type="text" id="department" name="department" required placeholder="e.g., Computer Science, Mathematics">
                </div>

                <div class="form-group">
                    <label for="phone_no">Phone Number <span class="required">*</span></label>
                    <input type="tel" id="phone_no" name="phone_no" required placeholder="Enter your phone number">
                </div>

                <div class="form-group">
                    <label for="email">Email Address <span class="required">*</span></label>
                    <input type="email" id="email" name="email" required placeholder="Enter your email">
                </div>

                <div class="form-group">
                    <label for="password">Password <span class="required">*</span></label>
                    <input type="password" id="password" name="password" required placeholder="Create a password (min 6 characters)">
                </div>

                <div class="form-group">
                    <label for="confirm_password">Confirm Password <span class="required">*</span></label>
                    <input type="password" id="confirm_password" name="confirm_password" required placeholder="Re-enter your password">
                </div>

                <div class="form-group">
                    <label>College <span class="required">*</span></label>
                    <div class="college-options">
                        <div class="college-radio">
                            <input type="radio" name="college_type" id="collegeMCC" value="mcc" checked>
                            <label for="collegeMCC">Madras Christian College</label>
                        </div>
                        <div class="college-radio">
                            <input type="radio" name="college_type" id="collegeOther" value="other">
                            <label for="collegeOther">Other</label>
                        </div>
                    </div>
                    <input type="text" id="other_college" name="other_college" class="hidden" placeholder="Enter college name">
                </div>

                <button type="submit" class="submit-btn">Create Account</button>
            </form>

            <div class="login-link">
                Already have an account? <a href="login.php">Login here</a>
            </div>
        </div>
    </div>

    <script>
        // User type toggle
        const userTypeBtns = document.querySelectorAll('.user-type-btn');
        const userTypeInput = document.getElementById('userType');
        const studentFields = document.getElementById('studentFields');
        const staffFields = document.getElementById('staffFields');

        userTypeBtns.forEach(btn => {
            btn.addEventListener('click', () => {
                userTypeBtns.forEach(b => b.classList.remove('active'));
                btn.classList.add('active');
                
                const type = btn.dataset.type;
                userTypeInput.value = type;

                if (type === 'student') {
                    studentFields.classList.remove('hidden');
                    staffFields.classList.add('hidden');
                    document.getElementById('reg_no').required = true;
                    document.getElementById('designation').required = false;
                } else {
                    studentFields.classList.add('hidden');
                    staffFields.classList.remove('hidden');
                    document.getElementById('reg_no').required = false;
                    document.getElementById('designation').required = true;
                }
            });
        });

        // College type toggle
        const collegeRadios = document.querySelectorAll('input[name="college_type"]');
        const otherCollegeInput = document.getElementById('other_college');

        collegeRadios.forEach(radio => {
            radio.addEventListener('change', () => {
                if (radio.value === 'other') {
                    otherCollegeInput.classList.remove('hidden');
                    otherCollegeInput.required = true;
                } else {
                    otherCollegeInput.classList.add('hidden');
                    otherCollegeInput.required = false;
                    otherCollegeInput.value = '';
                }
            });
        });

        // Form validation
        const registerForm = document.getElementById('registerForm');
        const alertBox = document.getElementById('alertBox');

        registerForm.addEventListener('submit', (e) => {
            e.preventDefault();
            
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirm_password').value;

            // Clear previous alerts
            alertBox.innerHTML = '';

            // Validate password
            if (password.length < 6) {
                showAlert('Password must be at least 6 characters long', 'error');
                return;
            }

            if (password !== confirmPassword) {
                showAlert('Passwords do not match', 'error');
                return;
            }

            // Submit form
            const formData = new FormData(registerForm);
            
            fetch('register_process.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showAlert(data.message, 'success');
                    setTimeout(() => {
                        window.location.href = 'login.php';
                    }, 2000);
                } else {
                    showAlert(data.message, 'error');
                }
            })
            .catch(error => {
                showAlert('An error occurred. Please try again.', 'error');
                console.error('Error:', error);
            });
        });

        function showAlert(message, type) {
            alertBox.innerHTML = `<div class="alert alert-${type}">${message}</div>`;
        }
    </script>
</body>
</html>

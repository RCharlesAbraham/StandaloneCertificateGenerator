<?php
require_once __DIR__ . '/../includes/config.php';

// Redirect to index if already logged in
if (isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Certificate Generator</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="register-container">
        <div class="register-card">
            <div class="login-header-row" style="display:flex; align-items:center; gap:12px; margin-bottom:12px; flex-wrap:wrap;">
                <div class="login-logo-wrapper" style="flex:0 0 auto;">
                    <img src="assets/MMC-LOGO-2-229x300.png" alt="MCC-MRF Logo" class="login-logo" style="width:283px; max-width:80%; height:auto; display:block; margin:0 auto; padding-bottom:20px;">
                </div>
                <div class="register-header" style="flex:1 1 auto; text-align:left; min-width:200px;">  
                    <h2 style="margin:0; font-size:1.5rem; font-weight:600; color:#67150a;">Create Account</h2>
                    <p style="margin:4px 0 0; color:#666;">Certificate Generator - Madras Christian College</p>
                </div>
            </div>

            <div id="alertBox"></div>

            <form id="registerForm">
                <div class="form-grid">
                    
                <!-- User Type Selection removed: use Registration Number (students) or Designation (staff) -->
                <div class="form-group col-span-2">
                    <p style="color:#666; font-size:14px; margin-bottom:8px;">Please provide your Registration Number to create a student account. Staff registrations are handled separately by admins.</p>
                </div>

                

                <!-- Student Fields -->
                <div id="studentFields">
                    <!-- Stream Selection -->
                    <div class="form-group">
                        <label>Stream <span class="required">*</span></label>
                        <div class="stream-options">
                            <label class="radio-option">
                                <input type="radio" name="stream" id="streamAided" value="aided" required>
                                <span>Aided</span>
                            </label>
                            <label class="radio-option">
                                <input type="radio" name="stream" id="streamSFS" value="sfs" required>
                                <span>Self-Financed Stream (SFS)</span>
                            </label>
                        </div>
                    </div>

                    <!-- Level Selection -->
                    <div class="form-group">
                        <label>Level <span class="required">*</span></label>
                        <div class="level-options">
                            <label class="radio-option">
                                <input type="radio" name="level" id="levelUG" value="ug" required>
                                <span>UG (Undergraduate)</span>
                            </label>
                            <label class="radio-option">
                                <input type="radio" name="level" id="levelPG" value="pg" required>
                                <span>PG (Postgraduate)</span>
                            </label>
                        </div>
                    </div>

                    <!-- Registration Number -->
                    <div class="form-group">
                        <label for="reg_no">Registration Number <span class="required">*</span></label>
                        <input type="text" id="reg_no" name="reg_no" placeholder="Enter numbers only" required pattern="[0-9]+" title="Only numbers allowed">
                    </div>
                </div>

                <!-- Staff registration disabled: only student signup is supported -->

                <div class="form-group">
                    <label for="name">Full Name <span class="required">*</span></label>
                    <input type="text" id="name" name="name" required placeholder="Enter your full name" pattern="[A-Za-z ]+" title="Only letters and spaces allowed">
                </div>

                <!-- Department Selection (Dynamic based on Stream & Level) -->
                <div class="form-group col-span-2" id="departmentGroup">
                    <label for="department">Department <span class="required">*</span></label>
                    <select id="department" name="department" required>
                        <option value="">-- Select Stream and Level first --</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="phone_no">Phone Number <span class="required">*</span></label>
                    <input type="tel" id="phone_no" name="phone_no" required placeholder="Enter your phone number">
                </div>

                <div class="form-group">
                    <label for="email">College Email <span class="required">*</span></label>
                    <input type="email" id="email" name="email" required placeholder="yourname@mcc.edu.in" pattern="[a-zA-Z0-9._%+-]+@mcc\.edu\.in$" title="Must be a valid @mcc.edu.in email address">
                </div>

                <div class="form-group">
                    <label for="password">Password <span class="required">*</span></label>
                    <input type="password" id="password" name="password" required placeholder="Create a password (min 6 characters)">
                </div>

                <div class="form-group">
                    <label for="confirm_password">Confirm Password <span class="required">*</span></label>
                    <input type="password" id="confirm_password" name="confirm_password" required placeholder="Re-enter your password">
                </div>

                <!-- College removed: all users assumed to be from Madras Christian College -->

                <button type="submit" class="submit-btn col-span-2">Create Account</button>
                </div>
            </form>

            <div class="login-link">
                Already have an account? <a href="login.php">Login here</a>
            </div>
        </div>
    </div>

    <script>
        // Department data structure
        const departments = {
            aided: {
                ug: [
                    'English Language & Literature',
                    'Tamil Literature',
                    'History',
                    'Political Science',
                    'Economics',
                    'Philosophy',
                    'Commerce (General)',
                    'Mathematics',
                    'Statistics',
                    'Physics',
                    'Chemistry',
                    'Plant Biology & Plant Biotechnology',
                    'Zoology'
                ],
                pg: [
                    'English Language & Literature',
                    'Tamil Literature',
                    'History',
                    'Political Science',
                    'Public Administration',
                    'Economics',
                    'Philosophy',
                    'Commerce (M.Com)',
                    'MSW (Community Development / Medical & Psychiatry)',
                    'Mathematics',
                    'Statistics',
                    'Physics',
                    'Chemistry',
                    'Plant Biology & Plant Biotechnology',
                    'Zoology'
                ]
            },
            sfs: {
                ug: [
                    'English Language & Literature',
                    'Journalism',
                    'History (Vocational – Archaeology & Museology)',
                    'Social Work (BSW)',
                    'Commerce (General)',
                    'Commerce (Accounting & Finance)',
                    'Commerce (Professional Accounting)',
                    'Business Administration (BBA)',
                    'Computer Applications (BCA)',
                    'Geography, Tourism & Travel Management',
                    'Hospitality & Tourism',
                    'Mathematics',
                    'Physics',
                    'Microbiology',
                    'Computer Science',
                    'Visual Communication',
                    'Physical Education, Health Education & Sports',
                    'Psychology'
                ],
                pg: [
                    'M.A. Communication',
                    'MSW – Human Resource Management',
                    'M.Com – Computer Oriented Business Applications',
                    'M.Sc. Chemistry',
                    'M.Sc. Applied Microbiology',
                    'MCA – Computer Applications',
                    'M.Sc. Data Science'
                ]
            }
        };

        // Student-only registration: ensure student fields visible and required
        const studentFields = document.getElementById('studentFields');
        const departmentGroup = document.getElementById('departmentGroup');
        const regNoInput = document.getElementById('reg_no');

        function ensureStudentFields() {
            studentFields.classList.remove('hidden');
            departmentGroup.style.display = 'block';
            if (regNoInput) regNoInput.required = true;
            document.querySelectorAll('input[name="stream"]').forEach(s => s.required = true);
            document.querySelectorAll('input[name="level"]').forEach(l => l.required = true);
        }

        // Initialize on page load
        ensureStudentFields();

        // Stream and Level selection handlers
        const streamRadios = document.querySelectorAll('input[name="stream"]');
        const levelRadios = document.querySelectorAll('input[name="level"]');
        const departmentSelect = document.getElementById('department');

        function updateDepartmentDropdown() {
            const selectedStream = document.querySelector('input[name="stream"]:checked');
            const selectedLevel = document.querySelector('input[name="level"]:checked');
            
            // Clear existing options
            departmentSelect.innerHTML = '<option value="">-- Select a department --</option>';
            
            if (selectedStream && selectedLevel) {
                const stream = selectedStream.value;
                const level = selectedLevel.value;
                const deptList = departments[stream][level];
                
                deptList.forEach(dept => {
                    const option = document.createElement('option');
                    option.value = dept;
                    option.textContent = dept;
                    departmentSelect.appendChild(option);
                });
            } else {
                departmentSelect.innerHTML = '<option value="">-- Select Stream and Level first --</option>';
            }
        }

        // resetDepartmentForStaff removed - staff registration disabled

        streamRadios.forEach(radio => {
            radio.addEventListener('change', updateDepartmentDropdown);
        });

        levelRadios.forEach(radio => {
            radio.addEventListener('change', updateDepartmentDropdown);
        });

        // College selection removed (no longer part of registration form)

        // Form validation and submission
        const registerForm = document.getElementById('registerForm');
        const alertBox = document.getElementById('alertBox');

        registerForm.addEventListener('submit', (e) => {
            e.preventDefault();
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirm_password').value;
            const email = document.getElementById('email').value.trim();
            const name = document.getElementById('name').value.trim();
            const regNo = document.getElementById('reg_no').value.trim();
            alertBox.innerHTML = '';

            // Validate registration number (numbers only)
            if (!regNo) {
                showAlert('Registration number is required to create an account.', 'error');
                return;
            }
            if (!/^[0-9]+$/.test(regNo)) {
                showAlert('Registration number must contain only numbers', 'error');
                return;
            }

            // Validate full name (letters and spaces only)
            if (!/^[A-Za-z ]+$/.test(name)) {
                showAlert('Full name must contain only letters and spaces', 'error');
                return;
            }

            // Validate college email (must end with @mcc.edu.in)
            if (!email.endsWith('@mcc.edu.in')) {
                showAlert('Email must be a valid college email ending with @mcc.edu.in', 'error');
                return;
            }
            if (!document.querySelector('input[name="stream"]:checked') || !document.querySelector('input[name="level"]:checked')) {
                showAlert('Please select stream and level for students', 'error');
                return;
            }

            if (password.length < 6) {
                showAlert('Password must be at least 6 characters long', 'error');
                return;
            }
            if (password !== confirmPassword) {
                showAlert('Passwords do not match', 'error');
                return;
            }

            const formData = new FormData(registerForm);
            fetch('actions/register_process.php', {
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

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
            <div class="register-header">
                <h1>Create Account</h1>
                <p>Certificate Generator - Madras Christian College</p>
            </div>

            <div id="alertBox"></div>

            <form id="registerForm">
                <!-- User Type Selection -->
                <div class="form-group">
                    <label>User Type <span class="required">*</span></label>
                    <div class="user-type-options">
                        <div class="radio-option">
                            <input type="radio" name="user_type" id="typeStudent" value="student" checked>
                            <label for="typeStudent">Student</label>
                        </div>
                        <div class="radio-option">
                            <input type="radio" name="user_type" id="typeStaff" value="staff">
                            <label for="typeStaff">Staff</label>
                        </div>
                    </div>
                </div>

                <!-- Student Fields -->
                <div id="studentFields">
                    <!-- Stream Selection -->
                    <div class="form-group">
                        <label>Stream <span class="required">*</span></label>
                        <div class="stream-options">
                            <div class="radio-option">
                                <input type="radio" name="stream" id="streamAided" value="aided" required>
                                <label for="streamAided">Aided</label>
                            </div>
                            <div class="radio-option">
                                <input type="radio" name="stream" id="streamSFS" value="sfs" required>
                                <label for="streamSFS">Self-Financed Stream (SFS)</label>
                            </div>
                        </div>
                    </div>

                    <!-- Level Selection -->
                    <div class="form-group">
                        <label>Level <span class="required">*</span></label>
                        <div class="level-options">
                            <div class="radio-option">
                                <input type="radio" name="level" id="levelUG" value="ug" required>
                                <label for="levelUG">UG (Undergraduate)</label>
                            </div>
                            <div class="radio-option">
                                <input type="radio" name="level" id="levelPG" value="pg" required>
                                <label for="levelPG">PG (Postgraduate)</label>
                            </div>
                        </div>
                    </div>

                    <!-- Registration Number -->
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

                <div class="form-group">
                    <label for="name">Full Name <span class="required">*</span></label>
                    <input type="text" id="name" name="name" required placeholder="Enter your full name">
                </div>

                <!-- Department Selection (Dynamic based on Stream & Level) -->
                <div class="form-group" id="departmentGroup">
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

        // User type toggle
        const userTypeRadios = document.querySelectorAll('input[name="user_type"]');
        const studentFields = document.getElementById('studentFields');
        const staffFields = document.getElementById('staffFields');
        const departmentGroup = document.getElementById('departmentGroup');

        userTypeRadios.forEach(radio => {
            radio.addEventListener('change', () => {
                const type = radio.value;

                if (type === 'student') {
                    studentFields.classList.remove('hidden');
                    staffFields.classList.add('hidden');
                    departmentGroup.style.display = 'block';
                    document.getElementById('reg_no').required = true;
                    document.getElementById('designation').required = false;
                    
                    // Make stream and level required
                    document.querySelectorAll('input[name="stream"]').forEach(s => s.required = true);
                    document.querySelectorAll('input[name="level"]').forEach(l => l.required = true);
                } else {
                    studentFields.classList.add('hidden');
                    staffFields.classList.remove('hidden');
                    departmentGroup.style.display = 'block';
                    document.getElementById('reg_no').required = false;
                    document.getElementById('designation').required = true;
                    
                    // Make stream and level not required for staff
                    document.querySelectorAll('input[name="stream"]').forEach(s => s.required = false);
                    document.querySelectorAll('input[name="level"]').forEach(l => l.required = false);
                    
                    // Reset department to text input for staff
                    resetDepartmentForStaff();
                }
            });
        });

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

        function resetDepartmentForStaff() {
            // For staff, replace select with text input
            const deptGroup = document.getElementById('departmentGroup');
            deptGroup.innerHTML = `
                <label for="department">Department <span class="required">*</span></label>
                <input type="text" id="department" name="department" required placeholder="e.g., Computer Science, Mathematics">
            `;
        }

        streamRadios.forEach(radio => {
            radio.addEventListener('change', updateDepartmentDropdown);
        });

        levelRadios.forEach(radio => {
            radio.addEventListener('change', updateDepartmentDropdown);
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

        // Form validation and submission
        const registerForm = document.getElementById('registerForm');
        const alertBox = document.getElementById('alertBox');

        registerForm.addEventListener('submit', (e) => {
            e.preventDefault();
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirm_password').value;
            alertBox.innerHTML = '';

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

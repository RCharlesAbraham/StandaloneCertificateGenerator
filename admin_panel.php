<?php
require_once 'config.php';

// Check if admin is authenticated
if (!isset($_SESSION['admin_id'])) {
    header('Location: admin_login.php');
    exit();
}

$admin_username = $_SESSION['admin_username'] ?? 'Admin';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - Certificate Generator</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f5f5f5;
        }

        .admin-container {
            display: flex;
            min-height: 100vh;
        }

        .sidebar {
            width: 250px;
            background: #67150a;
            color: white;
            padding: 20px;
            position: fixed;
            height: 100vh;
            overflow-y: auto;
        }

        .sidebar h2 {
            margin-bottom: 30px;
            font-size: 20px;
            border-bottom: 2px solid rgba(255,255,255,0.2);
            padding-bottom: 15px;
        }

        .sidebar-menu {
            list-style: none;
        }

        .sidebar-menu li {
            margin-bottom: 10px;
        }

        .sidebar-menu a {
            color: white;
            text-decoration: none;
            padding: 12px 15px;
            display: block;
            border-radius: 8px;
            transition: all 0.3s;
        }

        .sidebar-menu a:hover,
        .sidebar-menu a.active {
            background: rgba(255,255,255,0.1);
        }

        .main-content {
            margin-left: 250px;
            flex: 1;
            padding: 30px;
        }

        .header {
            background: white;
            padding: 20px 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .header h1 {
            color: #67150a;
            font-size: 24px;
        }

        .logout-btn {
            padding: 10px 20px;
            background: #67150a;
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
        }

        .logout-btn:hover {
            background: #8a1d0f;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .stat-card h3 {
            color: #666;
            font-size: 14px;
            margin-bottom: 10px;
        }

        .stat-card .number {
            font-size: 32px;
            font-weight: bold;
            color: #67150a;
        }

        .content-card {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }

        .content-card h2 {
            color: #67150a;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #f0f0f0;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        table th,
        table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #f0f0f0;
        }

        table th {
            background: #f8f8f8;
            color: #333;
            font-weight: 600;
        }

        table tr:hover {
            background: #f8f8f8;
        }

        .badge {
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }

        .badge-student {
            background: #e3f2fd;
            color: #1976d2;
        }

        .badge-staff {
            background: #f3e5f5;
            color: #7b1fa2;
        }

        .badge-active {
            background: #e8f5e9;
            color: #388e3c;
        }

        .badge-inactive {
            background: #ffebee;
            color: #d32f2f;
        }

        .search-box {
            margin-bottom: 20px;
            display: flex;
            gap: 10px;
        }

        .search-box input {
            flex: 1;
            padding: 12px;
            border: 2px solid #ddd;
            border-radius: 8px;
            font-size: 14px;
        }

        .search-box select {
            padding: 12px;
            border: 2px solid #ddd;
            border-radius: 8px;
            font-size: 14px;
        }

        .btn {
            padding: 10px 20px;
            background: #67150a;
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
        }

        .btn:hover {
            background: #8a1d0f;
        }

        .no-data {
            text-align: center;
            padding: 40px;
            color: #999;
        }

        .loading {
            text-align: center;
            padding: 40px;
            color: #666;
        }

        .pagination {
            display: flex;
            justify-content: center;
            gap: 10px;
            margin-top: 20px;
        }

        .pagination button {
            padding: 8px 15px;
            border: 1px solid #ddd;
            background: white;
            cursor: pointer;
            border-radius: 5px;
        }

        .pagination button.active {
            background: #67150a;
            color: white;
            border-color: #67150a;
        }

        .pagination button:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <aside class="sidebar">
            <h2>Admin Panel</h2>
            <ul class="sidebar-menu">
                <li><a href="#" class="active" onclick="showSection('dashboard')">Dashboard</a></li>
                <li><a href="#" onclick="showSection('users')">All Users</a></li>
                <li><a href="#" onclick="showSection('certificates')">Certificate Logs</a></li>
                <li><a href="#" onclick="showSection('activity')">Activity Logs</a></li>
                <li><a href="#" onclick="showSection('sessions')">Active Sessions</a></li>
            </ul>
        </aside>

        <main class="main-content">
            <div class="header">
                <div>
                    <h1>Welcome, <?php echo htmlspecialchars($admin_username); ?></h1>
                    <p style="color: #666; font-size: 14px; margin-top: 5px;">Certificate Generator Management System</p>
                </div>
                <button class="logout-btn" onclick="logout()">Logout</button>
            </div>

            <!-- Dashboard Section -->
            <div id="dashboard" class="section">
                <div class="stats-grid">
                    <div class="stat-card">
                        <h3>Total Users</h3>
                        <div class="number" id="totalUsers">0</div>
                    </div>
                    <div class="stat-card">
                        <h3>Students</h3>
                        <div class="number" id="totalStudents">0</div>
                    </div>
                    <div class="stat-card">
                        <h3>Staff</h3>
                        <div class="number" id="totalStaff">0</div>
                    </div>
                    <div class="stat-card">
                        <h3>Certificates Generated</h3>
                        <div class="number" id="totalCertificates">0</div>
                    </div>
                </div>

                <div class="content-card">
                    <h2>Recent Activity</h2>
                    <div id="recentActivity" class="loading">Loading...</div>
                </div>
            </div>

            <!-- Users Section -->
            <div id="users" class="section" style="display: none;">
                <div class="content-card">
                    <h2>All Users</h2>
                    <div class="search-box">
                        <input type="text" id="userSearch" placeholder="Search by name, email, or reg no...">
                        <select id="userTypeFilter">
                            <option value="">All Types</option>
                            <option value="student">Students</option>
                            <option value="staff">Staff</option>
                        </select>
                        <button class="btn" onclick="searchUsers()">Search</button>
                    </div>
                    <div id="usersTable" class="loading">Loading...</div>
                </div>
            </div>

            <!-- Certificates Section -->
            <div id="certificates" class="section" style="display: none;">
                <div class="content-card">
                    <h2>Certificate Generation Logs</h2>
                    <div id="certificatesTable" class="loading">Loading...</div>
                </div>
            </div>

            <!-- Activity Section -->
            <div id="activity" class="section" style="display: none;">
                <div class="content-card">
                    <h2>User Activity Logs</h2>
                    <div id="activityTable" class="loading">Loading...</div>
                </div>
            </div>

            <!-- Sessions Section -->
            <div id="sessions" class="section" style="display: none;">
                <div class="content-card">
                    <h2>Active User Sessions</h2>
                    <div id="sessionsTable" class="loading">Loading...</div>
                </div>
            </div>
        </main>
    </div>

    <script>
        // Check if admin is logged in
        if (!sessionStorage.getItem('adminAuthenticated')) {
            window.location.href = 'admin_login.php';
        }

        // Load dashboard data on page load
        loadDashboard();

        function showSection(sectionName) {
            // Hide all sections
            document.querySelectorAll('.section').forEach(section => {
                section.style.display = 'none';
            });

            // Remove active class from all menu items
            document.querySelectorAll('.sidebar-menu a').forEach(link => {
                link.classList.remove('active');
            });

            // Show selected section
            document.getElementById(sectionName).style.display = 'block';

            // Add active class to clicked menu item
            event.target.classList.add('active');

            // Load section data
            switch(sectionName) {
                case 'dashboard':
                    loadDashboard();
                    break;
                case 'users':
                    loadUsers();
                    break;
                case 'certificates':
                    loadCertificates();
                    break;
                case 'activity':
                    loadActivity();
                    break;
                case 'sessions':
                    loadSessions();
                    break;
            }
        }

        function loadDashboard() {
            fetch('admin_api.php?action=dashboard')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        document.getElementById('totalUsers').textContent = data.stats.total_users;
                        document.getElementById('totalStudents').textContent = data.stats.total_students;
                        document.getElementById('totalStaff').textContent = data.stats.total_staff;
                        document.getElementById('totalCertificates').textContent = data.stats.total_certificates;
                        
                        displayRecentActivity(data.recent_activity);
                    }
                });
        }

        function displayRecentActivity(activities) {
            const container = document.getElementById('recentActivity');
            if (activities.length === 0) {
                container.innerHTML = '<div class="no-data">No recent activity</div>';
                return;
            }

            let html = '<table><thead><tr><th>User</th><th>Type</th><th>Activity</th><th>Time</th></tr></thead><tbody>';
            activities.forEach(activity => {
                html += `<tr>
                    <td>${activity.name}</td>
                    <td><span class="badge badge-${activity.user_type}">${activity.user_type}</span></td>
                    <td>${activity.activity_type}</td>
                    <td>${activity.created_at}</td>
                </tr>`;
            });
            html += '</tbody></table>';
            container.innerHTML = html;
        }

        function loadUsers() {
            fetch('admin_api.php?action=users')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        displayUsers(data.users);
                    }
                });
        }

        function displayUsers(users) {
            const container = document.getElementById('usersTable');
            if (users.length === 0) {
                container.innerHTML = '<div class="no-data">No users found</div>';
                return;
            }

            let html = '<table><thead><tr><th>ID</th><th>Name</th><th>Type</th><th>Reg/Designation</th><th>Department</th><th>College</th><th>Email</th><th>Status</th><th>Registered</th></tr></thead><tbody>';
            users.forEach(user => {
                html += `<tr>
                    <td>${user.id}</td>
                    <td>${user.name}</td>
                    <td><span class="badge badge-${user.user_type}">${user.user_type}</span></td>
                    <td>${user.reg_no || user.designation || '-'}</td>
                    <td>${user.department}</td>
                    <td>${user.college}</td>
                    <td>${user.email}</td>
                    <td><span class="badge badge-${user.status}">${user.status}</span></td>
                    <td>${user.created_at}</td>
                </tr>`;
            });
            html += '</tbody></table>';
            container.innerHTML = html;
        }

        function searchUsers() {
            const searchTerm = document.getElementById('userSearch').value;
            const userType = document.getElementById('userTypeFilter').value;
            
            fetch(`admin_api.php?action=users&search=${searchTerm}&type=${userType}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        displayUsers(data.users);
                    }
                });
        }

        function loadCertificates() {
            fetch('admin_api.php?action=certificates')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        displayCertificates(data.certificates);
                    }
                });
        }

        function displayCertificates(certificates) {
            const container = document.getElementById('certificatesTable');
            if (certificates.length === 0) {
                container.innerHTML = '<div class="no-data">No certificates generated yet</div>';
                return;
            }

            let html = '<table><thead><tr><th>ID</th><th>User</th><th>Type</th><th>Recipient</th><th>Certificate No</th><th>Generation Type</th><th>Count</th><th>Generated At</th></tr></thead><tbody>';
            certificates.forEach(cert => {
                html += `<tr>
                    <td>${cert.id}</td>
                    <td>${cert.user_name}</td>
                    <td><span class="badge badge-${cert.user_type}">${cert.user_type}</span></td>
                    <td>${cert.recipient_name}</td>
                    <td>${cert.certificate_no || '-'}</td>
                    <td>${cert.generation_type}</td>
                    <td>${cert.bulk_count}</td>
                    <td>${cert.generated_at}</td>
                </tr>`;
            });
            html += '</tbody></table>';
            container.innerHTML = html;
        }

        function loadActivity() {
            fetch('admin_api.php?action=activity')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        displayActivity(data.activities);
                    }
                });
        }

        function displayActivity(activities) {
            const container = document.getElementById('activityTable');
            if (activities.length === 0) {
                container.innerHTML = '<div class="no-data">No activity logs</div>';
                return;
            }

            let html = '<table><thead><tr><th>User</th><th>Type</th><th>Activity</th><th>Description</th><th>IP Address</th><th>Time</th></tr></thead><tbody>';
            activities.forEach(activity => {
                html += `<tr>
                    <td>${activity.user_name}</td>
                    <td><span class="badge badge-${activity.user_type}">${activity.user_type}</span></td>
                    <td>${activity.activity_type}</td>
                    <td>${activity.activity_description || '-'}</td>
                    <td>${activity.ip_address}</td>
                    <td>${activity.created_at}</td>
                </tr>`;
            });
            html += '</tbody></table>';
            container.innerHTML = html;
        }

        function loadSessions() {
            fetch('admin_api.php?action=sessions')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        displaySessions(data.sessions);
                    }
                });
        }

        function displaySessions(sessions) {
            const container = document.getElementById('sessionsTable');
            if (sessions.length === 0) {
                container.innerHTML = '<div class="no-data">No active sessions</div>';
                return;
            }

            let html = '<table><thead><tr><th>User</th><th>Type</th><th>IP Address</th><th>Login Time</th><th>Last Activity</th><th>Status</th></tr></thead><tbody>';
            sessions.forEach(session => {
                html += `<tr>
                    <td>${session.user_name || 'Unknown'}</td>
                    <td><span class="badge badge-${session.user_type}">${session.user_type}</span></td>
                    <td>${session.ip_address}</td>
                    <td>${session.login_time}</td>
                    <td>${session.last_activity}</td>
                    <td><span class="badge badge-${session.status}">${session.status}</span></td>
                </tr>`;
            });
            html += '</tbody></table>';
            container.innerHTML = html;
        }

        function logout() {
            sessionStorage.removeItem('adminAuthenticated');
            window.location.href = 'admin_login.php';
        }

        // Auto-refresh dashboard every 30 seconds
        setInterval(() => {
            if (document.getElementById('dashboard').style.display !== 'none') {
                loadDashboard();
            }
        }, 30000);
    </script>
</body>
</html>

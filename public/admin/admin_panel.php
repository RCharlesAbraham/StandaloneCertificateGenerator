<?php
require_once __DIR__ . '/../../includes/config.php';

// Check if admin is authenticated
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
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
    <link rel="stylesheet" href="../styles.css">
    <style>
        body {
            background: #f5f7fa;
            margin: 0;
            padding: 0;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        }
        .admin-navbar {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 15px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .admin-navbar h1 {
            margin: 0;
            font-size: 20px;
        }
        .admin-user {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        .logout-btn {
            background: rgba(255,255,255,0.2);
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 6px;
            cursor: pointer;
            font-size: 14px;
        }
        .logout-btn:hover {
            background: rgba(255,255,255,0.3);
        }
        .admin-container {
            max-width: 1400px;
            margin: 30px auto;
            padding: 0 20px;
        }
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        .stat-card {
            background: white;
            border-radius: 12px;
            padding: 25px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        .stat-card h3 {
            margin: 0 0 10px 0;
            font-size: 14px;
            color: #666;
            font-weight: 600;
        }
        .stat-card .value {
            font-size: 36px;
            font-weight: bold;
            color: #333;
        }
        .stat-card.purple { border-left: 4px solid #667eea; }
        .stat-card.blue { border-left: 4px solid #4facfe; }
        .stat-card.green { border-left: 4px solid #43e97b; }
        .stat-card.orange { border-left: 4px solid #fa709a; }
        .tabs {
            background: white;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            overflow: hidden;
        }
        .tab-buttons {
            display: flex;
            border-bottom: 2px solid #f0f0f0;
            background: #fafafa;
        }
        .tab-btn {
            flex: 1;
            padding: 15px 20px;
            border: none;
            background: transparent;
            cursor: pointer;
            font-size: 14px;
            font-weight: 600;
            color: #666;
            transition: all 0.3s;
        }
        .tab-btn.active {
            color: #667eea;
            background: white;
            border-bottom: 3px solid #667eea;
        }
        .tab-content {
            display: none;
            padding: 25px;
        }
        .tab-content.active {
            display: block;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        table th {
            background: #f8f9fa;
            padding: 12px;
            text-align: left;
            font-size: 13px;
            font-weight: 600;
            color: #333;
            border-bottom: 2px solid #e0e0e0;
        }
        table td {
            padding: 12px;
            border-bottom: 1px solid #f0f0f0;
            font-size: 13px;
        }
        table tr:hover {
            background: #f8f9fa;
        }
        .badge {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 12px;
            font-size: 11px;
            font-weight: 600;
        }
        .badge-student { background: #e3f2fd; color: #1976d2; }
        .badge-staff { background: #f3e5f5; color: #7b1fa2; }
        .badge-active { background: #e8f5e9; color: #388e3c; }
        .loading {
            text-align: center;
            padding: 40px;
            color: #999;
        }
    </style>
</head>
<body>
    <nav class="admin-navbar">
        <h1>Admin Panel - Certificate Generator</h1>
        <div class="admin-user">
            <span>Welcome, <strong><?php echo htmlspecialchars($admin_username); ?></strong></span>
            <button class="logout-btn" onclick="logout()">Logout</button>
        </div>
    </nav>

    <div class="admin-container">
        <!-- Stats Cards -->
        <div class="stats-grid">
            <div class="stat-card purple">
                <h3>Total Users</h3>
                <div class="value" id="totalUsers">-</div>
            </div>
            <div class="stat-card blue">
                <h3>Students</h3>
                <div class="value" id="totalStudents">-</div>
            </div>
            <div class="stat-card green">
                <h3>Staff</h3>
                <div class="value" id="totalStaff">-</div>
            </div>
            <div class="stat-card orange">
                <h3>Certificates Generated</h3>
                <div class="value" id="totalCertificates">-</div>
            </div>
        </div>

        <!-- Tabs -->
        <div class="tabs">
            <div class="tab-buttons">
                <button class="tab-btn active" onclick="showTab('users')">Users</button>
                <button class="tab-btn" onclick="showTab('certificates')">Certificates</button>
                <button class="tab-btn" onclick="showTab('activity')">Activity Logs</button>
                <button class="tab-btn" onclick="showTab('sessions')">Active Sessions</button>
            </div>

            <div id="usersTab" class="tab-content active">
                <h3 style="margin-top: 0;">All Users</h3>
                <div id="usersTable" class="loading">Loading...</div>
            </div>

            <div id="certificatesTab" class="tab-content">
                <h3 style="margin-top: 0;">Certificate Generation History</h3>
                <div id="certificatesTable" class="loading">Loading...</div>
            </div>

            <div id="activityTab" class="tab-content">
                <h3 style="margin-top: 0;">User Activity Logs</h3>
                <div id="activityTable" class="loading">Loading...</div>
            </div>

            <div id="sessionsTab" class="tab-content">
                <h3 style="margin-top: 0;">Active User Sessions</h3>
                <div id="sessionsTable" class="loading">Loading...</div>
            </div>
        </div>
    </div>

    <script>
        // Load dashboard stats on page load
        async function loadDashboard() {
            try {
                const response = await fetch('admin_api.php?action=dashboard');
                const data = await response.json();
                
                if (data.success) {
                    document.getElementById('totalUsers').textContent = data.stats.total_users;
                    document.getElementById('totalStudents').textContent = data.stats.total_students;
                    document.getElementById('totalStaff').textContent = data.stats.total_staff;
                    document.getElementById('totalCertificates').textContent = data.stats.total_certificates;
                }
            } catch (error) {
                console.error('Error loading dashboard:', error);
            }
        }

        async function loadUsers() {
            try {
                const response = await fetch('admin_api.php?action=users');
                const data = await response.json();
                
                if (data.success && data.users.length > 0) {
                    let html = '<table><thead><tr><th>ID</th><th>Name</th><th>Type</th><th>Email</th><th>Department</th><th>Created</th></tr></thead><tbody>';
                    data.users.forEach(user => {
                        html += `<tr>
                            <td>${user.id}</td>
                            <td>${user.name}</td>
                            <td><span class="badge badge-${user.user_type}">${user.user_type.toUpperCase()}</span></td>
                            <td>${user.email}</td>
                            <td>${user.department}</td>
                            <td>${new Date(user.created_at).toLocaleDateString()}</td>
                        </tr>`;
                    });
                    html += '</tbody></table>';
                    document.getElementById('usersTable').innerHTML = html;
                } else {
                    document.getElementById('usersTable').innerHTML = '<p>No users found</p>';
                }
            } catch (error) {
                document.getElementById('usersTable').innerHTML = '<p>Error loading users</p>';
            }
        }

        async function loadCertificates() {
            try {
                const response = await fetch('admin_api.php?action=certificates');
                const data = await response.json();
                
                if (data.success && data.certificates.length > 0) {
                    let html = '<table><thead><tr><th>ID</th><th>User</th><th>Type</th><th>Recipient</th><th>Generated</th><th>Count</th></tr></thead><tbody>';
                    data.certificates.forEach(cert => {
                        html += `<tr>
                            <td>${cert.id}</td>
                            <td>${cert.user_name}</td>
                            <td><span class="badge badge-${cert.user_type}">${cert.user_type}</span></td>
                            <td>${cert.recipient_name}</td>
                            <td>${new Date(cert.generated_at).toLocaleString()}</td>
                            <td>${cert.bulk_count}</td>
                        </tr>`;
                    });
                    html += '</tbody></table>';
                    document.getElementById('certificatesTable').innerHTML = html;
                } else {
                    document.getElementById('certificatesTable').innerHTML = '<p>No certificates generated yet</p>';
                }
            } catch (error) {
                document.getElementById('certificatesTable').innerHTML = '<p>Error loading certificates</p>';
            }
        }

        async function loadActivity() {
            try {
                const response = await fetch('admin_api.php?action=activity');
                const data = await response.json();
                
                if (data.success && data.activities.length > 0) {
                    let html = '<table><thead><tr><th>User</th><th>Type</th><th>Activity</th><th>Description</th><th>Time</th></tr></thead><tbody>';
                    data.activities.forEach(activity => {
                        html += `<tr>
                            <td>${activity.user_name}</td>
                            <td><span class="badge badge-${activity.user_type}">${activity.user_type}</span></td>
                            <td>${activity.activity_type}</td>
                            <td>${activity.activity_description}</td>
                            <td>${new Date(activity.created_at).toLocaleString()}</td>
                        </tr>`;
                    });
                    html += '</tbody></table>';
                    document.getElementById('activityTable').innerHTML = html;
                } else {
                    document.getElementById('activityTable').innerHTML = '<p>No activity logs found</p>';
                }
            } catch (error) {
                document.getElementById('activityTable').innerHTML = '<p>Error loading activity logs</p>';
            }
        }

        async function loadSessions() {
            try {
                const response = await fetch('admin_api.php?action=sessions');
                const data = await response.json();
                
                if (data.success && data.sessions.length > 0) {
                    let html = '<table><thead><tr><th>User</th><th>IP Address</th><th>Login Time</th><th>Last Activity</th><th>Status</th></tr></thead><tbody>';
                    data.sessions.forEach(session => {
                        html += `<tr>
                            <td>${session.user_name || 'Unknown'}</td>
                            <td>${session.ip_address}</td>
                            <td>${new Date(session.login_time).toLocaleString()}</td>
                            <td>${new Date(session.last_activity).toLocaleString()}</td>
                            <td><span class="badge badge-active">${session.status}</span></td>
                        </tr>`;
                    });
                    html += '</tbody></table>';
                    document.getElementById('sessionsTable').innerHTML = html;
                } else {
                    document.getElementById('sessionsTable').innerHTML = '<p>No active sessions</p>';
                }
            } catch (error) {
                document.getElementById('sessionsTable').innerHTML = '<p>Error loading sessions</p>';
            }
        }

        function showTab(tab) {
            // Hide all tabs
            document.querySelectorAll('.tab-content').forEach(el => el.classList.remove('active'));
            document.querySelectorAll('.tab-btn').forEach(el => el.classList.remove('active'));
            
            // Show selected tab
            document.getElementById(tab + 'Tab').classList.add('active');
            event.target.classList.add('active');
            
            // Load data for tab
            switch(tab) {
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

        function logout() {
            if (confirm('Are you sure you want to logout?')) {
                window.location.href = '../actions/logout.php';
            }
        }

        // Load initial data
        window.addEventListener('load', () => {
            loadDashboard();
            loadUsers();
        });
    </script>
</body>
</html>

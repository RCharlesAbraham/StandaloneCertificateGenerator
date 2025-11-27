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
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            background: #f5f5f5;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            display: flex;
            min-height: 100vh;
        }
        
        /* Vertical Sidebar */
        .admin-sidebar {
            width: 260px;
            background: linear-gradient(180deg, #67150a 0%, #4a0f07 100%);
            color: white;
            display: flex;
            flex-direction: column;
            position: fixed;
            height: 100vh;
            box-shadow: 2px 0 10px rgba(0,0,0,0.1);
        }
        .sidebar-header {
            padding: 25px 20px;
            border-bottom: 1px solid rgba(255,255,255,0.1);
            text-align: center;
        }
        .sidebar-header h1 {
            font-size: 18px;
            font-weight: 700;
            margin-bottom: 5px;
        }
        .sidebar-header p {
            font-size: 12px;
            opacity: 0.7;
        }
        .sidebar-nav {
            flex: 1;
            padding: 20px 0;
        }
        .nav-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 14px 25px;
            color: rgba(255,255,255,0.8);
            text-decoration: none;
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s;
            border-left: 3px solid transparent;
        }
        .nav-item:hover {
            background: rgba(255,255,255,0.1);
            color: white;
        }
        .nav-item.active {
            background: rgba(255,255,255,0.15);
            color: white;
            border-left-color: #fff;
        }
        .nav-item svg {
            width: 20px;
            height: 20px;
            opacity: 0.9;
        }
        .sidebar-footer {
            padding: 20px;
            border-top: 1px solid rgba(255,255,255,0.1);
        }
        .admin-info {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 15px;
        }
        .admin-avatar {
            width: 40px;
            height: 40px;
            background: rgba(255,255,255,0.2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .admin-avatar svg {
            width: 20px;
            height: 20px;
        }
        .admin-details {
            flex: 1;
        }
        .admin-details strong {
            display: block;
            font-size: 14px;
        }
        .admin-details span {
            font-size: 11px;
            opacity: 0.7;
        }
        .logout-btn {
            width: 100%;
            background: rgba(255,255,255,0.1);
            color: white;
            border: 1px solid rgba(255,255,255,0.2);
            padding: 10px 16px;
            border-radius: 6px;
            cursor: pointer;
            font-size: 13px;
            font-weight: 500;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            transition: all 0.2s;
        }
        .logout-btn:hover {
            background: rgba(255,255,255,0.2);
        }
        
        /* Main Content */
        .admin-main {
            flex: 1;
            margin-left: 260px;
            padding: 30px;
        }
        .page-header {
            margin-bottom: 25px;
        }
        .page-header h2 {
            font-size: 24px;
            color: #333;
            margin-bottom: 5px;
        }
        .page-header p {
            color: #666;
            font-size: 14px;
        }
        
        /* Stats Grid */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        .stat-card {
            background: white;
            border-radius: 12px;
            padding: 22px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.04);
            display: flex;
            align-items: center;
            gap: 18px;
        }
        .stat-icon {
            width: 52px;
            height: 52px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .stat-icon svg {
            width: 26px;
            height: 26px;
        }
        .stat-icon.maroon { background: rgba(103,21,10,0.1); color: #67150a; }
        .stat-icon.blue { background: rgba(59,130,246,0.1); color: #3b82f6; }
        .stat-icon.green { background: rgba(34,197,94,0.1); color: #22c55e; }
        .stat-icon.orange { background: rgba(249,115,22,0.1); color: #f97316; }
        .stat-content h3 {
            font-size: 12px;
            color: #888;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 4px;
        }
        .stat-content .value {
            font-size: 28px;
            font-weight: 700;
            color: #333;
        }
        
        /* Content Card */
        .content-card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.04);
            overflow: hidden;
        }
        .content-header {
            padding: 20px 25px;
            border-bottom: 1px solid #f0f0f0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .content-header h3 {
            font-size: 16px;
            color: #333;
            font-weight: 600;
        }
        .content-body {
            padding: 0;
        }
        
        /* Table Styles */
        table {
            width: 100%;
            border-collapse: collapse;
        }
        table th {
            background: #fafafa;
            padding: 14px 20px;
            text-align: left;
            font-size: 12px;
            font-weight: 600;
            color: #666;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border-bottom: 1px solid #f0f0f0;
        }
        table td {
            padding: 14px 20px;
            border-bottom: 1px solid #f5f5f5;
            font-size: 13px;
            color: #444;
        }
        table tr:hover {
            background: #fafafa;
        }
        table tr:last-child td {
            border-bottom: none;
        }
        
        /* Badges */
        .badge {
            display: inline-block;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }
        .badge-student { background: #e0f2fe; color: #0369a1; }
        .badge-staff { background: #fce7f3; color: #be185d; }
        .badge-active { background: #dcfce7; color: #166534; }
        .badge-admin { background: rgba(103,21,10,0.1); color: #67150a; }
        
        /* Loading & Empty States */
        .loading, .empty-state {
            text-align: center;
            padding: 50px 20px;
            color: #999;
        }
        .empty-state svg {
            width: 48px;
            height: 48px;
            margin-bottom: 15px;
            opacity: 0.3;
        }
        
        /* Tab Content */
        .tab-panel {
            display: none;
        }
        .tab-panel.active {
            display: block;
        }
    </style>
</head>
<body>
    <!-- Vertical Sidebar -->
    <aside class="admin-sidebar">
        <div class="sidebar-header">

        <div class="login-logo-wrapper logo-wrapper-admin" style="text-align:center; margin-bottom:12px;">
                <img src="../assets/MMC-LOGO-1-229x300.png" alt="MCC-MRF Logo" class="login-logo-admin" style=" ">
            </div>
            <h1>MCC Certificate</h1>
            <p>Admin Dashboard</p>
        </div>
        
        <nav class="sidebar-nav">
            <a class="nav-item active" data-tab="dashboard">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <rect x="3" y="3" width="7" height="7"></rect>
                    <rect x="14" y="3" width="7" height="7"></rect>
                    <rect x="14" y="14" width="7" height="7"></rect>
                    <rect x="3" y="14" width="7" height="7"></rect>
                </svg>
                Dashboard
            </a>
            <a class="nav-item" data-tab="users">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                    <circle cx="9" cy="7" r="4"></circle>
                    <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                    <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                </svg>
                Users
            </a>
            <a class="nav-item" data-tab="certificates">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                    <polyline points="14 2 14 8 20 8"></polyline>
                    <line x1="16" y1="13" x2="8" y2="13"></line>
                    <line x1="16" y1="17" x2="8" y2="17"></line>
                </svg>
                Certificates
            </a>
            <a class="nav-item" data-tab="activity">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <polyline points="22 12 18 12 15 21 9 3 6 12 2 12"></polyline>
                </svg>
                Activity Logs
            </a>
            <a class="nav-item" data-tab="sessions">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="12" cy="12" r="10"></circle>
                    <polyline points="12 6 12 12 16 14"></polyline>
                </svg>
                Sessions
            </a>
        </nav>
        
        <div class="sidebar-footer">
            <div class="admin-info">
                <div class="admin-avatar">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                        <circle cx="12" cy="7" r="4"></circle>
                    </svg>
                </div>
                <div class="admin-details">
                    <strong><?php echo htmlspecialchars($admin_username); ?></strong>
                    <span>Administrator</span>
                </div>
            </div>
            <button class="logout-btn" onclick="logout()">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
                    <polyline points="16 17 21 12 16 7"></polyline>
                    <line x1="21" y1="12" x2="9" y2="12"></line>
                </svg>
                Logout
            </button>
        </div>
    </aside>

    <!-- Main Content -->
    <main class="admin-main">
        <!-- Dashboard Tab -->
        <div id="dashboardTab" class="tab-panel active">
            <div class="page-header">
                <h2>Dashboard Overview</h2>
                <p>Welcome back! Here's what's happening with your certificate system.</p>
            </div>
            
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon maroon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                            <circle cx="9" cy="7" r="4"></circle>
                            <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                            <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                        </svg>
                    </div>
                    <div class="stat-content">
                        <h3>Total Users</h3>
                        <div class="value" id="totalUsers">-</div>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon blue">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M22 10v6M2 10l10-5 10 5-10 5z"></path>
                            <path d="M6 12v5c3 3 9 3 12 0v-5"></path>
                        </svg>
                    </div>
                    <div class="stat-content">
                        <h3>Students</h3>
                        <div class="value" id="totalStudents">-</div>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon green">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <rect x="2" y="7" width="20" height="14" rx="2" ry="2"></rect>
                            <path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"></path>
                        </svg>
                    </div>
                    <div class="stat-content">
                        <h3>Staff</h3>
                        <div class="value" id="totalStaff">-</div>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon orange">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                            <polyline points="14 2 14 8 20 8"></polyline>
                            <line x1="16" y1="13" x2="8" y2="13"></line>
                            <line x1="16" y1="17" x2="8" y2="17"></line>
                        </svg>
                    </div>
                    <div class="stat-content">
                        <h3>Certificates</h3>
                        <div class="value" id="totalCertificates">-</div>
                    </div>
                </div>
            </div>
            
            <div class="content-card">
                <div class="content-header">
                    <h3>Recent Users</h3>
                </div>
                <div class="content-body" id="recentUsersTable">
                    <div class="loading">Loading...</div>
                </div>
            </div>
        </div>

        <!-- Users Tab -->
        <div id="usersTab" class="tab-panel">
            <div class="page-header">
                <h2>User Management</h2>
                <p>View and manage all registered users in the system.</p>
            </div>
            <div class="content-card">
                <div class="content-header">
                    <h3>All Users</h3>
                </div>
                <div class="content-body" id="usersTable">
                    <div class="loading">Loading...</div>
                </div>
            </div>
        </div>

        <!-- Certificates Tab -->
        <div id="certificatesTab" class="tab-panel">
            <div class="page-header">
                <h2>Certificate History</h2>
                <p>Track all certificates generated through the system.</p>
            </div>
            <div class="content-card">
                <div class="content-header">
                    <h3>Generated Certificates</h3>
                </div>
                <div class="content-body" id="certificatesTable">
                    <div class="loading">Loading...</div>
                </div>
            </div>
        </div>

        <!-- Activity Tab -->
        <div id="activityTab" class="tab-panel">
            <div class="page-header">
                <h2>Activity Logs</h2>
                <p>Monitor user activities and system events.</p>
            </div>
            <div class="content-card">
                <div class="content-header">
                    <h3>Recent Activity</h3>
                </div>
                <div class="content-body" id="activityTable">
                    <div class="loading">Loading...</div>
                </div>
            </div>
        </div>

        <!-- Sessions Tab -->
        <div id="sessionsTab" class="tab-panel">
            <div class="page-header">
                <h2>Active Sessions</h2>
                <p>View currently active user sessions.</p>
            </div>
            <div class="content-card">
                <div class="content-header">
                    <h3>Current Sessions</h3>
                </div>
                <div class="content-body" id="sessionsTable">
                    <div class="loading">Loading...</div>
                </div>
            </div>
        </div>
    </main>

    <script>
        // Tab Navigation
        document.querySelectorAll('.nav-item').forEach(item => {
            item.addEventListener('click', function() {
                const tab = this.dataset.tab;
                
                // Update nav items
                document.querySelectorAll('.nav-item').forEach(el => el.classList.remove('active'));
                this.classList.add('active');
                
                // Update tab panels
                document.querySelectorAll('.tab-panel').forEach(el => el.classList.remove('active'));
                document.getElementById(tab + 'Tab').classList.add('active');
                
                // Load data for tab
                switch(tab) {
                    case 'dashboard':
                        loadDashboard();
                        loadRecentUsers();
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
            });
        });

        // Load dashboard stats
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

        async function loadRecentUsers() {
            try {
                const response = await fetch('admin_api.php?action=users&limit=5');
                const data = await response.json();
                
                if (data.success && data.users.length > 0) {
                    let html = '<table><thead><tr><th>Name</th><th>Type</th><th>Email</th><th>Joined</th></tr></thead><tbody>';
                    data.users.slice(0, 5).forEach(user => {
                        html += `<tr>
                            <td><strong>${user.name}</strong></td>
                            <td><span class="badge badge-${user.user_type}">${user.user_type}</span></td>
                            <td>${user.email}</td>
                            <td>${new Date(user.created_at).toLocaleDateString()}</td>
                        </tr>`;
                    });
                    html += '</tbody></table>';
                    document.getElementById('recentUsersTable').innerHTML = html;
                } else {
                    document.getElementById('recentUsersTable').innerHTML = '<div class="empty-state">No users found</div>';
                }
            } catch (error) {
                document.getElementById('recentUsersTable').innerHTML = '<div class="empty-state">Error loading users</div>';
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
                            <td>#${user.id}</td>
                            <td><strong>${user.name}</strong></td>
                            <td><span class="badge badge-${user.user_type}">${user.user_type}</span></td>
                            <td>${user.email}</td>
                            <td>${user.department || '-'}</td>
                            <td>${new Date(user.created_at).toLocaleDateString()}</td>
                        </tr>`;
                    });
                    html += '</tbody></table>';
                    document.getElementById('usersTable').innerHTML = html;
                } else {
                    document.getElementById('usersTable').innerHTML = '<div class="empty-state">No users found</div>';
                }
            } catch (error) {
                document.getElementById('usersTable').innerHTML = '<div class="empty-state">Error loading users</div>';
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
                            <td>#${cert.id}</td>
                            <td><strong>${cert.user_name}</strong></td>
                            <td><span class="badge badge-${cert.user_type}">${cert.user_type}</span></td>
                            <td>${cert.recipient_name}</td>
                            <td>${new Date(cert.created_at).toLocaleString()}</td>
                            <td>${cert.bulk_count}</td>
                        </tr>`;
                    });
                    html += '</tbody></table>';
                    document.getElementById('certificatesTable').innerHTML = html;
                } else {
                    document.getElementById('certificatesTable').innerHTML = '<div class="empty-state">No certificates generated yet</div>';
                }
            } catch (error) {
                document.getElementById('certificatesTable').innerHTML = '<div class="empty-state">Error loading certificates</div>';
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
                            <td><strong>${activity.user_name}</strong></td>
                            <td><span class="badge badge-${activity.user_type}">${activity.user_type}</span></td>
                            <td>${activity.activity_type}</td>
                            <td>${activity.activity_description}</td>
                            <td>${new Date(activity.created_at).toLocaleString()}</td>
                        </tr>`;
                    });
                    html += '</tbody></table>';
                    document.getElementById('activityTable').innerHTML = html;
                } else {
                    document.getElementById('activityTable').innerHTML = '<div class="empty-state">No activity logs found</div>';
                }
            } catch (error) {
                document.getElementById('activityTable').innerHTML = '<div class="empty-state">Error loading activity logs</div>';
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
                            <td><strong>${session.user_name || 'Unknown'}</strong></td>
                            <td><code>${session.ip_address}</code></td>
                            <td>${new Date(session.login_time).toLocaleString()}</td>
                            <td>${new Date(session.last_activity).toLocaleString()}</td>
                            <td><span class="badge badge-active">${session.status}</span></td>
                        </tr>`;
                    });
                    html += '</tbody></table>';
                    document.getElementById('sessionsTable').innerHTML = html;
                } else {
                    document.getElementById('sessionsTable').innerHTML = '<div class="empty-state">No active sessions</div>';
                }
            } catch (error) {
                document.getElementById('sessionsTable').innerHTML = '<div class="empty-state">Error loading sessions</div>';
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
            loadRecentUsers();
        });
    </script>
</body>
</html>

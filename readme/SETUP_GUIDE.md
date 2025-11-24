# Certificate Generator - Complete Setup Guide

## System Overview

This is a complete web-based certificate generator with user management, admin panel, and database logging. The system tracks all users and certificate generation activities.

## Installation Steps

### 1. Install Prerequisites

- **XAMPP** (recommended) or **WAMP** - Download from https://www.apachefriends.org/
- Includes: Apache, MySQL, PHP
- Or use: PHP 7.4+, MySQL 5.7+, Apache/Nginx

### 2. Setup Database

1. **Start MySQL** via XAMPP Control Panel
2. **Open phpMyAdmin** (http://localhost/phpmyadmin)
3. **Import Database:**
   - Click "Import" tab
   - Choose file: `database_schema.sql`
   - Click "Go"
   - This creates the `certificate_generator` database with all tables

### 3. Configure Database Connection

Edit `config.php` if needed:
```php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');  // Default XAMPP has no password
define('DB_NAME', 'certificate_generator');
```

### 4. Start Server

1. **Start Apache** in XAMPP Control Panel
2. **Start MySQL** in XAMPP Control Panel
3. **Copy project files** to `C:\xampp\htdocs\StandaloneCertificateGenerator\`
4. **Access application**: http://localhost/StandaloneCertificateGenerator/

## Default Credentials

### Admin Panel
- **URL:** http://localhost/StandaloneCertificateGenerator/admin_login.html
- **Username:** `Admin@MCC`
- **Password:** `Admin123`

⚠️ **Change admin password after first login!**

## User Workflow

### For New Users (Students/Staff)

1. **Go to:** http://localhost/StandaloneCertificateGenerator/register.html
2. **Select User Type:** Student or Staff
3. **Fill Registration Form:**
   - Student needs: Reg No, Name, Department, Phone, College, Email, Password
   - Staff needs: Name, Designation, Department, Phone, College, Email, Password
4. **Submit** → Account created
5. **Go to Login** → http://localhost/StandaloneCertificateGenerator/login.html
6. **Enter credentials** → Access certificate generator

### Using Certificate Generator

1. **Single Certificate:**
   - Fill in form fields (Name, Cert No, Dates, etc.)
   - Upload signature images
   - Click "Generate PDF"
   - Certificate saved + logged to database

2. **Bulk Certificates:**
   - Upload Excel file with data
   - Click "Generate All PDFs"
   - Creates ZIP file with all certificates
   - All logged to database

### Excel Format for Bulk Generation

| Column | Description | Example |
|--------|-------------|---------|
| A (0) | Certificate No | MCC-001 |
| B (1) | Name | John Doe |
| C (2) | Certified For | Python Developer |
| D (3) | From Date | 06/01/2024 |
| E (4) | To Date | 07/01/2024 |
| F (5) | Sig 1 Name | Dr. Smith |
| G (6) | Sig 1 Designation | Director |
| H (7) | Sig 1 College | MCC |
| I (8) | Sig 2 Name | Prof. Jones |
| J (9) | Sig 2 Designation | HOD |
| K (10) | Sig 2 College | MCC |
| L (11) | Sig 3 Name | Dr. Wilson |
| M (12) | Sig 3 Designation | Principal |
| N (13) | Sig 3 College | MCC |

## Admin Panel Features

### Dashboard
- Total users count
- Students count
- Staff count
- Total certificates generated
- Recent activity feed

### All Users
- View all registered users
- Filter by user type (Student/Staff)
- Search by name, email, or reg no
- See registration date, status

### Certificate Logs
- Who generated certificates
- When they were generated
- Single vs Bulk generation
- Certificate details
- IP addresses

### Activity Logs
- User login/logout
- Account creation
- Certificate generation
- All user actions
- IP tracking

### Active Sessions
- Currently logged-in users
- Session start time
- Last activity time
- IP addresses
- User agents

## Database Structure

### Tables Created

1. **users** - All registered users (students & staff)
2. **admins** - Admin accounts
3. **certificate_logs** - All certificate generations
4. **activity_logs** - User activity tracking
5. **admin_logs** - Admin activity tracking
6. **user_sessions** - Active user sessions

## Security Features

✅ **Password Hashing** - bcrypt encryption
✅ **SQL Injection Protection** - Prepared statements
✅ **Session Management** - Secure session tokens
✅ **Activity Logging** - IP addresses tracked
✅ **Status Control** - Active/inactive accounts
✅ **Admin Authentication** - Separate admin login

## File Structure

```
/
├── public/                    # Public document root
│   ├── index.php              # Certificate generator (main app)
│   ├── login.php              # User login page
│   ├── register.php           # User registration page
│   ├── styles.css
│   ├── script.js              # Frontend JavaScript
│   ├── assets/                # Images and assets
│   └── actions/               # API actions
│       ├── login_process.php
│       ├── register_process.php
│       ├── logout.php
│       └── log_certificate.php
├── public/admin/              # Admin panel and APIs
│   ├── login.php
│   ├── panel.php
│   └── admin_api.php
├── includes/                  # Shared PHP includes
│   └── config.php             # Database configuration and helpers
├── db/                        # Database schema and sample data
├── docs/                      # Documentation
└── legacy_backup/             # Backups of original root files
```

## Troubleshooting

### "Connection failed" Error
- Check MySQL is running in XAMPP
- Verify database credentials in `config.php`
- Ensure database `certificate_generator` exists

### "Unauthorized" Error
- User not logged in
- Session expired → Login again
- Check browser console for errors

### Admin Panel Not Loading
- Check admin is logged in
- Verify `public/admin/admin_api.php` is accessible
- Check PHP error logs in XAMPP

### Certificates Not Logging
- Check `public/actions/log_certificate.php` is accessible
- Verify user session is active
- Check browser console for fetch errors
- Check PHP error logs

### Registration Not Working
- Verify all required fields filled
- Check email not already registered
- Check reg no unique (for students)
- Verify database tables exist

## API Endpoints

### User APIs
- **POST** `/public/actions/login_process.php` - User login
- **POST** `/public/actions/register_process.php` - User registration
- **POST** `/public/actions/logout.php` - User logout
- **POST** `/public/actions/log_certificate.php` - Log certificate generation

### Admin APIs
- **POST** `/public/admin/admin_login_process.php` - Admin login
- **GET** `/public/admin/admin_api.php?action=dashboard` - Dashboard stats
- **GET** `/public/admin/admin_api.php?action=users` - All users
- **GET** `/public/admin/admin_api.php?action=certificates` - Certificate logs
- **GET** `/public/admin/admin_api.php?action=activity` - Activity logs
- **GET** `/public/admin/admin_api.php?action=sessions` - Active sessions

## Change Admin Password

To change the default admin password:

1. **Generate new hash:**
```php
<?php
echo password_hash('YourNewPassword', PASSWORD_DEFAULT);
?>
```

2. **Update database:**
```sql
UPDATE admins 
SET password = 'paste_hash_here' 
WHERE username = 'Admin@MCC';
```

## Backup & Maintenance

### Database Backup
1. Open phpMyAdmin
2. Select `certificate_generator` database
3. Click "Export" tab
4. Choose "Quick" export method
5. Click "Go"
6. Save .sql file

### Clear Old Sessions
```sql
DELETE FROM user_sessions 
WHERE status = 'logged_out' 
AND logout_time < DATE_SUB(NOW(), INTERVAL 30 DAY);
```

### View Statistics
```sql
-- Total users
SELECT COUNT(*) FROM users;

-- Total certificates
SELECT SUM(bulk_count) FROM certificate_logs;

-- Most active users
SELECT u.name, COUNT(*) as cert_count 
FROM certificate_logs cl
JOIN users u ON cl.user_id = u.id
GROUP BY u.id
ORDER BY cert_count DESC
LIMIT 10;
```

## Support & Development

For issues or enhancements:
1. Check this documentation
2. Review PHP error logs in XAMPP
3. Check browser console for JavaScript errors
4. Verify database connections
5. Test with default admin account

## Production Deployment

When deploying to production:

1. **Update config.php** with production credentials
2. **Change admin password** immediately
3. **Enable HTTPS** for security
4. **Set proper file permissions**
5. **Configure firewall rules**
6. **Setup automatic backups**
7. **Enable error logging** (not display)
8. **Update BASE_URL** in config.php

## License & Credits

Certificate Generator System
Madras Christian College
Developed for internal use

---

**Last Updated:** November 2025
**Version:** 1.0

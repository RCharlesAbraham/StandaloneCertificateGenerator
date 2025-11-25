## Troubleshooting

### Admin Panel Not Loading Data
 - Verify `public/admin/admin_api.php` is accessible
 - Check PHP session is working
 - Ensure admin is logged in
# Certificate Generator - Backend Setup Guide

## Requirements
- PHP 7.4 or higher
- MySQL 5.7 or higher
- Apache/Nginx web server
- XAMPP/WAMP (for local development)

## Installation Steps

### 1. Database Setup

1. Open phpMyAdmin or MySQL command line
2. Run the SQL script from `database_schema.sql` to create the database and tables
3. This will create:
   - Database: `certificate_generator`
   - Tables: users, admins, certificate_logs, activity_logs, admin_logs, user_sessions
   - Default admin account

### 2. Configuration

1. Open `config.php`
2. Update database credentials if needed:
   ```php
   define('DB_HOST', 'localhost');
   define('DB_USER', 'root');
   define('DB_PASS', '');
   define('DB_NAME', 'certificate_generator');
   ```

### 3. File Permissions

Ensure the following files are readable by the web server:
- config.php
- All PHP files (*.php)

## Default Admin Credentials

**Username:** `Admin@MCC`  
**Password:** `Admin123`

⚠️ **IMPORTANT:** Change the default admin password after first login!

## Features

### User Registration
- **Student Registration (public UI):**
  - Registration Number (`reg_no`)
  - Name
  - Department
  - Phone Number
  - Email
  - Password

> Note: Public registration is student-only. Staff accounts should be managed by an admin. The `college` and `designation` fields are no longer part of public registration.

### Admin Panel Features
- Dashboard with statistics
- View all users (students and staff)
- Monitor certificate generation logs
- Track user activity
  - Account creation
  - Login/logout
  - Certificate generation
- View active user sessions
- Search and filter users

### Certificate Logging
The system automatically logs:
- Who generated certificates
- When certificates were generated
- Type of generation (single/bulk)
- Number of certificates generated
- Recipient details
- IP addresses

## File Structure

```
/
├── public/
│   ├── index.php                 # Certificate generator (main app)
│   ├── login.php                 # User login page
│   ├── register.php              # User registration page
│   ├── script.js                 # Frontend scripts
│   └── actions/
│       ├── login_process.php
│       ├── register_process.php
│       ├── logout.php
│       └── log_certificate.php
├── public/admin/
│   ├── login.php
│   ├── panel.php
│   └── admin_api.php
├── includes/
│   └── config.php                # Database configuration
├── db/                           # Database structure
└── legacy_backup/                 # Backup of original root files
```
```

## API Endpoints

-- **URL:** `/public/actions/register_process.php`
-- **Method:** POST
-- **Parameters:** name, department, phone_no, email, password, reg_no (students only)

> Implementation note: The backend will derive a user's role by checking `reg_no` (if present -> student). `user_type` and `college` are no longer used in DB inserts for public registration.

- **URL:** `/public/admin/admin_login_process.php`
- **Method:** POST
- **Parameters:** username, password

- **URL:** `/public/admin/admin_api.php?action={action}`
- **Method:** GET
- **Actions:**
  - `dashboard` - Get statistics and recent activity
  - `users` - Get all users (with optional search/filter)
  - `certificates` - Get certificate generation logs
  - `activity` - Get user activity logs
  - `sessions` - Get active user sessions

## Security Notes

1. **Password Hashing:** All passwords are hashed using PHP's `password_hash()` with bcrypt
2. **SQL Injection Prevention:** All queries use prepared statements
3. **Session Management:** Admin sessions are tracked and validated
4. **IP Logging:** All activities are logged with IP addresses for auditing

## Testing

### Test User Registration:
1. Go to `register.html`
2. Select Student or Staff
3. Fill in all required fields
4. Click "Create Account"
5. Should redirect to login page on success

### Test Admin Login:
1. Go to `admin_login.html`
2. Enter: Username: `Admin@MCC`, Password: `Admin123`
3. Should redirect to admin panel on success

### Test Admin Panel:
1. After logging in, view dashboard statistics
2. Navigate through different sections:
   - All Users
   - Certificate Logs
   - Activity Logs
   - Active Sessions

## Troubleshooting

### Database Connection Error
- Check MySQL is running
- Verify database credentials in `config.php`
- Ensure database exists (run `database_schema.sql`)

### Registration Not Working
- Check PHP error logs
- Verify all required fields are filled
- Ensure database tables exist
- Check file permissions

### Admin Panel Not Loading Data
- Check browser console for JavaScript errors
- Verify `admin_api.php` is accessible
- Check PHP session is working
- Ensure admin is logged in

## Next Steps

To integrate with the existing certificate generator:

1. Update `login.html` to authenticate against the database
2. Store user session after login
3. Log certificate generation in `script.js`:
  ```javascript
  // After generating certificate
  fetch('/public/actions/log_certificate.php', {
       method: 'POST',
       body: JSON.stringify({
           certificate_no: certNo,
           recipient_name: name,
           certified_for: certifiedFor,
           generation_type: 'single'
       })
   });
   ```

4. Create `log_certificate.php` to insert into `certificate_logs` table

## Support

For issues or questions, contact the system administrator.

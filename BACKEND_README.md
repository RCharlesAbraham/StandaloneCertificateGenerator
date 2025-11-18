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
- **Student Registration:**
  - Registration Number
  - Name
  - Department
  - Phone Number
  - College (Default: Madras Christian College or Other)
  - Email
  - Password

- **Staff Registration:**
  - Name
  - Designation
  - Department
  - Phone Number
  - College (Default: Madras Christian College or Other)
  - Email
  - Password

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
├── config.php                    # Database configuration
├── database_schema.sql          # Database structure
├── register.html                # User registration page
├── register_process.php         # Registration handler
├── admin_login.html             # Admin login page
├── admin_login_process.php      # Admin authentication
├── admin_panel.html             # Admin dashboard
├── admin_api.php                # Admin panel API endpoints
├── index.html                   # Certificate generator (existing)
├── login.html                   # User login (existing)
└── script.js                    # Frontend scripts (existing)
```

## API Endpoints

### Registration
- **URL:** `/register_process.php`
- **Method:** POST
- **Parameters:** user_type, name, department, phone_no, email, password, college_type, other_college, reg_no (students), designation (staff)

### Admin Login
- **URL:** `/admin_login_process.php`
- **Method:** POST
- **Parameters:** username, password

### Admin API
- **URL:** `/admin_api.php?action={action}`
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
   fetch('log_certificate.php', {
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

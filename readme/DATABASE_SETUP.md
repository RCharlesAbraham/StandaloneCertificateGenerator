# Database Setup Verification Guide

## Quick Test

1. **Start XAMPP** (Apache + MySQL)
2. **Open in browser**: `http://localhost:3000/test_database.php`
3. This will check:
   - Database connection
   - All required tables
   - Default admin account
   - PHP sessions
   - File permissions

## Manual Database Setup (If Needed)

### Step 1: Create Database
```sql
CREATE DATABASE IF NOT EXISTS certificate_generator;
```

### Step 2: Import Schema
1. Open phpMyAdmin: `http://localhost/phpmyadmin`
2. Select database: `certificate_generator`
3. Click "Import" tab
4. Choose file: `db/database_schema.sql`
5. Click "Go"

### Step 3: Verify Tables
The following tables should exist:
- ✓ `users` - Student and staff accounts
- ✓ `admins` - Admin accounts
- ✓ `certificate_logs` - Certificate generation history
- ✓ `activity_logs` - User activity tracking
- ✓ `admin_logs` - Admin activity tracking
- ✓ `user_sessions` - Active session management

## Default Credentials

### Admin Account
- **Username**: `Admin@MCC`
- **Password**: `Admin123`
-- **Login URL**: `http://localhost:3000/public/admin/login.php`

### Test User (Create via Registration)
1. Go to: `http://localhost:3000/public/register.php`
2. Fill in the form (Student or Staff)
3. Login at: `http://localhost:3000/public/login.php`

## Database Configuration

Check `config.php` settings:
```php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');        // Default XAMPP user
define('DB_PASS', '');            // Default XAMPP password (empty)
define('DB_NAME', 'certificate_generator');
```

## Session Variables

### User Session (after login)
- `$_SESSION['user_id']` - User ID
- `$_SESSION['user_type']` - 'student' or 'staff'
- `$_SESSION['user_name']` - Full name
- `$_SESSION['user_email']` - Email address
- `$_SESSION['session_token']` - Session tracking token

### Admin Session (after admin login)
- `$_SESSION['admin_id']` - Admin ID
- `$_SESSION['admin_username']` - Admin username

## Common Issues & Solutions

### Issue 1: Database Connection Failed
**Error**: "Connection failed: Access denied"
**Solution**: Check MySQL is running in XAMPP and credentials in `config.php`

### Issue 2: Tables Not Found
**Error**: "Table 'certificate_generator.users' doesn't exist"
**Solution**: Import `db/database_schema.sql` via phpMyAdmin

### Issue 3: Admin Login Fails
**Error**: "Invalid username or password"
**Solution**: 
1. Check if admin exists: 
   ```sql
   SELECT * FROM admins WHERE username = 'Admin@MCC';
   ```
2. If not found, run the INSERT statement from schema file

### Issue 4: Session Not Working
**Error**: User redirected to login after successful login
**Solution**: 
1. Check PHP session in `config.php` (session_start() is called)
2. Verify `index.php` uses correct session keys (`$_SESSION['user_name']` not `$_SESSION['name']`)

## File Structure Verification

Required PHP files:
```
✓ config.php              - Database configuration
✓ public/login.php               - User login page
✓ public/register.php            - User registration page
✓ index.php               - Main certificate generator
✓ public/admin/login.php         - Admin login page
✓ public/admin/panel.php         - Admin dashboard
✓ public/actions/login_process.php       - Login handler
✓ public/actions/register_process.php    - Registration handler
✓ public/actions/logout.php              - Logout handler
✓ public/actions/log_certificate.php     - Certificate logging
✓ public/admin/admin_login_process.php - Admin login handler
✓ public/admin/admin_api.php           - Admin API endpoints
✓ test_database.php       - Database test script
```

## Testing Workflow

### 1. Test Database Connection
```
http://localhost:3000/test_database.php
```

### 2. Test User Registration
```
http://localhost:3000/register.php
→ Fill form → Submit
→ Should redirect to login.php
```

### 3. Test User Login
```
http://localhost:3000/login.php
→ Enter email/password → Submit
→ Should redirect to index.php
```

### 4. Test Certificate Generation
```
http://localhost:3000/index.php
→ Fill certificate details → Generate PDF
→ Check admin panel for logs
```

### 5. Test Admin Login
```
http://localhost:3000/admin_login.php
→ Enter Admin@MCC / Admin123
→ Should redirect to admin_panel.php
```

### 6. Test Admin Panel
```
http://localhost:3000/admin_panel.php
→ View dashboard statistics
→ Check user list
→ View certificate logs
→ Monitor activity
```

## Security Checklist

- ✓ Passwords are hashed using bcrypt (password_hash)
- ✓ SQL injection prevention (prepared statements)
- ✓ Session tokens for tracking
- ✓ IP address logging for audit
- ✓ CSRF protection via session validation
- ✓ Input validation on all forms

## Success Indicators

When everything is working correctly:

1. ✅ test_database.php shows all green checks
2. ✅ Can create new user account
3. ✅ Can login with created account
4. ✅ Can generate certificates
5. ✅ Can login as admin
6. ✅ Admin panel shows users and activity
7. ✅ Logout works and redirects to login

## Next Steps After Setup

1. **Change default admin password** in database:
   ```sql
   UPDATE admins SET password = '$2y$10$YOUR_NEW_HASH' WHERE username = 'Admin@MCC';
   ```
   Generate hash in PHP: `password_hash('YourNewPassword', PASSWORD_DEFAULT);`

2. **Configure for production** (if deploying):
   - Update `config.php` with production database credentials
   - Change `BASE_URL` in `config.php`
   - Enable HTTPS
   - Set proper file permissions

3. **Backup database regularly**:
   ```bash
   mysqldump -u root certificate_generator > backup.sql
   ```

## Support

If issues persist after following this guide:
1. Check Apache error logs in XAMPP
2. Enable PHP error reporting temporarily
3. Check browser console for JavaScript errors
4. Verify all file paths are correct

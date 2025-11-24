# PHP Conversion Complete

All HTML files have been converted to PHP with proper session management and authentication checks.

## New PHP Files Created

### 1. **login.php**
- Replaces: login.html
- Features: 
  - PHP session check (redirects to index.php if already logged in)
  - Email/password authentication
  - "Sign up here" link to register.php
  - Session storage integration

### 2. **register.php**
- Replaces: register.html
- Features:
  - PHP session check (redirects to index.php if already logged in)
  - Student/Staff registration form
  - Links to login.php after registration

### 3. **index.php**
- Replaces: index.html
- Features:
  - PHP authentication check (redirects to login.php if not logged in)
  - Loads user data from PHP session (name, email, user_type)
  - Sets sessionStorage from PHP session for JavaScript access
  - Full certificate generator interface

### 4. **admin_login.php**
- Replaces: admin_login.html
- Features:
  - PHP admin session check (redirects to admin_panel.php if already logged in)
  - Admin authentication form
  - Links to index.php

### 5. **admin_panel.php**
- Replaces: admin_panel.html
- Features:
  - PHP admin authentication check (redirects to admin_login.php if not logged in)
  - Displays admin username from PHP session
  - Full admin dashboard with user management

## Updated Files

### script.js
- All redirects updated to use .php extensions:
  - `login.html` → `login.php`
  - `index.html` → `index.php`
  - `register.html` → `register.php`
- Page detection updated to check for .php files

## How to Use

### For Users:
1. Access the application via: `login.php`
2. New users can click "Sign up here" to go to `register.php`
3. After login, automatically redirected to `index.php`
4. Generate certificates with full database logging

### For Admins:
1. Access admin panel via: `admin_login.php`
2. Default credentials: Admin@MCC / Admin123
3. Monitor all users, certificates, and activity

### First Time Setup:
1. Install XAMPP (Apache + MySQL)
2. Import `db/database_schema.sql` into MySQL
3. Update `config.php` with database credentials
4. Start Apache and MySQL services
5. Access via: `http://localhost/StandaloneCertificateGenerator/login.php`

## File Structure
```
StandaloneCertificateGenerator/
├── login.php               # User login (NEW)
├── register.php            # User registration (NEW)
├── index.php              # Certificate generator (NEW)
├── admin_login.php        # Admin login (NEW)
├── admin_panel.php        # Admin dashboard (NEW)
├── login_process.php      # Login handler (existing)
├── register_process.php   # Registration handler (existing)
├── logout.php             # Logout handler (existing)
├── log_certificate.php    # Certificate logging (existing)
├── admin_login_process.php # Admin login handler (existing)
├── admin_api.php          # Admin API (existing)
├── config.php             # Database config (existing)
├── script.js              # Updated with .php redirects
├── styles.css
└── db/
    └── database_schema.sql
```

## Security Features
- ✅ PHP session-based authentication
- ✅ Server-side session validation
- ✅ Password hashing (bcrypt)
- ✅ SQL injection prevention (prepared statements)
- ✅ Session token tracking
- ✅ IP address logging
- ✅ Activity audit trails

## Notes
- Old HTML files (login.html, register.html, index.html, etc.) can be deleted
- All functionality now runs through PHP with proper authentication
- Database integration is complete
- Sign-up link is available on login.php

# 📦 DEPLOYMENT GUIDE - OPTION B (Folder Structure Intact)

## 🎯 Overview
This guide helps you deploy the Certificate Generator while keeping the folder structure with `public/` subdirectory.

---

## 📁 Upload Structure to InfinityFree

Upload your entire project to `htdocs/`:

```
htdocs/
├── .htaccess                    ← Root redirect rules
├── index.php                    ← Redirects to public/
├── public/
│   ├── .htaccess               ← Public directory rules
│   ├── index.php               ← Main application
│   ├── login.php
│   ├── register.php
│   ├── styles.css
│   ├── script.js
│   ├── config.json
│   ├── actions/
│   │   ├── login_process.php
│   │   ├── register_process.php
│   │   ├── logout.php
│   │   └── log_certificate.php
│   ├── admin/
│   │   ├── login.php
│   │   ├── panel.php
│   │   ├── admin_api.php
│   │   └── check_admin.php
│   ├── assets/
│   │   ├── MainPlaceholderCertificate.jpg
│   │   ├── MMC-LOGO-2-229x300.jpg
│   │   └── signature-images/
│   │       ├── Frank.png
│   │       ├── Aarthi.png
│   │       └── Wilson.png
│   ├── debug/
│   │   └── databaseconnectioncheck.php
│   ├── test_css.php
│   └── css_diagnostic.html
├── includes/
│   └── config.php              ← Database configuration
├── db/
│   ├── database_schema.sql
│   └── database_schema_hosting.sql
└── readme/
    └── (documentation files)
```

---

## 🔧 Configuration Files

### 1. Root `.htaccess` (already created)
- Redirects root domain to `public/`
- Makes `yourdomain.com` work like `yourdomain.com/public/`
- Protects `includes/` directory from direct access

### 2. Public `.htaccess` (already created)
- Sets correct MIME types for CSS/JS
- Enables compression
- Handles caching

### 3. `includes/config.php` (already configured)
```php
define('DB_HOST', 'sql100.infinityfree.com');
define('DB_USER', 'if0_40495407');
define('DB_PASS', 'Chab2000');
define('DB_NAME', 'if0_40495407_certificatedb');
```

---

## 🌐 URL Structure

With Option B, your URLs will work as:

| Action | URL |
|--------|-----|
| Home | `http://yourdomain.com/` → redirects to `public/` |
| Register | `http://yourdomain.com/public/register.php` |
| Login | `http://yourdomain.com/public/login.php` |
| Admin | `http://yourdomain.com/public/admin/login.php` |
| CSS Test | `http://yourdomain.com/public/test_css.php` |
| DB Check | `http://yourdomain.com/public/debug/databaseconnectioncheck.php` |

**Note:** With the `.htaccess` rules, some files can be accessed without `/public/` in the URL.

---

## 📤 Upload Steps

### Using FileZilla (Recommended):

1. **Connect to InfinityFree:**
   - Host: `ftpupload.net` or your FTP hostname
   - Username: Your FTP username
   - Password: Your FTP password
   - Port: 21

2. **Navigate to `htdocs/`** on the remote server

3. **Upload ALL files and folders:**
   - Drag entire project to `htdocs/`
   - Ensure folder structure is maintained
   - Upload in **Binary** mode (FileZilla default)

4. **Verify upload:**
   - Check `htdocs/.htaccess` exists
   - Check `htdocs/public/` folder exists
   - Check `htdocs/includes/config.php` exists

### Using cPanel File Manager:

1. Login to InfinityFree control panel
2. Open **File Manager**
3. Navigate to `htdocs/`
4. Click **Upload**
5. Select all files/folders
6. Wait for upload to complete
7. Verify structure matches above

---

## 🗄️ Database Setup

1. **Import Schema:**
   - Open phpMyAdmin from cPanel
   - Select database: `if0_40495407_certificatedb`
   - Click **Import** tab
   - Choose file: `db/database_schema_hosting.sql`
   - Click **Go**

2. **Verify Tables Created:**
   ```
   ✅ users (with stream, level, program_id)
   ✅ admins
   ✅ certificate_logs
   ✅ activity_logs
   ✅ admin_logs
   ✅ user_sessions
   ```

3. **Default Credentials:**
   - Admin: `Admin@MCC` / `Admin123`
   - Demo Student: `john.doe@student.mcc.edu` / Password
   - Demo Staff: `jane.smith@staff.mcc.edu` / Password

---

## ✅ Testing Checklist

After uploading, test these pages in order:

### 1. Database Connection
Visit: `http://yourdomain.com/public/debug/databaseconnectioncheck.php`
- ✅ All tests should pass
- ✅ All 6 tables should show as "EXISTS"
- ✅ User statistics should display

### 2. CSS Loading
Visit: `http://yourdomain.com/public/css_diagnostic.html`
- ✅ Should show "CSS Loaded Successfully"
- ✅ Test button should have styling
- ✅ Visual test should pass

### 3. Registration System
Visit: `http://yourdomain.com/public/register.php`
- ✅ Page loads with proper styling
- ✅ Radio buttons for Stream/Level work
- ✅ Department dropdown populates based on selections
- ✅ Can create account successfully

### 4. Login System
Visit: `http://yourdomain.com/public/login.php`
- ✅ Login page styled correctly
- ✅ Can login with created account
- ✅ Redirects to main certificate generator

### 5. Certificate Generator
After login:
- ✅ Main app loads with full UI
- ✅ Can enter certificate details
- ✅ Canvas displays template
- ✅ Can generate single PDF
- ✅ Can import Excel for bulk generation

### 6. Admin Panel
Visit: `http://yourdomain.com/public/admin/login.php`
- ✅ Admin login page loads
- ✅ Can login with Admin@MCC / Admin123
- ✅ Dashboard shows statistics

---

## 🐛 Common Issues & Fixes

### Issue 1: CSS Not Loading
**Symptoms:** Pages show unstyled HTML
**Fix:**
1. Check `public/styles.css` uploaded correctly
2. Visit `http://yourdomain.com/public/styles.css` directly
3. Should show CSS code, not 404
4. Clear browser cache (Ctrl+F5)
5. Check file permissions (should be 644)

### Issue 2: Database Connection Failed
**Symptoms:** "Database Connection Failed" on check page
**Fix:**
1. Verify `includes/config.php` has correct credentials
2. Check database exists in phpMyAdmin
3. Ensure database name matches exactly
4. Wait 5-10 minutes for hosting DNS propagation

### Issue 3: 404 Not Found on Pages
**Symptoms:** Pages return 404 errors
**Fix:**
1. Verify `.htaccess` uploaded to root
2. Check file paths match exactly
3. Ensure folder structure intact
4. Re-upload missing files

### Issue 4: "includes/config.php" Not Found
**Symptoms:** Error about config file
**Fix:**
1. Check `includes/` folder at root level (not inside public/)
2. Path should be: `htdocs/includes/config.php`
3. Not: `htdocs/public/includes/config.php`

### Issue 5: Admin Panel Not Accessible
**Symptoms:** 403 or 404 on admin pages
**Fix:**
1. Check `public/admin/` folder exists
2. Verify all admin PHP files uploaded
3. Check `.htaccess` not blocking admin directory

---

## 🔒 Security Recommendations

1. **Change Default Admin Password:**
   - Login as Admin@MCC
   - Change password immediately
   - Use strong password (12+ characters)

2. **Protect Sensitive Directories:**
   - `.htaccess` already protects `includes/`
   - Never expose database credentials
   - Keep `db/` folder for reference only

3. **Regular Backups:**
   - Backup database weekly
   - Keep copy of `includes/config.php`
   - Export user data regularly

---

## 📊 File Permissions

Set these permissions via File Manager:

| File/Folder | Permission | Octal |
|-------------|------------|-------|
| `.htaccess` | rw-r--r-- | 644 |
| `*.php` files | rw-r--r-- | 644 |
| `*.css` files | rw-r--r-- | 644 |
| `*.js` files | rw-r--r-- | 644 |
| Directories | rwxr-xr-x | 755 |
| `includes/` | rwxr-xr-x | 755 |
| `config.php` | rw------- | 600 (most secure) |

---

## 🚀 Performance Tips

1. **Enable Compression:**
   - Already configured in `.htaccess`
   - Reduces load time by 50-70%

2. **Browser Caching:**
   - `.htaccess` sets cache headers
   - Static files cached for 1 month

3. **Optimize Images:**
   - Compress certificate templates
   - Use WebP format if possible
   - Keep images under 2MB

4. **Database Optimization:**
   - Run `OPTIMIZE TABLE` monthly in phpMyAdmin
   - Delete old logs periodically

---

## 📞 Support

### Diagnostic Tools:
- **Database:** `/public/debug/databaseconnectioncheck.php`
- **CSS:** `/public/css_diagnostic.html`
- **General:** `/public/test_css.php`

### InfinityFree Support:
- Forum: https://forum.infinityfree.com/
- Check server status before troubleshooting
- Wait 10-15 minutes for file changes to propagate

---

## ✨ Success Indicators

Your deployment is successful when:
- ✅ Root domain redirects to public/
- ✅ All diagnostic tests pass
- ✅ CSS loads on all pages
- ✅ Can register new accounts
- ✅ Can login successfully
- ✅ Can generate certificates
- ✅ Admin panel accessible
- ✅ Database shows user data

---

**Deployment Date:** November 24, 2025
**Structure:** Option B - Folder Structure Intact
**Hosting:** InfinityFree (sql100.infinityfree.com)
**Version:** 2.0 with Stream/Level Selection

# 🚀 Certificate Generator - Quick Start Guide

## Get Started in 5 Minutes!

---

## 📥 Step 1: Deploy Files

Upload all project files to your web hosting:

```
htdocs/
├── includes/
│   └── config.php          ← Database configuration
├── public/
│   ├── index.php           ← Main certificate generator
│   ├── login.php           ← User login
│   ├── register.php        ← User registration
│   ├── styles.css          ← Styling
│   ├── script.js           ← JavaScript functionality
│   ├── actions/            ← Backend PHP actions
│   ├── admin/              ← Admin panel files
│   ├── assets/             ← Images & templates
│   └── demo-excel/         ← Excel template
└── .htaccess               ← URL routing (if needed)
```

---

## 🗄️ Step 2: Configure Database

### Option A: Using phpMyAdmin
1. Open phpMyAdmin
2. Create a new database (e.g., `certificate_generator`)
3. Import `debug/database_schema.sql`

### Option B: Manual SQL
Run the SQL from `debug/database_schema.sql` in your MySQL client.

### Update Configuration
Edit `includes/config.php`:

```php
define('DB_HOST', 'localhost');        // Your database host
define('DB_USER', 'your_username');    // Database username
define('DB_PASS', 'your_password');    // Database password
define('DB_NAME', 'certificate_generator'); // Database name
```

**For InfinityFree Hosting:**
```php
define('DB_HOST', 'sql100.infinityfree.com');
define('DB_USER', 'if0_XXXXXXXX');
define('DB_PASS', 'your_password');
define('DB_NAME', 'if0_XXXXXXXX_certificatedb');
```

---

## 👤 Step 3: Create Admin Account

1. Go to: `https://yoursite.com/public/admin/setup.php`
2. Fill in:
   - **Username**: `Admin@MCC`
   - **Email**: `admin@college.edu`
   - **Password**: `YourSecurePassword`
3. Click **"Create Admin Account"**
4. **⚠️ DELETE `setup.php` after creating admin!**

---

## ✅ Step 4: Test the System

### Test User Registration
1. Go to `/public/register.php`
2. Create a test account (Student or Staff)
3. Check if account is created successfully

### Test User Login
1. Go to `/public/login.php`
2. Login with test account
3. Verify you reach the certificate generator

### Test Certificate Generation
1. Fill in certificate details
2. Click "Generate PDF"
3. Verify PDF downloads correctly

### Test Admin Panel
1. Go to `/public/admin/login.php`
2. Login with admin credentials
3. Check dashboard shows statistics

---

## 🎯 Quick Usage

### Creating a Certificate
```
1. Login → /public/login.php
2. Fill fields:
   - Certificate No: MCC/2024/001
   - Name: John Doe
   - Certified For: Best Performance
   - From Date: 2024-01-01
   - To Date: 2024-01-01
3. Click "Generate PDF"
4. Download and print!
```

### Bulk Generation
```
1. Download template → Click "Template" button
2. Fill Excel with multiple entries
3. Upload Excel → Click "Choose Excel"
4. Click "Generate"
5. Download ZIP with all PDFs
```

---

## 🔗 Important URLs

| Purpose | URL Path |
|---------|----------|
| User Login | `/public/login.php` |
| User Register | `/public/register.php` |
| Generator | `/public/index.php` |
| Admin Login | `/public/admin/login.php` |
| Admin Panel | `/public/admin/admin_panel.php` |
| Admin Setup | `/public/admin/setup.php` |

---

## ⚠️ Security Checklist

- [ ] Delete `/public/admin/setup.php` after creating admin
- [ ] Change default admin password
- [ ] Update database credentials in config.php
- [ ] Set proper file permissions (644 for files, 755 for folders)
- [ ] Remove debug files in production

---

## 🆘 Need Help?

- **Full Documentation**: See `readme/USER_GUIDE.md`
- **Database Setup**: See `readme/DATABASE_SETUP.md`
- **Configuration**: See `readme/CONFIGURATION_GUIDE.md`

---

*Ready to generate certificates! 🎉*

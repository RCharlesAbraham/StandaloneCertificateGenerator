# 🚀 DEPLOYMENT GUIDE FOR INFINITYFREE HOSTING

## ⚠️ IMPORTANT: CSS Not Loading Fix

If your CSS is not working after hosting, follow these steps:

---

## 📁 Step 1: Upload Files in CORRECT Structure

Upload to `htdocs/` folder:

```
htdocs/
├── .htaccess                    ← Upload this!
├── test_css.php                 ← Test page
├── styles.css                   ← MUST BE IN ROOT
├── script.js
├── index.php
├── login.php
├── register.php
├── config.json
├── actions/
│   ├── login_process.php
│   ├── register_process.php
│   ├── logout.php
│   └── log_certificate.php
├── admin/
│   ├── login.php
│   ├── panel.php
│   └── admin_api.php
├── assets/
│   ├── MainPlaceholderCertificate.jpg
│   └── signature-images/
│       ├── Frank.png
│       ├── Aarthi.png
│       └── Wilson.png
└── includes/
    └── config.php
```

---

## 🔧 Step 2: Fix CSS Path Issues

### Check #1: File Upload
- Verify `styles.css` is uploaded to `htdocs/styles.css`
- File size should be around 15-20 KB
- Use FileZilla or File Manager in cPanel

### Check #2: MIME Type
InfinityFree sometimes doesn't serve CSS properly. Add this to `.htaccess`:

```apache
AddType text/css .css
AddType application/javascript .js
```

### Check #3: File Permissions
Set permissions for `styles.css`:
- Right-click file in File Manager
- Set to `644` (rw-r--r--)

---

## 🧪 Step 3: Test CSS Loading

Visit: `http://yourdomain.com/test_css.php`

This will show:
- ✅ CSS Loaded Successfully (Green) = Working
- ❌ CSS NOT Loading (Red) = Still broken

---

## 🐛 Step 4: Common Issues & Fixes

### Issue 1: CSS Returns 404
**Problem:** File not uploaded or wrong location
**Fix:** Re-upload `styles.css` to root `htdocs/` folder

### Issue 2: CSS Returns 403 Forbidden
**Problem:** Wrong file permissions
**Fix:** Change permissions to 644

### Issue 3: CSS Shows Gibberish/Plain Text
**Problem:** Wrong MIME type
**Fix:** Add `AddType text/css .css` to `.htaccess`

### Issue 4: Cached Old CSS
**Problem:** Browser cache
**Fix:** Hard refresh with Ctrl+F5 or Cmd+Shift+R

### Issue 5: InfinityFree blocks CSS
**Problem:** Ad insertion interfering
**Fix:** Add this to `.htaccess`:
```apache
<FilesMatch "\.(css|js)$">
    Header set Cache-Control "max-age=31536000, public"
</FilesMatch>
```

---

## 💾 Step 5: Database Setup

1. **Create Database** in MySQL Databases:
   - Database name: `if0_40495407_certificatedb`
   - Already created: ✅

2. **Import Schema** via phpMyAdmin:
   - Select database
   - Import tab
   - Choose: `db/database_schema_hosting.sql`
   - Click "Go"

3. **Verify Tables Created:**
   - users ✅
   - admins ✅
   - certificate_logs ✅
   - activity_logs ✅
   - admin_logs ✅
   - user_sessions ✅

---

## 🔐 Step 6: Update config.php

Already configured:
```php
define('DB_HOST', 'sql100.infinityfree.com');
define('DB_USER', 'if0_40495407');
define('DB_PASS', 'Chab2000');
define('DB_NAME', 'if0_40495407_certificatedb');
```

---

## ✅ Step 7: Test Registration System

1. Visit: `http://yourdomain.com/register.php`
2. Select: **Student** → **Aided** → **UG**
3. Choose Department from dropdown
4. Fill all fields
5. Submit

Expected:
- Form submits successfully
- Redirects to login page
- Can login with created account

---

## 🔍 Step 8: Verify Everything Works

### Test Checklist:
- [ ] test_css.php shows green ✅
- [ ] register.php displays properly styled
- [ ] login.php displays properly styled
- [ ] Radio buttons work (Student/Staff, Aided/SFS, UG/PG)
- [ ] Department dropdown populates correctly
- [ ] Registration creates account
- [ ] Login works
- [ ] Main certificate generator loads
- [ ] Can generate single PDF
- [ ] Can import Excel for bulk generation

---

## 🆘 Still Not Working?

### Quick Fix Commands:

1. **Clear browser cache completely**
2. **Try different browser** (Chrome/Firefox/Edge)
3. **Check browser console** (F12 → Console tab)
   - Look for CSS loading errors
   - Check Network tab for 404/403 errors
4. **Verify file encoding** (must be UTF-8)
5. **Re-upload styles.css** (might be corrupted)

---

## 📞 Support Resources:

**InfinityFree Forum:** https://forum.infinityfree.com/
**Check CSS File Direct URL:** http://yourdomain.com/styles.css
- Should show CSS code, not 404 or HTML

---

## 🎯 Expected Result:

After following all steps:
- Beautiful gradient background on login/register pages
- Professional styled forms with rounded corners
- Radio buttons with custom styling
- Proper spacing and typography
- Hover effects on buttons
- Smooth animations

---

## 📝 Notes:

- InfinityFree inserts ads in HTML files but NOT in CSS/JS
- CSS file must be uploaded in BINARY mode (FileZilla uses this by default)
- If ads appear in your pages, they won't affect CSS loading
- First-time load might be slow due to free hosting

---

**Last Updated:** November 24, 2025
**Author:** Certificate Generator Team
**Version:** 2.0 with Stream/Level Selection

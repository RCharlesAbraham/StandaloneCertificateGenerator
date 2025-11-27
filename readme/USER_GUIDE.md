# 📜 Certificate Generator - User Guide

## Complete Documentation for Madras Christian College Certificate Generator

---

## 📋 Table of Contents

1. [Overview](#overview)
2. [Getting Started](#getting-started)
3. [User Registration](#user-registration)
4. [User Login](#user-login)
5. [Certificate Generator Interface](#certificate-generator-interface)
6. [Creating Single Certificates](#creating-single-certificates)
7. [Bulk Certificate Generation](#bulk-certificate-generation)
8. [Canvas Controls & Customization](#canvas-controls--customization)
9. [Admin Panel](#admin-panel)
10. [Troubleshooting](#troubleshooting)

---

## 🎯 Overview

The **Certificate Generator** is a web-based application designed for Madras Christian College to create professional certificates quickly and efficiently. 

### Key Features:
- ✅ **Single Certificate Generation** - Create individual certificates with custom details
- ✅ **Bulk Generation** - Generate multiple certificates from Excel files
- ✅ **Live Preview** - See changes in real-time before generating
- ✅ **Draggable Text** - Position text anywhere on the certificate
- ✅ **Custom Templates** - Upload your own certificate templates
- ✅ **PDF Export** - Download certificates as high-quality PDFs
- ✅ **User Management** - Separate accounts for Students and Staff
- ✅ **Admin Dashboard** - Monitor users, certificates, and activity

---

## 🚀 Getting Started

### System Requirements
- Modern web browser (Chrome, Firefox, Edge, Safari)
- Internet connection
- PDF viewer for downloaded certificates

### Access URLs

| Page | URL |
|------|-----|
| User Login | `/public/login.php` |
| User Registration | `/public/register.php` |
| Certificate Generator | `/public/index.php` (after login) |
| Admin Login | `/public/admin/login.php` |
| Admin Panel | `/public/admin/admin_panel.php` |

---

## 📝 User Registration

### Step 1: Access Registration Page
Navigate to `/public/register.php`

### Step 2: Select User Type
Choose one:
- **Student** - For college students
- **Staff** - For faculty and staff members

### Step 3: Fill Required Information

#### For Students:
| Field | Description | Required |
|-------|-------------|----------|
| Stream | Aided or SFS (Self-Financed Stream) | ✅ |
| Level | UG (Undergraduate) or PG (Postgraduate) | ✅ |
| Registration Number | Your college registration number | ✅ |
| Full Name | Your complete name | ✅ |
| Department | Select from dropdown (based on Stream & Level) | ✅ |
| Phone Number | Contact number | ✅ |
| Email Address | Valid email for login | ✅ |
| Password | Minimum 6 characters | ✅ |
| College | MCC or Other | ✅ |

#### For Staff:
| Field | Description | Required |
|-------|-------------|----------|
| Designation | e.g., Assistant Professor, HOD | ✅ |
| Full Name | Your complete name | ✅ |
| Department | Type your department | ✅ |
| Phone Number | Contact number | ✅ |
| Email Address | Valid email for login | ✅ |
| Password | Minimum 6 characters | ✅ |
| College | MCC or Other | ✅ |

### Step 4: Submit
Click **"Create Account"** and wait for confirmation.

### Department Lists

#### Aided - UG (13 Departments)
- English Language & Literature
- Tamil Literature
- History
- Political Science
- Economics
- Philosophy
- Commerce (General)
- Mathematics
- Statistics
- Physics
- Chemistry
- Plant Biology & Plant Biotechnology
- Zoology

#### Aided - PG (15 Departments)
- All UG departments plus:
- Public Administration
- Commerce (M.Com)
- MSW (Community Development / Medical & Psychiatry)

#### SFS - UG (18 Departments)
- English Language & Literature
- Journalism
- History (Vocational – Archaeology & Museology)
- Social Work (BSW)
- Commerce (General)
- Commerce (Accounting & Finance)
- Commerce (Professional Accounting)
- Business Administration (BBA)
- Computer Applications (BCA)
- Geography, Tourism & Travel Management
- Hospitality & Tourism
- Mathematics
- Physics
- Microbiology
- Computer Science
- Visual Communication
- Physical Education, Health Education & Sports
- Psychology

#### SFS - PG (7 Departments)
- M.A. Communication
- MSW – Human Resource Management
- M.Com – Computer Oriented Business Applications
- M.Sc. Chemistry
- M.Sc. Applied Microbiology
- MCA – Computer Applications
- M.Sc. Data Science

---

## 🔐 User Login

### Step 1: Access Login Page
Navigate to `/public/login.php`

### Step 2: Enter Credentials
- **Email Address**: Your registered email
- **Password**: Your account password

### Step 3: Click "Sign In"
You'll be redirected to the Certificate Generator.

### Forgot Password?
Contact the administrator to reset your password.

---

## 🖥️ Certificate Generator Interface

After logging in, you'll see the main interface with two sections:

### Left Sidebar (Form Section)
- Certificate details input fields
- Action buttons (Insert Text, Generate PDF)
- Bulk data section
- Canvas controls

### Right Section (Live Preview)
- Real-time certificate preview
- Template change option
- Zoom controls

---

## 📄 Creating Single Certificates

### Step 1: Fill Certificate Details

| Field | Description | Example |
|-------|-------------|---------|
| Certificate No | Unique certificate number | `MCC/2024/001` |
| Name | Recipient's full name | `John Doe` |
| Certified For | Achievement/Event description | `Outstanding Performance in Annual Sports Meet` |
| From Date | Start date of event/period | `2024-01-15` |
| To Date | End date of event/period | `2024-01-20` |

### Step 2: Preview
Watch the live preview update as you type.

### Step 3: Position Text (Optional)
- Click and drag text placeholders on the canvas
- Use zoom controls for precise positioning

### Step 4: Generate PDF
Click **"Generate PDF"** button to download your certificate.

---

## 📊 Bulk Certificate Generation

Generate multiple certificates at once using Excel files.

### Step 1: Download Template
Click **"Template"** button to download `Excel_Blueprint.xlsx`

### Step 2: Fill Excel Data
Open the template and fill in the columns:

| Column | Description |
|--------|-------------|
| Certificate No | Unique number for each certificate |
| Name | Recipient name |
| Certified For | Achievement description |
| From Date | Start date (YYYY-MM-DD format) |
| To Date | End date (YYYY-MM-DD format) |

**Example:**
```
| Certificate No | Name        | Certified For          | From Date  | To Date    |
|----------------|-------------|------------------------|------------|------------|
| MCC/2024/001   | Alice Smith | Best Student Award     | 2024-03-01 | 2024-03-01 |
| MCC/2024/002   | Bob Johnson | Sports Achievement     | 2024-03-15 | 2024-03-20 |
| MCC/2024/003   | Carol White | Academic Excellence    | 2024-04-01 | 2024-04-01 |
```

### Step 3: Upload Excel
Click **"Choose Excel To Upload Data"** and select your filled Excel file.

### Step 4: Generate All
Click **"Generate"** button that appears after upload.

### Step 5: Download
All certificates will be generated and downloaded as a ZIP file.

### Progress Tracking
A progress bar shows generation status: `Generating... 5/20`

---

## 🎨 Canvas Controls & Customization

### Moving Text Elements
- **Click and drag** any text placeholder on the canvas
- Position text exactly where you want it

### Zoom Controls
| Action | Method |
|--------|--------|
| Zoom In | Click 🔍+ button or `Ctrl + Plus` |
| Zoom Out | Click 🔍- button or `Ctrl + Minus` |
| Reset Zoom | Click 🏠 button or `Ctrl + 0` |
| Mouse Wheel | `Ctrl + Scroll` |

### Toggle Grid
Click **"Toggle Grid"** to show/hide alignment grid with percentage markers.

### Change Template
1. Click **"Change the Template"** in the canvas header
2. Select your custom certificate image (JPG, PNG)
3. The preview updates immediately

### Change Logo
1. Click on the logo in the top navigation
2. Select a new logo image
3. Logo updates across the interface

### Insert Custom Text
1. Click **"Insert Text"** button
2. Add custom text fields as needed
3. Position them on the canvas

---

## 👑 Admin Panel

### Accessing Admin Panel
1. Navigate to `/public/admin/login.php`
2. Enter admin credentials
3. Click "Sign In"

### First-Time Admin Setup
1. Go to `/public/admin/setup.php`
2. Create your admin account
3. **Delete setup.php after creating account!**

### Dashboard Overview

#### Statistics Cards
- **Total Users** - All registered users
- **Students** - Student accounts count
- **Staff** - Staff accounts count
- **Certificates Generated** - Total certificates created

### Tabs

#### 1. Users Tab
View all registered users:
- ID, Name, Type, Email, Department, Created Date
- Filter by Student/Staff

#### 2. Certificates Tab
Certificate generation history:
- ID, User, Type, Recipient, Generated Date, Bulk Count

#### 3. Activity Logs Tab
User activity tracking:
- User, Type, Activity, Description, Timestamp

#### 4. Active Sessions Tab
Currently logged-in users:
- User, IP Address, Login Time, Last Activity, Status

### Admin Actions
- **Logout**: Click "Logout" button in the top-right corner

---

## 🔧 Troubleshooting

### Common Issues & Solutions

#### "Cannot connect to database"
- Check database configuration in `includes/config.php`
- Verify database server is running
- Confirm credentials are correct

#### "CSS not loading properly"
- Clear browser cache (`Ctrl + Shift + R`)
- Check if `.htaccess` file is uploaded
- Verify file permissions on hosting

#### "500 Internal Server Error"
- Check PHP error logs
- Verify file paths in PHP files
- Ensure all required files are uploaded

#### "Login not working"
- Check email/password are correct
- Clear browser cookies
- Try incognito/private browsing mode

#### "PDF not generating"
- Ensure JavaScript is enabled
- Check browser console for errors (`F12`)
- Try a different browser

#### "Excel upload not working"
- Use `.xlsx` format (not `.xls`)
- Ensure file follows the template structure
- Check date formats (YYYY-MM-DD)

#### "Template image not showing"
- Upload image in JPG or PNG format
- Check image file size (max 5MB recommended)
- Verify image path is accessible

### Browser Support
| Browser | Supported |
|---------|-----------|
| Chrome 80+ | ✅ |
| Firefox 75+ | ✅ |
| Edge 80+ | ✅ |
| Safari 13+ | ✅ |
| Internet Explorer | ❌ |

---

## 📞 Support

### Contact Information
- **Technical Support**: Contact your system administrator
- **College IT Department**: For account issues
- **GitHub Repository**: [StandaloneCertificateGenerator](https://github.com/RCharlesAbraham/StandaloneCertificateGenerator)

### Reporting Issues
When reporting issues, please include:
1. Browser name and version
2. Screenshot of the error
3. Steps to reproduce the problem
4. Any error messages shown

---

## 📝 Quick Reference Card

### Keyboard Shortcuts
| Shortcut | Action |
|----------|--------|
| `Ctrl + +` | Zoom In |
| `Ctrl + -` | Zoom Out |
| `Ctrl + 0` | Reset Zoom |
| `Ctrl + Scroll` | Zoom with mouse |

### File Formats
| Type | Accepted Formats |
|------|------------------|
| Templates | JPG, PNG |
| Logo | JPG, PNG |
| Excel Data | XLSX |
| Output | PDF |

### URL Quick Links
```
Login:      /public/login.php
Register:   /public/register.php
Generator:  /public/index.php
Admin:      /public/admin/login.php
```

---

*Last Updated: November 2025*
*Version: 1.0*
*Madras Christian College Certificate Generator*

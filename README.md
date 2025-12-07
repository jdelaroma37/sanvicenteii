# Barangay San Vicente II Portal System
## Complete Documentation for Transfer to Another Laptop

---

## 📋 TABLE OF CONTENTS
1. [Project Overview](#project-overview)
2. [System Requirements](#system-requirements)
3. [Installation Instructions](#installation-instructions)
4. [Database Setup](#database-setup)
5. [Configuration Files](#configuration-files)
6. [File Structure](#file-structure)
7. [Default Credentials](#default-credentials)
8. [Features](#features)
9. [Transfer Instructions](#transfer-instructions)
10. [Troubleshooting](#troubleshooting)

---

## 🎯 PROJECT OVERVIEW

**Barangay San Vicente II Portal System** is a comprehensive web-based management system for barangay operations. It includes:

- **Resident Management** - Registration, profiles, family members
- **Document Request System** - Online document requests and processing
- **Admin Dashboard** - Complete administrative control panel
- **Worker Management** - Staff scheduling and task management
- **Announcements & Events** - Public announcements and event calendar
- **Contact System** - Message handling and communication
- **Multi-language Support** - English and Tagalog
- **PDF Generation** - Document generation using dompdf

---

## 💻 SYSTEM REQUIREMENTS

### Required Software:
1. **XAMPP** (or similar local server)
   - PHP 7.4 or higher
   - MySQL 5.7 or higher
   - Apache Web Server

2. **Web Browser**
   - Chrome, Firefox, Edge, or Safari (latest versions)

### Recommended:
- **phpMyAdmin** (included in XAMPP) for database management
- **Text Editor** (VS Code, Notepad++, etc.) for code editing

---

## 📦 INSTALLATION INSTRUCTIONS

### Step 1: Install XAMPP
1. Download XAMPP from https://www.apachefriends.org/
2. Install XAMPP to default location: `C:\xampp\`
3. Start Apache and MySQL services from XAMPP Control Panel

### Step 2: Copy Project Files
1. Copy the entire `brgysanvicenteii` folder to:
   ```
   C:\xampp\htdocs\brgysanvicenteii
   ```

### Step 3: Database Setup
1. Open phpMyAdmin: http://localhost/phpmyadmin
2. Click "Import" tab
3. Select the file: `barangay_portal.sql`
4. Click "Go" to import
5. Wait for "Import has been successfully finished" message

### Step 4: Configure Database Connection
The database configuration is already set in:
- `config.php`
- `db.php`

**Default Settings:**
```php
Host: localhost
Database: barangay_db
Username: root
Password: (empty - XAMPP default)
```

**If you need to change these settings**, edit:
- `config.php` (lines 4-7)
- `db.php` (lines 2-5)

### Step 5: Set Up Default Admin Account
1. Open browser and go to:
   ```
   http://localhost/brgysanvicenteii/setup_default_admin.php
   ```
2. This will set up the default admin account with password

### Step 6: Access the System
- **Homepage:** http://localhost/brgysanvicenteii/
- **Admin Login:** http://localhost/brgysanvicenteii/admin/admin_login.php
- **Resident Login:** http://localhost/brgysanvicenteii/resident/login.php
- **Worker Login:** http://localhost/brgysanvicenteii/worker/worker_login.php

---

## 🗄️ DATABASE SETUP

### Database Name:
```
barangay_db
```

### Main Tables:
1. **residents** - Resident information and profiles
2. **family_members** - Family member records
3. **admins** - Administrator accounts
4. **barangay_workers** - Barangay staff/workers
5. **document_requests** - Document request records
6. **walk_in_requests** - Walk-in request records
7. **walk_in_requests_archive** - Archived requests
8. **notifications** - System notifications
9. **contact_messages** - Contact form messages
10. **activity_log** - System activity logs
11. **worker_responsibilities** - Worker responsibilities
12. **duty_schedules** - Worker duty schedules
13. **staff_tasks** - Staff task assignments

### Database Backup:
To backup your database:
1. Open phpMyAdmin
2. Select `barangay_db` database
3. Click "Export" tab
4. Choose "Quick" or "Custom" method
5. Click "Go" to download SQL file

### Database Restore:
1. Open phpMyAdmin
2. Create new database: `barangay_db`
3. Click "Import" tab
4. Select your backup SQL file
5. Click "Go"

---

## ⚙️ CONFIGURATION FILES

### 1. Database Configuration

**File: `config.php`**
```php
$host = 'localhost';
$db   = 'barangay_db';
$user = 'root';
$pass = ''; // XAMPP default (empty)
```

**File: `db.php`**
```php
$host = "localhost";
$dbname = "barangay_db";
$username = "root";
$password = ""; // XAMPP default (empty)
```

### 2. Important Directories

**Uploads Folder:**
- `uploads/` - User uploaded photos and files
- Make sure this folder has write permissions

**Data Folder:**
- `data/` - JSON data files, announcements, notifications
- `data/generated_pdfs/` - Generated PDF documents
- `data/announcements/` - Announcement images and data

**Images Folder:**
- `images/` - System images (logo, barangay photos, etc.)

---

## 📁 FILE STRUCTURE

```
brgysanvicenteii/
├── admin/                    # Admin panel files
│   ├── admin_dashboard.php
│   ├── admin_login.php
│   ├── admin_residents.php
│   └── ... (other admin files)
│
├── resident/                 # Resident portal files
│   ├── resident_dashboard.php
│   ├── login.php
│   ├── profile.php
│   ├── css/                  # Resident CSS files
│   ├── js/                   # Resident JavaScript files
│   └── maps/                 # Map HTML files
│
├── worker/                   # Worker portal files
│   ├── worker_dashboard.php
│   ├── worker_login.php
│   └── ...
│
├── data/                     # Data storage
│   ├── announcements/        # Announcement data
│   ├── generated_pdfs/       # Generated PDFs
│   ├── notifications/        # Notification data
│   ├── requests/             # Request data
│   └── settings/             # System settings
│
├── dompdf/                   # PDF generation library
├── fonts/                    # Custom fonts
├── images/                   # System images
├── lang/                     # Language files (en.php, tl.php)
├── uploads/                  # User uploads
│
├── index.php                 # Homepage
├── index.css                 # Main stylesheet
├── index.js                  # Main JavaScript
├── config.php                # Database config
├── db.php                    # Database connection
├── barangay_portal.sql       # Database schema
└── README.md                 # This file
```

---

## 🔐 DEFAULT CREDENTIALS

### Super Admin Account 1:
- **Email:** admin@barangaysanvicente.com
- **Password:** admin123

### Super Admin Account 2:
- **Email:** weh@gmail.com
- **Password:** sanaolbaliw

**Note:** Both accounts are automatically created when you import the database. If you need to reset the passwords, you can run `setup_default_admin.php` to restore them to the default values.

### Admin Registration Limits:
- **Maximum Admins:** 3
- **Maximum Workers:** 15

---

## ✨ FEATURES

### Resident Features:
- ✅ Resident registration and profile management
- ✅ Family member management
- ✅ Document request submission
- ✅ Request status tracking
- ✅ Notifications
- ✅ Contact form
- ✅ Profile photo upload
- ✅ View announcements and events

### Admin Features:
- ✅ Complete resident management
- ✅ Document request processing
- ✅ Walk-in request management
- ✅ Announcement management
- ✅ Event calendar management
- ✅ Barangay officials management
- ✅ Worker/staff management
- ✅ Activity logs
- ✅ System settings
- ✅ Backup and export
- ✅ Financial records
- ✅ Project records
- ✅ Message management
- ✅ PDF generation

### Worker Features:
- ✅ Worker dashboard
- ✅ Task management
- ✅ Notification system
- ✅ Profile management

### System Features:
- ✅ Multi-language support (English/Tagalog)
- ✅ Responsive design
- ✅ PDF document generation
- ✅ File upload system
- ✅ Notification system
- ✅ Activity logging
- ✅ Session management

---

## 📦 TRANSFER INSTRUCTIONS

### To Transfer to Another Laptop:

#### Step 1: Backup Everything
1. **Copy the entire project folder:**
   ```
   C:\xampp\htdocs\brgysanvicenteii
   ```
   Copy this entire folder to:
   - USB drive, or
   - External hard drive, or
   - Cloud storage (Google Drive, Dropbox, etc.)

2. **Export the Database:**
   - Open phpMyAdmin
   - Select `barangay_db` database
   - Click "Export" → "Quick" → "Go"
   - Save the SQL file in the project folder

#### Step 2: On New Laptop
1. **Install XAMPP** (same version if possible)
2. **Start Apache and MySQL** from XAMPP Control Panel
3. **Copy project folder** to:
   ```
   C:\xampp\htdocs\brgysanvicenteii
   ```

#### Step 3: Import Database
1. Open phpMyAdmin: http://localhost/phpmyadmin
2. Click "Import" tab
3. Select `barangay_portal.sql` file
4. Click "Go"
5. Wait for successful import message

#### Step 4: Verify Configuration
1. Check `config.php` and `db.php` - should be:
   ```php
   $host = 'localhost';
   $db = 'barangay_db';
   $user = 'root';
   $pass = '';
   ```

2. If database credentials are different on new laptop, update:
   - `config.php`
   - `db.php`

#### Step 5: Set Permissions
1. Make sure `uploads/` folder has write permissions
2. Make sure `data/` folder has write permissions
3. Make sure `data/generated_pdfs/` folder has write permissions

#### Step 6: Test the System
1. Access: http://localhost/brgysanvicenteii/
2. Try logging in with admin credentials
3. Test file uploads
4. Test PDF generation

---

## 🔧 TROUBLESHOOTING

### Problem: Database Connection Error
**Solution:**
- Check if MySQL is running in XAMPP
- Verify database name is `barangay_db`
- Check `config.php` and `db.php` credentials
- Make sure database is imported correctly

### Problem: Page Not Found (404)
**Solution:**
- Check if Apache is running in XAMPP
- Verify project folder is in `C:\xampp\htdocs\brgysanvicenteii`
- Check URL: http://localhost/brgysanvicenteii/

### Problem: Images/Photos Not Showing
**Solution:**
- Check if image files exist in `images/` and `uploads/` folders
- Verify folder permissions
- Check file paths in code

### Problem: PDF Generation Not Working
**Solution:**
- Check if `dompdf` folder exists
- Verify `data/generated_pdfs/` folder has write permissions
- Check PHP error logs

### Problem: File Upload Not Working
**Solution:**
- Check `uploads/` folder permissions
- Verify PHP upload settings in `php.ini`:
  ```
  upload_max_filesize = 10M
  post_max_size = 10M
  ```

### Problem: Session Errors
**Solution:**
- Make sure `session_start()` is called before any output
- Check PHP session settings
- Clear browser cookies and cache

### Problem: Admin Login Not Working
**Solution:**
- Run `setup_default_admin.php` again
- Check database for admin accounts
- Verify password hashing is working

---

## 📝 IMPORTANT NOTES

### 1. Database Backup
- **ALWAYS backup your database** before making changes
- Export database regularly using phpMyAdmin
- Keep backup SQL files in a safe location

### 2. File Permissions
- `uploads/` folder must be writable
- `data/` folder must be writable
- `data/generated_pdfs/` must be writable

### 3. PHP Version
- System requires PHP 7.4 or higher
- Check PHP version: http://localhost/brgysanvicenteii/admin/admin_system_info.php

### 4. Browser Compatibility
- Tested on: Chrome, Firefox, Edge, Safari
- Clear browser cache if experiencing issues

### 5. Security
- Change default admin passwords after setup
- Keep XAMPP updated
- Don't expose system to public internet without proper security

### 6. Data Files
- JSON files in `data/` folder contain important system data
- Backup these files regularly
- Don't delete or modify manually unless necessary

---

## 🎨 DESIGN & STYLING

### Main Stylesheet:
- **File:** `index.css` (4,637 lines)
- Contains all homepage styling
- Custom fonts: Fann Grotesque
- Color scheme: Blue (#AECADF, #2C5F8D)

### Resident Stylesheets:
- Located in `resident/css/` folder
- Separate CSS files for each page

### Responsive Design:
- Mobile-friendly
- Tablet support
- Desktop optimized

---

## 📞 SUPPORT

### If You Need Help:
1. Check this README.md file
2. Review error messages in browser console (F12)
3. Check PHP error logs in XAMPP
4. Verify all files are copied correctly
5. Ensure database is imported properly

---

## ✅ CHECKLIST FOR TRANSFER

Before transferring, make sure you have:
- [ ] Entire `brgysanvicenteii` folder copied
- [ ] Database exported (`barangay_portal.sql`)
- [ ] All images in `images/` folder
- [ ] All uploads in `uploads/` folder (if needed)
- [ ] All data files in `data/` folder
- [ ] `dompdf` library folder
- [ ] Fonts folder
- [ ] This README.md file

After transfer, verify:
- [ ] XAMPP installed and running
- [ ] Database imported successfully
- [ ] Can access homepage
- [ ] Can login as admin
- [ ] File uploads work
- [ ] PDF generation works
- [ ] All images display correctly

---

## 📄 LICENSE & CREDITS

**Project:** Barangay San Vicente II Portal System
**Location:** Barangay San Vicente II, Silang, Cavite
**Technology:** PHP, MySQL, JavaScript, CSS, HTML

**Libraries Used:**
- dompdf (PDF generation)
- Custom fonts (Fann Grotesque)

---

## 🎯 QUICK START SUMMARY

1. Install XAMPP
2. Copy folder to `C:\xampp\htdocs\brgysanvicenteii`
3. Start Apache and MySQL
4. Import `barangay_portal.sql` in phpMyAdmin
5. Run `setup_default_admin.php`
6. Access: http://localhost/brgysanvicenteii/
7. Login with admin credentials

---

**Last Updated:** 2024
**Version:** 1.0
**Status:** Production Ready

---

## 🇵🇭 TAGALOG VERSION

### PAGLIPAT SA IBANG LAPTOP

**Oo, kapag nilipat mo ang buong folder na ito sa ibang laptop, ganon pa rin ang lahat - database, design, at lahat ng features!**

**Mga Hakbang:**

1. **I-copy ang buong folder:**
   - Kopyahin ang `brgysanvicenteii` folder
   - Ilagay sa USB o external drive

2. **I-export ang database:**
   - Buksan ang phpMyAdmin
   - Piliin ang `barangay_db`
   - Click "Export" → "Go"
   - I-save ang SQL file

3. **Sa bagong laptop:**
   - Mag-install ng XAMPP
   - I-copy ang folder sa `C:\xampp\htdocs\`
   - I-import ang database
   - Buksan ang http://localhost/brgysanvicenteii/

**Lahat ng ginawa dito ay naka-save na:**
- ✅ Lahat ng database tables
- ✅ Lahat ng design at CSS
- ✅ Lahat ng PHP files
- ✅ Lahat ng images
- ✅ Lahat ng configurations
- ✅ Lahat ng features

**Important:** Siguraduhin na:
- Naka-install ang XAMPP
- Naka-start ang Apache at MySQL
- Na-import ang database
- Parehong folder structure

---

**End of Documentation**


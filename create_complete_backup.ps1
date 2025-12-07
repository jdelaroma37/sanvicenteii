# Complete Backup Script for Barangay System
# This script creates a complete backup package with all files, database, and data

$timestamp = Get-Date -Format "yyyyMMdd_HHmmss"
$backupFolder = "brgysanvicenteii_complete_backup_$timestamp"
$backupPath = Join-Path $PSScriptRoot $backupFolder

Write-Host "Creating complete backup package..." -ForegroundColor Green
Write-Host "Backup location: $backupPath" -ForegroundColor Yellow

# Create backup directory
New-Item -ItemType Directory -Path $backupPath -Force | Out-Null
New-Item -ItemType Directory -Path "$backupPath\database" -Force | Out-Null
New-Item -ItemType Directory -Path "$backupPath\system_files" -Force | Out-Null

# Copy latest SQL backup
Write-Host "`n[1/5] Copying database backup..." -ForegroundColor Cyan
$sqlFiles = @(
    "barangay_db_backup.sql",
    "barangay_db_complete_export.sql",
    "barangay_db_export.sql",
    "barangay_portal.sql"
)

$latestSql = $null
$latestDate = [DateTime]::MinValue

foreach ($sqlFile in $sqlFiles) {
    $filePath = Join-Path $PSScriptRoot $sqlFile
    if (Test-Path $filePath) {
        $fileDate = (Get-Item $filePath).LastWriteTime
        if ($fileDate -gt $latestDate) {
            $latestDate = $fileDate
            $latestSql = $filePath
        }
    }
}

if ($latestSql) {
    Copy-Item $latestSql "$backupPath\database\barangay_db_backup.sql" -Force
    Write-Host "   [OK] Copied: $(Split-Path $latestSql -Leaf)" -ForegroundColor Green
} else {
    Write-Host "   [WARNING] No SQL backup found - MySQL may need to be running to create one" -ForegroundColor Yellow
}

# Copy all PHP files and system files
Write-Host "`n[2/5] Copying system files..." -ForegroundColor Cyan
$excludePatterns = @(
    "*.sql",
    "*.zip",
    "node_modules",
    ".git",
    "create_complete_backup.ps1",
    "*.log"
)

Get-ChildItem -Path $PSScriptRoot -Recurse -File | Where-Object {
    $exclude = $false
    foreach ($pattern in $excludePatterns) {
        if ($_.FullName -like "*$pattern*") {
            $exclude = $true
            break
        }
    }
    return -not $exclude
} | ForEach-Object {
    $relativePath = $_.FullName.Substring($PSScriptRoot.Length + 1)
    $destPath = Join-Path $backupPath $relativePath
    $destDir = Split-Path $destPath -Parent
    if (-not (Test-Path $destDir)) {
        New-Item -ItemType Directory -Path $destDir -Force | Out-Null
    }
    Copy-Item $_.FullName $destPath -Force
}

Write-Host "   [OK] All system files copied" -ForegroundColor Green

# Copy data directory (announcements, settings, etc.)
Write-Host "`n[3/5] Copying data files..." -ForegroundColor Cyan
if (Test-Path "$PSScriptRoot\data") {
    Copy-Item "$PSScriptRoot\data" "$backupPath\data" -Recurse -Force
    Write-Host "   [OK] Data directory copied (announcements, settings, etc.)" -ForegroundColor Green
}

# Copy uploads directory (images, photos, PDFs)
Write-Host "`n[4/5] Copying uploads..." -ForegroundColor Cyan
if (Test-Path "$PSScriptRoot\uploads") {
    Copy-Item "$PSScriptRoot\uploads" "$backupPath\uploads" -Recurse -Force
    Write-Host "   [OK] Uploads directory copied (images, photos, PDFs)" -ForegroundColor Green
}

# Copy images directory
if (Test-Path "$PSScriptRoot\images") {
    Copy-Item "$PSScriptRoot\images" "$backupPath\images" -Recurse -Force
    Write-Host "   [OK] Images directory copied" -ForegroundColor Green
}

# Create installation instructions file
Write-Host "`n[5/5] Creating installation guide..." -ForegroundColor Cyan
$installGuide = @"
COMPLETE BACKUP PACKAGE - Barangay San Vicente II System
Generated: $(Get-Date -Format "yyyy-MM-dd HH:mm:ss")

===========================================
INSTALLATION INSTRUCTIONS
===========================================

1. DATABASE SETUP:
   - Install XAMPP (or any MySQL/MariaDB server)
   - Start MySQL service
   - Open phpMyAdmin (http://localhost/phpmyadmin)
   - Create database: barangay_db
   - Import: database\barangay_db_backup.sql

2. FILE SETUP:
   - Copy ALL files to: C:\xampp\htdocs\brgysanvicenteii\
   - Make sure folder structure is preserved

3. CONFIGURATION:
   - Check config.php - database settings should be:
     * Host: localhost
     * Database: barangay_db
     * User: root
     * Password: (blank for XAMPP default)

4. PERMISSIONS:
   - Ensure uploads/ folder is writable
   - Ensure data/ folder is writable

5. TEST:
   - Open: http://localhost/brgysanvicenteii/
   - Login with your admin account
   - Verify all data, announcements, and files are present

===========================================
WHAT'S INCLUDED:
===========================================

[OK] Complete database backup (all accounts, data, settings)
[OK] All PHP files and system code
[OK] All announcements (JSON + images)
[OK] All uploaded files (photos, PDFs, documents)
[OK] All configuration files
[OK] All data files (settings, notifications, etc.)
[OK] All images and assets

===========================================
NOTES:
===========================================

- All accounts and passwords are preserved
- All announcements and changes are saved
- All uploaded files are included
- System is ready to use immediately after import

===========================================
"@

$installGuide | Out-File "$backupPath\INSTALLATION_GUIDE.txt" -Encoding UTF8
Write-Host "   [OK] Installation guide created" -ForegroundColor Green

# Create a summary
$summary = @"
BACKUP SUMMARY
==============

Backup Created: $(Get-Date -Format "yyyy-MM-dd HH:mm:ss")
Backup Location: $backupPath

Contents:
- Database: database\barangay_db_backup.sql
- System Files: All PHP, CSS, JS, and configuration files
- Data Files: data\ directory (announcements, settings, etc.)
- Uploads: uploads\ directory (all user uploads)
- Images: images\ directory (all system images)

Total Size: $([math]::Round((Get-ChildItem $backupPath -Recurse | Measure-Object -Property Length -Sum).Sum / 1MB, 2)) MB

Ready for transfer to another laptop/device!
"@

$summary | Out-File "$backupPath\BACKUP_SUMMARY.txt" -Encoding UTF8

Write-Host "`n========================================" -ForegroundColor Green
Write-Host "BACKUP COMPLETE!" -ForegroundColor Green
Write-Host "========================================" -ForegroundColor Green
Write-Host "Location: $backupPath" -ForegroundColor Yellow
Write-Host "Total Size: $([math]::Round((Get-ChildItem $backupPath -Recurse | Measure-Object -Property Length -Sum).Sum / 1MB, 2)) MB" -ForegroundColor Yellow
Write-Host "`nReady to transfer to another laptop!" -ForegroundColor Green
Write-Host "`nPress any key to exit..."
$null = $Host.UI.RawUI.ReadKey("NoEcho,IncludeKeyDown")


-- ============================================================
-- BARANGAY SAN VICENTE II - Complete Database Schema
-- Generated for phpMyAdmin / MySQL
-- ============================================================

-- Create Database
CREATE DATABASE IF NOT EXISTS barangay_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE barangay_db;

-- ============================================================
-- TABLE: residents
-- Main resident information table
-- ============================================================
CREATE TABLE IF NOT EXISTS residents (
    id INT AUTO_INCREMENT PRIMARY KEY,

    -- BASIC INFO
    photo VARCHAR(255) DEFAULT NULL,
    first_name VARCHAR(80) NOT NULL,
    middle_name VARCHAR(80) DEFAULT NULL,
    last_name VARCHAR(80) NOT NULL,
    suffix VARCHAR(20) DEFAULT NULL,
    gender ENUM('Female','Male','Other') DEFAULT NULL,
    date_of_birth DATE DEFAULT NULL,
    place_of_birth VARCHAR(150) DEFAULT NULL,
    civil_status ENUM('Single','Married','Widowed','Separated') DEFAULT NULL,
    religion VARCHAR(80) DEFAULT NULL,
    nationality VARCHAR(80) DEFAULT NULL,

    -- ADDRESS
    home_number VARCHAR(50) DEFAULT NULL,
    street VARCHAR(150) DEFAULT NULL,
    barangay VARCHAR(150) DEFAULT NULL,
    municipality VARCHAR(150) DEFAULT NULL,
    city_province VARCHAR(150) DEFAULT NULL,

    -- CONTACT INFO
    contact_number VARCHAR(50) DEFAULT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,

    -- Profile completion status
    profile_complete ENUM('Yes','No') DEFAULT 'No',

    -- PARENTS / GUARDIAN
    father_name VARCHAR(150) DEFAULT NULL,
    mother_name VARCHAR(150) DEFAULT NULL,
    guardian_name VARCHAR(150) DEFAULT NULL,
    guardian_contact VARCHAR(50) DEFAULT NULL,

    -- VOTER STATUS
    voter ENUM('Yes','No') DEFAULT NULL,
    registered_voter ENUM('Yes','No') DEFAULT NULL,
    voter_in_barangay ENUM('Yes','No') DEFAULT NULL,
    precinct_number VARCHAR(50) DEFAULT NULL,

    -- SOCIAL CATEGORY (LGU STANDARD)
    senior_citizen ENUM('Yes','No') DEFAULT NULL,
    pwd ENUM('Yes','No') DEFAULT NULL,
    disability_type VARCHAR(150) DEFAULT NULL,
    solo_parent ENUM('Yes','No') DEFAULT NULL,
    youth_member ENUM('Yes','No') DEFAULT NULL,
    ip_member ENUM('Yes','No') DEFAULT NULL,
    ofw_household_member ENUM('Yes','No') DEFAULT NULL,
    pregnant_woman ENUM('Yes','No') DEFAULT NULL,
    lactating_mother ENUM('Yes','No') DEFAULT NULL,
    chronic_illness ENUM('Yes','No') DEFAULT NULL,
    immunization_updated ENUM('Yes','No') DEFAULT NULL,
    emergency_risk_level ENUM('Low','Medium','High') DEFAULT NULL,

    -- EDUCATION
    currently_studying ENUM('Yes','No') DEFAULT NULL,
    grade_year_level VARCHAR(100) DEFAULT NULL,
    highest_educ_attainment VARCHAR(150) DEFAULT NULL,

    -- CONTACT PERSON (EMERGENCY)
    emergency_contact_person VARCHAR(150) DEFAULT NULL,
    emergency_relationship VARCHAR(100) DEFAULT NULL,
    emergency_contact_number VARCHAR(50) DEFAULT NULL,

    -- RESIDENCY
    years_of_residency INT DEFAULT NULL,
    resident_type ENUM('Permanent', 'Transient') DEFAULT NULL,
    household_number VARCHAR(100) DEFAULT NULL,
    household_head ENUM('Yes','No') DEFAULT NULL,
    resident_since_birth ENUM('Yes','No') DEFAULT NULL,

    -- SOURCE OF INCOME (MAIN CATEGORY)
    income_category ENUM(
        'Employed', 'Self-Employed', 'Unemployed',
        'Student', 'Pensioner', 'OFW', 'Dependent'
    ) DEFAULT NULL,

    -- IF EMPLOYED
    occupation VARCHAR(150) DEFAULT NULL,
    work_department VARCHAR(150) DEFAULT NULL,
    employer VARCHAR(150) DEFAULT NULL,
    type_of_work ENUM('Private','Government','Overseas') DEFAULT NULL,
    monthly_income_range VARCHAR(100) DEFAULT NULL,

    -- IF SELF-EMPLOYED
    business_type VARCHAR(150) DEFAULT NULL,
    business_name VARCHAR(150) DEFAULT NULL,
    business_income_range VARCHAR(100) DEFAULT NULL,

    -- IF STUDENT
    school_name VARCHAR(150) DEFAULT NULL,
    student_year_level VARCHAR(100) DEFAULT NULL,
    student_supported_by ENUM('Parents','Scholarship') DEFAULT NULL,

    -- IF PENSIONER
    pension_type ENUM('SSS','GSIS','Private') DEFAULT NULL,
    monthly_pension VARCHAR(100) DEFAULT NULL,

    -- IF OFW
    ofw_country VARCHAR(150) DEFAULT NULL,
    ofw_job_position VARCHAR(150) DEFAULT NULL,
    ofw_agency VARCHAR(150) DEFAULT NULL,

    -- SOCIAL ASSISTANCE
    beneficiary_4ps ENUM('Yes','No') DEFAULT NULL,
    social_pension_beneficiary ENUM('Yes','No') DEFAULT NULL,
    sss_pensioner ENUM('Yes','No') DEFAULT NULL,
    gsis_pensioner ENUM('Yes','No') DEFAULT NULL,
    philhealth_member ENUM('Yes','No') DEFAULT NULL,
    other_cash_assistance ENUM('Yes','No') DEFAULT NULL,
    child_4ps_beneficiary ENUM('Yes','No') DEFAULT NULL,
    disaster_vulnerable ENUM('Yes','No') DEFAULT NULL,
    evacuation_assistance ENUM('Yes','No') DEFAULT NULL,
    livelihood_assistance ENUM('Yes','No') DEFAULT NULL,
    registered_farmer ENUM('Yes','No') DEFAULT NULL,
    registered_fisherfolk ENUM('Yes','No') DEFAULT NULL,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    INDEX idx_email (email),
    INDEX idx_last_name (last_name),
    INDEX idx_barangay (barangay)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- ============================================================
-- TABLE: family_members
-- Family members linked to a resident
-- ============================================================
CREATE TABLE IF NOT EXISTS family_members (
    id INT AUTO_INCREMENT PRIMARY KEY,
    resident_id INT NOT NULL,
    first_name VARCHAR(100) NOT NULL,
    middle_name VARCHAR(100) DEFAULT NULL,
    last_name VARCHAR(100) NOT NULL,
    gender ENUM('Male','Female','Other') NOT NULL,
    date_of_birth DATE NOT NULL,
    place_of_birth VARCHAR(255) DEFAULT NULL,
    civil_status ENUM('Single','Married','Widowed','Divorced','Separated') DEFAULT NULL,
    voter ENUM('Yes','No') DEFAULT NULL,
    pwd ENUM('Yes','No') DEFAULT NULL,
    solo_parent ENUM('Yes','No') DEFAULT NULL,
    relationship ENUM('Mother', 'Father', 'Grandmother', 'Grandfather', 'Sister', 'Brother', 'Son', 'Daughter', 'Spouse', 'Other') NOT NULL,
    contact_number VARCHAR(20) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (resident_id) REFERENCES residents(id) ON DELETE CASCADE,
    INDEX idx_resident_id (resident_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- ============================================================
-- TABLE: admins
-- Admin users for the barangay system
-- ============================================================
CREATE TABLE IF NOT EXISTS admins (
    id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    photo VARCHAR(255) DEFAULT NULL,
    role ENUM('super_admin', 'regular_admin') DEFAULT 'regular_admin',
    status ENUM('active', 'pending', 'suspended') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    INDEX idx_email (email),
    INDEX idx_role (role),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- ============================================================
-- TABLE: document_requests
-- Document requests from residents
-- ============================================================
CREATE TABLE IF NOT EXISTS document_requests (
    id INT AUTO_INCREMENT PRIMARY KEY,
    resident_id INT DEFAULT NULL,
    fullname VARCHAR(255) NOT NULL,
    document_type VARCHAR(100) NOT NULL,
    address VARCHAR(500) DEFAULT NULL,
    purpose VARCHAR(500) DEFAULT NULL,
    pdf_name VARCHAR(255) DEFAULT NULL,
    date_requested DATETIME DEFAULT CURRENT_TIMESTAMP,
    status ENUM('Pending', 'Processing', 'Ready to Pick Up', 'Completed', 'Rejected') DEFAULT 'Pending',
    file VARCHAR(255) DEFAULT NULL,
    admin_notes TEXT DEFAULT NULL,
    processed_by INT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (resident_id) REFERENCES residents(id) ON DELETE SET NULL,
    FOREIGN KEY (processed_by) REFERENCES admins(id) ON DELETE SET NULL,
    INDEX idx_resident_id (resident_id),
    INDEX idx_status (status),
    INDEX idx_date_requested (date_requested)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- ============================================================
-- TABLE: walk_in_requests
-- Walk-in requests processed at the barangay office
-- ============================================================
CREATE TABLE IF NOT EXISTS walk_in_requests (
    request_id INT AUTO_INCREMENT PRIMARY KEY,
    request_number VARCHAR(50) UNIQUE NOT NULL,
    resident_id INT DEFAULT NULL,
    resident_name VARCHAR(255) NOT NULL,
    resident_email VARCHAR(255) DEFAULT NULL,
    resident_contact VARCHAR(50) DEFAULT NULL,
    category VARCHAR(100) NOT NULL,
    type VARCHAR(100) NOT NULL,
    description TEXT DEFAULT NULL,
    attachment_url VARCHAR(500) DEFAULT NULL,
    status ENUM('Pending', 'Under Review', 'Completed', 'Rejected') DEFAULT 'Pending',
    admin_notes TEXT DEFAULT NULL,
    admin_attachment_url VARCHAR(500) DEFAULT NULL,
    appointment_date DATE DEFAULT NULL,
    appointment_time TIME DEFAULT NULL,
    processed_by INT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (resident_id) REFERENCES residents(id) ON DELETE SET NULL,
    FOREIGN KEY (processed_by) REFERENCES admins(id) ON DELETE SET NULL,
    INDEX idx_status (status),
    INDEX idx_request_number (request_number),
    INDEX idx_created_at (created_at),
    INDEX idx_resident_id (resident_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- ============================================================
-- TABLE: walk_in_requests_archive
-- Archived walk-in requests
-- ============================================================
CREATE TABLE IF NOT EXISTS walk_in_requests_archive (
    archive_id INT AUTO_INCREMENT PRIMARY KEY,
    original_request_id INT NOT NULL,
    request_number VARCHAR(50) NOT NULL,
    resident_id INT DEFAULT NULL,
    resident_name VARCHAR(255) NOT NULL,
    resident_email VARCHAR(255) DEFAULT NULL,
    resident_contact VARCHAR(50) DEFAULT NULL,
    category VARCHAR(100) NOT NULL,
    type VARCHAR(100) NOT NULL,
    description TEXT DEFAULT NULL,
    attachment_url VARCHAR(500) DEFAULT NULL,
    status ENUM('Pending', 'Under Review', 'Completed', 'Rejected') DEFAULT 'Pending',
    admin_notes TEXT DEFAULT NULL,
    admin_attachment_url VARCHAR(500) DEFAULT NULL,
    appointment_date DATE DEFAULT NULL,
    appointment_time TIME DEFAULT NULL,
    processed_by INT DEFAULT NULL,
    created_at DATETIME DEFAULT NULL,
    updated_at DATETIME DEFAULT NULL,
    archived_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    archived_by INT DEFAULT NULL,
    restored_at DATETIME DEFAULT NULL,
    restored_by INT DEFAULT NULL,

    INDEX idx_original_request_id (original_request_id),
    INDEX idx_request_number (request_number),
    INDEX idx_status (status),
    INDEX idx_archived_at (archived_at),
    INDEX idx_restored_at (restored_at),
    INDEX idx_resident_id (resident_id),
    INDEX idx_archived_by (archived_by)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ============================================================
-- TABLE: barangay_workers
-- Barangay staff/workers
-- ============================================================
CREATE TABLE IF NOT EXISTS barangay_workers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    position VARCHAR(100) NOT NULL,
    mobile VARCHAR(50) DEFAULT NULL,
    email VARCHAR(255) DEFAULT NULL UNIQUE,
    password VARCHAR(255) DEFAULT NULL,
    messenger VARCHAR(255) DEFAULT NULL,
    reset_token VARCHAR(64) DEFAULT NULL,
    reset_token_expires DATETIME DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    created_by INT DEFAULT NULL,

    FOREIGN KEY (created_by) REFERENCES admins(id) ON DELETE SET NULL,
    INDEX idx_position (position),
    INDEX idx_email (email),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- ============================================================
-- TABLE: worker_responsibilities
-- Responsibilities assigned to workers
-- ============================================================
CREATE TABLE IF NOT EXISTS worker_responsibilities (
    id INT AUTO_INCREMENT PRIMARY KEY,
    worker_id INT NOT NULL,
    responsibility_type ENUM('Document Processing', 'Resident Verification', 'Complaints Handling', 'Financial Tasks', 'Patrol Duties', 'Health Services', 'Other') NOT NULL,
    description TEXT DEFAULT NULL,
    status ENUM('Active', 'Inactive') DEFAULT 'Active',
    assigned_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    assigned_by INT DEFAULT NULL,

    FOREIGN KEY (worker_id) REFERENCES barangay_workers(id) ON DELETE CASCADE,
    FOREIGN KEY (assigned_by) REFERENCES admins(id) ON DELETE SET NULL,
    INDEX idx_worker_id (worker_id),
    INDEX idx_responsibility_type (responsibility_type),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- ============================================================
-- TABLE: duty_schedules
-- Duty schedules for barangay workers
-- ============================================================
CREATE TABLE IF NOT EXISTS duty_schedules (
    id INT AUTO_INCREMENT PRIMARY KEY,
    worker_id INT NOT NULL,
    schedule_type ENUM('Weekly', 'Monthly', 'One-time') DEFAULT 'Weekly',
    day_of_week ENUM('Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday') DEFAULT NULL,
    date_specific DATE DEFAULT NULL,
    start_time TIME NOT NULL,
    end_time TIME NOT NULL,
    duty_type ENUM('Office Duty', 'Patrol', 'Health Services', 'Event Assignment', 'Other') NOT NULL,
    notes TEXT DEFAULT NULL,
    status ENUM('Active', 'Completed', 'Cancelled') DEFAULT 'Active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    created_by INT DEFAULT NULL,

    FOREIGN KEY (worker_id) REFERENCES barangay_workers(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES admins(id) ON DELETE SET NULL,
    INDEX idx_worker_id (worker_id),
    INDEX idx_schedule_type (schedule_type),
    INDEX idx_date_specific (date_specific),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- ============================================================
-- TABLE: staff_tasks
-- Tasks assigned to barangay staff
-- ============================================================
CREATE TABLE IF NOT EXISTS staff_tasks (
    id INT AUTO_INCREMENT PRIMARY KEY,
    worker_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    description TEXT DEFAULT NULL,
    status ENUM('Pending', 'In Progress', 'Completed', 'Cancelled') DEFAULT 'Pending',
    priority ENUM('Low', 'Medium', 'High', 'Urgent') DEFAULT 'Medium',
    deadline DATE DEFAULT NULL,
    completed_at TIMESTAMP NULL DEFAULT NULL,
    assigned_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    assigned_by INT DEFAULT NULL,

    FOREIGN KEY (worker_id) REFERENCES barangay_workers(id) ON DELETE CASCADE,
    FOREIGN KEY (assigned_by) REFERENCES admins(id) ON DELETE SET NULL,
    INDEX idx_worker_id (worker_id),
    INDEX idx_status (status),
    INDEX idx_priority (priority),
    INDEX idx_deadline (deadline)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- ============================================================
-- TABLE: activity_log
-- System activity logging for auditing
-- ============================================================
CREATE TABLE IF NOT EXISTS activity_log (
    id INT AUTO_INCREMENT PRIMARY KEY,
    admin_id INT DEFAULT NULL,
    resident_id INT DEFAULT NULL,
    worker_id INT DEFAULT NULL,
    action_type VARCHAR(50) NOT NULL,
    table_name VARCHAR(100) DEFAULT NULL,
    record_id INT DEFAULT NULL,
    description TEXT DEFAULT NULL,
    ip_address VARCHAR(45) DEFAULT NULL,
    user_agent TEXT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (admin_id) REFERENCES admins(id) ON DELETE SET NULL,
    FOREIGN KEY (resident_id) REFERENCES residents(id) ON DELETE SET NULL,
    FOREIGN KEY (worker_id) REFERENCES barangay_workers(id) ON DELETE SET NULL,
    INDEX idx_admin_id (admin_id),
    INDEX idx_resident_id (resident_id),
    INDEX idx_action_type (action_type),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- ============================================================
-- TABLE: contact_messages
-- Contact form messages from residents/visitors
-- ============================================================
CREATE TABLE IF NOT EXISTS contact_messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    resident_id INT DEFAULT NULL,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL,
    subject VARCHAR(255) DEFAULT NULL,
    message TEXT NOT NULL,
    status ENUM('Unread', 'Read', 'Replied', 'Archived') DEFAULT 'Unread',
    replied_by INT DEFAULT NULL,
    replied_at DATETIME DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (resident_id) REFERENCES residents(id) ON DELETE SET NULL,
    FOREIGN KEY (replied_by) REFERENCES admins(id) ON DELETE SET NULL,
    INDEX idx_status (status),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- ============================================================
-- TABLE: notifications
-- System notifications for residents and admins
-- ============================================================
CREATE TABLE IF NOT EXISTS notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_type ENUM('resident', 'admin', 'worker') NOT NULL,
    user_id INT NOT NULL,
    type VARCHAR(100) NOT NULL,
    title VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    data JSON DEFAULT NULL,
    is_read TINYINT(1) DEFAULT 0,
    read_at DATETIME DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    INDEX idx_user (user_type, user_id),
    INDEX idx_is_read (is_read),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- ============================================================
-- INSERT DEFAULT SUPER ADMIN ACCOUNTS
-- These accounts are permanent and cannot be deleted
-- ============================================================

-- Default Super Admin Account 1
-- Email: admin@barangaysanvicente.com
-- Password: admin123 (hashed)
INSERT INTO admins (full_name, email, password, role, status) VALUES
('Super Admin', 'admin@barangaysanvicente.com', '$2y$10$2Aa7MtbZfc6wKxsQBWhqLOLP5QLL4OVoyqPFL.zhzpc2Ax3urpk2q', 'super_admin', 'active')
ON DUPLICATE KEY UPDATE 
    full_name = 'Super Admin',
    password = '$2y$10$2Aa7MtbZfc6wKxsQBWhqLOLP5QLL4OVoyqPFL.zhzpc2Ax3urpk2q',
    role = 'super_admin',
    status = 'active';

-- Default Super Admin Account 2 (Permanent - Cannot be deleted)
-- Email: weh@gmail.com
-- Password: sanaolbaliw (hashed)
-- This account is automatically created and protected from deletion
INSERT INTO admins (full_name, email, password, role, status) VALUES
('Super Admin', 'weh@gmail.com', '$2y$10$XE1CaWfl2xRnVhcLkP9CCeZbfAg2hi4PHV7g3Ov6g9Jm39bQUOTmq', 'super_admin', 'active')
ON DUPLICATE KEY UPDATE 
    full_name = 'Super Admin',
    password = '$2y$10$XE1CaWfl2xRnVhcLkP9CCeZbfAg2hi4PHV7g3Ov6g9Jm39bQUOTmq',
    role = 'super_admin',
    status = 'active';

-- ============================================================
-- END OF DATABASE SCHEMA
-- ============================================================

<?php
// register.php
// Ensure no output before headers
ob_start();
session_start();
require 'db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

// sanitize helper
function s($v) {
    return trim($v ?? '');
}

$voter = s($_POST['voter'] ?? null);
$gender = s($_POST['gender'] ?? null);
$date_of_birth = s($_POST['date_of_birth'] ?? null);
$place_of_birth = s($_POST['place_of_birth'] ?? null);
$pwd = s($_POST['pwd'] ?? null);
$solo_parent = s($_POST['solo_parent'] ?? null);
$first_name = s($_POST['first_name'] ?? null);
$middle_name = s($_POST['middle_name'] ?? null);
$last_name = s($_POST['last_name'] ?? null);
$suffix = s($_POST['suffix'] ?? null);
$civil_status = s($_POST['civil_status'] ?? null);
$religion = s($_POST['religion'] ?? null);
$nationality = s($_POST['nationality'] ?? null);

$home_number = s($_POST['home_number'] ?? null);
$street = s($_POST['street'] ?? null);
// Force all registrations to the fixed barangay.
$barangay = 'San Vicente II';
$municipality = s($_POST['municipality'] ?? null);
$city_province = s($_POST['city_province'] ?? null);
$contact_number = s($_POST['contact_number'] ?? null);

$father_name = s($_POST['father_name'] ?? null);
$mother_name = s($_POST['mother_name'] ?? null);
$guardian_name = s($_POST['guardian_name'] ?? null);
$guardian_contact = s($_POST['guardian_contact'] ?? null);

$display_name = s($_POST['display_name'] ?? null);
$email = s($_POST['email'] ?? null);
$password = $_POST['password'] ?? '';
$confirm_password = $_POST['confirm_password'] ?? '';

// Basic validation
$errors = [];
if (!$first_name || !$last_name) $errors[] = 'First and last name are required.';
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Invalid email.';
if (strlen($password) < 6) $errors[] = 'Password must be at least 6 characters.';
if ($password !== $confirm_password) $errors[] = 'Passwords do not match.';

if (!empty($errors)) {
    ob_end_clean();
    $_SESSION['reg_errors'] = $errors;
    header('Location: index.php');
    exit;
}

// check if email exists
$stmt = $pdo->prepare("SELECT id FROM residents WHERE email = ?");
$stmt->execute([$email]);
if ($stmt->fetch()) {
    ob_end_clean();
    $_SESSION['reg_errors'] = ['Email already registered.'];
    header('Location: index.php');
    exit;
}

// handle photo upload (optional)
$photo_path = null;

if (!empty($_FILES['photo']) && $_FILES['photo']['error'] !== UPLOAD_ERR_NO_FILE) {
    $file = $_FILES['photo'];

    if ($file['error'] === UPLOAD_ERR_OK) {

        $allowed = ['image/jpeg','image/png','image/webp','image/gif'];
        if (!in_array($file['type'], $allowed)) {
            ob_end_clean();
            $_SESSION['reg_errors'] = ['Invalid photo type.'];
            header('Location: index.php'); exit;
        }

        $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = 'photo_' . time() . '_' . bin2hex(random_bytes(5)) . '.' . $ext;

        // Ensure uploads folder exists
        if (!is_dir('uploads')) mkdir('uploads', 0755, true);

        $target = 'uploads/' . $filename;

        if (!move_uploaded_file($file['tmp_name'], $target)) {
            ob_end_clean();
            $_SESSION['reg_errors'] = ['Failed to save uploaded photo.'];
            header('Location: index.php'); exit;
        }

        // Save ONLY the filename in database
        $photo_path = $filename;
    }
}

// hash password for login security
$hash = password_hash($password, PASSWORD_DEFAULT);

// Insert into residents table (removed confirm_password - not in database)
$insert = "INSERT INTO residents
(photo, voter, gender, date_of_birth, place_of_birth, pwd, solo_parent, first_name, middle_name, last_name, suffix, civil_status, religion, nationality, home_number, street, barangay, municipality, city_province, contact_number, father_name, mother_name, guardian_name, guardian_contact, email, password)
VALUES
(?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)";

$stmt = $pdo->prepare($insert);
$params = [
    $photo_path,
    $voter,
    $gender,
    $date_of_birth ?: null,
    $place_of_birth,
    $pwd,
    $solo_parent,
    $first_name,
    $middle_name,
    $last_name,
    $suffix,
    $civil_status,
    $religion,
    $nationality,
    $home_number,
    $street,
    $barangay,
    $municipality,
    $city_province,
    $contact_number,
    $father_name,
    $mother_name,
    $guardian_name,
    $guardian_contact,
    $email,
    $hash
];

try {
    $stmt->execute($params);
    
    // Get the inserted user ID to verify
    $new_user_id = $pdo->lastInsertId();
    
    ob_end_clean();
    $_SESSION['reg_success'] = 'Registration successful! You can now log in with your email and password.';
    header('Location: index.php');
    exit;
} catch (PDOException $e) {
    // Better error handling - show actual database error for debugging
    ob_end_clean();
    $error_msg = 'Registration failed. ';
    if (strpos($e->getMessage(), 'Duplicate entry') !== false) {
        $error_msg .= 'Email already exists.';
    } else {
        $error_msg .= $e->getMessage();
    }
    $_SESSION['reg_errors'] = [$error_msg];
    header('Location: index.php');
    exit;
} catch (Exception $e) {
    ob_end_clean();
    $_SESSION['reg_errors'] = ['Registration failed: ' . $e->getMessage()];
    header('Location: index.php');
    exit;
}

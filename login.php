<?php
// login.php - Handles resident login from index.php modal
// Ensure no output before headers
ob_start();
session_start();
require 'config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    ob_end_clean();
    header('Location: index.php');
    exit;
}

$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';

// Basic validation
if (!filter_var($email, FILTER_VALIDATE_EMAIL) || !$password) {
    ob_end_clean();
    $_SESSION['login_error'] = 'Please provide a valid email and password.';
    header('Location: index.php');
    exit;
}

// Check if user is trying to login as admin (check admin table first)
$stmt = $pdo->prepare("SELECT * FROM admins WHERE email = ?");
$stmt->execute([$email]);
$admin = $stmt->fetch(PDO::FETCH_ASSOC);

if ($admin && password_verify($password, $admin['password'])) {
    // Admin login detected - redirect to admin login handler
    ob_end_clean();
    $_SESSION['login_error'] = 'Please use Admin Login for admin accounts.';
    header('Location: index.php');
    exit;
}

// Resident login
try {
    $stmt = $pdo->prepare("SELECT * FROM residents WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user || !password_verify($password, $user['password'])) {
        ob_end_clean();
        $_SESSION['login_error'] = 'Invalid email or password.';
        header('Location: index.php');
        exit;
    }

    // Successful resident login - set session variables
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['first_name'] = $user['first_name'];
    $_SESSION['role'] = 'resident';
    
    // Regenerate session ID for security (preserves session data)
    session_regenerate_id(true);

    // Clear output buffer and redirect
    ob_end_clean();
    
    // Redirect to resident dashboard
    header("Location: resident/resident_dashboard.php");
    exit;
} catch (PDOException $e) {
    ob_end_clean();
    $_SESSION['login_error'] = 'Database error. Please try again.';
    header('Location: index.php');
    exit;
}

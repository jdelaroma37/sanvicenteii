<?php
// admin_login_handler.php - Handles admin login from index.php modal
session_start();
require 'config.php';

// Security: Regenerate session ID on login page
if (!isset($_SESSION['initiated'])) {
    session_regenerate_id(true);
    $_SESSION['initiated'] = true;
}

// If already logged in as admin, redirect to dashboard
if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin') {
    header('Location: admin/admin_dashboard.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    // Basic validation
    if (!filter_var($email, FILTER_VALIDATE_EMAIL) || !$password) {
        $_SESSION['admin_login_error'] = 'Please provide a valid email and password.';
        header('Location: index.php');
        exit;
    } else {
        // Check admin table
        $stmt = $pdo->prepare("SELECT * FROM admins WHERE email = ?");
        $stmt->execute([$email]);
        $admin = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($admin && password_verify($password, $admin['password'])) {
            $status = $admin['status'] ?? 'active';
            
            if ($status === 'pending') {
                $_SESSION['admin_login_error'] = 'Your account is pending approval. Please wait for a Super Admin to approve your account.';
                header('Location: index.php');
                exit;
            } elseif ($status === 'inactive') {
                $_SESSION['admin_login_error'] = 'Your account has been deactivated. Please contact a Super Admin.';
                header('Location: index.php');
                exit;
            } else {
                $_SESSION['user_id'] = $admin['id'];
                $_SESSION['first_name'] = $admin['full_name'];
                $_SESSION['role'] = 'admin';
                $_SESSION['admin_role'] = $admin['role'] ?? 'regular_admin';
                $_SESSION['email'] = $admin['email'];
                $_SESSION['login_time'] = time();

                try {
                    $update_stmt = $pdo->prepare("UPDATE admins SET last_login = NOW() WHERE id = ?");
                    $update_stmt->execute([$admin['id']]);
                } catch (PDOException $e) {}

                session_regenerate_id(true);
                header('Location: admin/admin_dashboard.php');
                exit;
            }
        } else {
            $_SESSION['admin_login_error'] = 'Invalid email or password.';
            header('Location: index.php');
            exit;
        }
    }
} else {
    header('Location: index.php');
    exit;
}
?>


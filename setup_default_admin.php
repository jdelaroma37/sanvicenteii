<?php
/**
 * Setup Default Super Admin Accounts
 * 
 * This script ensures that the default super admin accounts always exist
 * in the database. Run this script after importing the database or whenever
 * you need to restore the default admin accounts.
 * 
 * Default Accounts:
 * 1. admin@barangaysanvicente.com / admin123
 * 2. weh@gmail.com / sanaolbaliw
 * 
 * These accounts are protected from deletion and will always be available
 * regardless of which device or location the system is deployed to.
 */

// Check if running from command line or web
$is_cli = php_sapi_name() === 'cli';

if (!$is_cli) {
    // Web interface
    echo "<!DOCTYPE html><html><head><title>Setup Default Admin</title>";
    echo "<style>body{font-family:Arial;padding:20px;background:#f5f5f5;}";
    echo ".container{max-width:600px;margin:0 auto;background:white;padding:30px;border-radius:8px;box-shadow:0 2px 10px rgba(0,0,0,0.1);}";
    echo "h1{color:#333;border-bottom:2px solid #4CAF50;padding-bottom:10px;}";
    echo ".success{color:#4CAF50;background:#e8f5e9;padding:15px;border-radius:5px;margin:10px 0;}";
    echo ".error{color:#f44336;background:#ffebee;padding:15px;border-radius:5px;margin:10px 0;}";
    echo ".info{background:#e3f2fd;padding:15px;border-radius:5px;margin:10px 0;color:#1976d2;}";
    echo "code{background:#f5f5f5;padding:2px 6px;border-radius:3px;font-family:monospace;}";
    echo "</style></head><body><div class='container'>";
    echo "<h1>🔧 Setup Default Super Admin Accounts</h1>";
}

require 'config.php';

// Default super admin credentials
$default_admins = [
    [
        'full_name' => 'Super Admin',
        'email' => 'admin@barangaysanvicente.com',
        'password' => 'admin123',
        'role' => 'super_admin',
        'status' => 'active'
    ],
    [
        'full_name' => 'Super Admin',
        'email' => 'weh@gmail.com',
        'password' => 'sanaolbaliw',
        'role' => 'super_admin',
        'status' => 'active'
    ]
];

try {
    $results = [];
    
    foreach ($default_admins as $default_admin) {
        // Hash the password
        $hashed_password = password_hash($default_admin['password'], PASSWORD_DEFAULT);
        
        // Check if account already exists
        $stmt = $pdo->prepare("SELECT id FROM admins WHERE email = ?");
        $stmt->execute([$default_admin['email']]);
        $existing = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($existing) {
            // Update existing account to ensure it's always a super admin and active
            $stmt = $pdo->prepare("
                UPDATE admins 
                SET full_name = ?, 
                    password = ?, 
                    role = 'super_admin', 
                    status = 'active' 
                WHERE email = ?
            ");
            $stmt->execute([
                $default_admin['full_name'],
                $hashed_password,
                $default_admin['email']
            ]);
            $results[] = [
                'action' => 'updated',
                'email' => $default_admin['email'],
                'password' => $default_admin['password']
            ];
        } else {
            // Insert new account
            $stmt = $pdo->prepare("
                INSERT INTO admins (full_name, email, password, role, status) 
                VALUES (?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $default_admin['full_name'],
                $default_admin['email'],
                $hashed_password,
                $default_admin['role'],
                $default_admin['status']
            ]);
            $results[] = [
                'action' => 'created',
                'email' => $default_admin['email'],
                'password' => $default_admin['password']
            ];
        }
    }
    
    // Display results
    foreach ($results as $result) {
        $action = $result['action'] === 'updated' ? 'updated' : 'created';
        $message = "✓ Default super admin account {$action} successfully!<br>";
        $message .= "  <strong>Email:</strong> {$result['email']}<br>";
        $message .= "  <strong>Password:</strong> {$result['password']}<br>";
        $message .= "  <strong>Role:</strong> Super Admin";
        
        if ($is_cli) {
            echo "✓ Default super admin account {$action} successfully!\n";
            echo "  Email: {$result['email']}\n";
            echo "  Password: {$result['password']}\n";
            echo "  Role: Super Admin\n\n";
        } else {
            echo "<div class='success'>{$message}</div>";
        }
    }
    
    $final_message = "✓ Setup completed! Both default super admin accounts are now ready to use.<br>";
    $final_message .= "These accounts are protected from deletion and will always be available.";
    if ($is_cli) {
        echo "\n✓ Setup completed! Both default super admin accounts are now ready to use.\n";
        echo "  These accounts are protected from deletion and will always be available.\n";
    } else {
        echo "<div class='info'>{$final_message}</div>";
        echo "<div class='info'><strong>Default Accounts:</strong><br>";
        echo "1. Email: <code>admin@barangaysanvicente.com</code> | Password: <code>admin123</code><br>";
        echo "2. Email: <code>weh@gmail.com</code> | Password: <code>sanaolbaliw</code></div>";
        echo "</div></body></html>";
    }
    
} catch (PDOException $e) {
    $error_msg = "✗ Error: " . $e->getMessage();
    if ($is_cli) {
        die($error_msg . "\n");
    } else {
        echo "<div class='error'>{$error_msg}</div>";
        echo "</div></body></html>";
        exit;
    }
}
?>


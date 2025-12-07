<?php
// Temporary file to check if account exists - DELETE AFTER TESTING
session_start();
require 'config.php';

$email = 'allysa@gmail.com';

echo "<h1>Account Check for: $email</h1>";

// Check if account exists
$stmt = $pdo->prepare("SELECT id, email, first_name, last_name, password FROM residents WHERE email = ?");
$stmt->execute([$email]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if ($user) {
    echo "<p style='color: green;'>✅ Account FOUND in database!</p>";
    echo "<pre>";
    echo "ID: " . $user['id'] . "\n";
    echo "Email: " . $user['email'] . "\n";
    echo "Name: " . $user['first_name'] . " " . ($user['last_name'] ?? '') . "\n";
    echo "Password Hash: " . substr($user['password'], 0, 20) . "...\n";
    echo "</pre>";
    
    // Test password
    $test_password = 'allysa@123';
    if (password_verify($test_password, $user['password'])) {
        echo "<p style='color: green;'>✅ Password is CORRECT!</p>";
    } else {
        echo "<p style='color: red;'>❌ Password is INCORRECT!</p>";
        echo "<p>Stored hash doesn't match the password you're trying.</p>";
    }
} else {
    echo "<p style='color: red;'>❌ Account NOT FOUND in database!</p>";
    echo "<p>You need to register first.</p>";
}

echo "<hr>";
echo "<p><a href='index.php'>Back to Home</a></p>";
?>


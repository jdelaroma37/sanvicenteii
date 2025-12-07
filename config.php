<?php
/**
 * config.php
 * Ready-to-use configuration for Railway PHP + MySQL
 * Works for both Railway deployment and local testing
 */

// Get environment variables set by Railway
$host = getenv("MYSQLHOST") ?: '127.0.0.1';      // fallback for local testing
$port = getenv("MYSQLPORT") ?: '3306';           // fallback port
$dbname = getenv("MYSQLDATABASE") ?: 'mydb';    // fallback database name
$user = getenv("MYSQLUSER") ?: 'root';          // fallback username
$pass = getenv("MYSQLPASSWORD") ?: '';          // fallback password

try {
    // Include port in DSN
    $conn = new PDO("mysql:host=$host;port=$port;dbname=$dbname;charset=utf8", $user, $pass);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

// Optional: Uncomment for debugging connection
// echo "Connected to database $dbname at $host:$port successfully.";

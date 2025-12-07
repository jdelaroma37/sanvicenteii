<?php
// Get environment variables (Railway sets these)
$host = getenv("MYSQLHOST") ?: '127.0.0.1';        // fallback host
$port = getenv("MYSQLPORT") ?: '3306';             // fallback port
$dbname = getenv("MYSQLDATABASE") ?: 'mydb';      // fallback database name
$user = getenv("MYSQLUSER") ?: 'root';            // fallback username
$pass = getenv("MYSQLPASSWORD") ?: '';            // fallback password

try {
    $conn = new PDO("mysql:host=$host;port=$port;dbname=$dbname", $user, $pass);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

<?php
$host = $_ENV["MYSQLHOST"];
$db   = $_ENV["MYSQLDATABASE"];
$user = $_ENV["MYSQLUSER"];
$pass = $_ENV["MYSQLPASSWORD"];

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}
?>

<?php
/**
 * config.php
 * Railway-ready database configuration using MYSQL_URL
 */

// Get the MySQL connection URL from the environment variable
$mysql_url = getenv("MYSQL_URL");

if (!$mysql_url) {
    die("Error: MYSQL_URL environment variable is not set.");
}

try {
    // Create a PDO connection using the URL
    $conn = new PDO($mysql_url);
    
    // Set PDO to throw exceptions on error
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Optional: for debug purposes
    // echo "Database connected successfully!";
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

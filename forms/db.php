<?php
// database connection helper
$servername = "localhost";
$username   = "your_db_user";      // <--- replace with real credentials
$password   = "your_db_password";  // <--- replace with real credentials
$dbname     = "your_db_name";      // <--- replace with your database

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname;charset=utf8", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    // in production you would log this instead of echoing
    die("Database connection failed: " . $e->getMessage());
}

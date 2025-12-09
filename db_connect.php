<?php
// Database connection
$host = "localhost";
$username = "root";       // Default XAMPP username
$password = "";           // Default XAMPP password
$database = "lifestyl";   // Database name - you might need to create this database

// Create connection
$conn = mysqli_connect($host, $username, $password, $database);

// Check connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Set charset to ensure proper handling of special characters
mysqli_set_charset($conn, "utf8mb4");
?>
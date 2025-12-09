<?php
// Database setup script
$host = "localhost";
$username = "root";
$password = "";

// Connect without database selected
$conn = mysqli_connect($host, $username, $password);

// Check connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Create database if not exists
$sql = "CREATE DATABASE IF NOT EXISTS lifestyl";
if (mysqli_query($conn, $sql)) {
    echo "Database created successfully or already exists<br>";
} else {
    echo "Error creating database: " . mysqli_error($conn) . "<br>";
}

// Select the database
mysqli_select_db($conn, "lifestyl");

// Create cart table
$sql = "CREATE TABLE IF NOT EXISTS cart (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    user_id VARCHAR(128) NOT NULL,
    product_id INT(11) NOT NULL,
    product_name VARCHAR(255) NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    quantity INT(11) NOT NULL DEFAULT 1,
    image VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX (user_id)
)";

if (mysqli_query($conn, $sql)) {
    echo "Cart table created successfully or already exists<br>";
} else {
    echo "Error creating cart table: " . mysqli_error($conn) . "<br>";
}

// Close connection
mysqli_close($conn);

echo "<p>Database setup complete. You can now <a href='index.html'>return to the home page</a>.</p>";
?>
<?php
session_start();
require_once('db_connect.php');

// Process registration form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data and sanitize inputs
    $first_name = mysqli_real_escape_string($conn, $_POST['first_name']);
    $last_name = mysqli_real_escape_string($conn, $_POST['last_name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // Validate form data
    $errors = [];

    // Check if passwords match
    if ($password !== $confirm_password) {
        $errors[] = "Passwords do not match";
    }

    // Validate password strength (at least 8 characters)
    if (strlen($password) < 8) {
        $errors[] = "Password must be at least 8 characters long";
    }

    // Check if email is valid
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Please enter a valid email address";
    }

    // Check if email already exists in the database
    $check_email_query = "SELECT * FROM users WHERE email = '$email'";
    $result = mysqli_query($conn, $check_email_query);

    if (mysqli_num_rows($result) > 0) {
        $errors[] = "Email address is already registered";
    }

    // If there are errors, redirect back to registration form with error messages
    if (!empty($errors)) {
        $_SESSION['register_error'] = implode("<br>", $errors);
        header("Location: login.php");
        exit();
    }

    // Hash the password for secure storage
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    
    // Set default admin status
    $is_admin = 0; // Regular user, not admin

    // Insert user data into the database
    $insert_query = "INSERT INTO users (first_name, last_name, email, password, is_admin) 
                     VALUES ('$first_name', '$last_name', '$email', '$hashed_password', '$is_admin')";

    if (mysqli_query($conn, $insert_query)) {
        // Registration successful
        $_SESSION['register_success'] = "Account created successfully. Please login.";
        header("Location: login.php");
        exit();
    } else {
        // Registration failed
        $_SESSION['register_error'] = "Error creating account: " . mysqli_error($conn);
        header("Location: login.php");
        exit();
    }
} else {
    // If someone tries to access this page directly, redirect to login page
    header("Location: login.php");
    exit();
}
?>
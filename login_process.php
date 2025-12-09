<?php
session_start();
require_once('db_connect.php');

// Process login form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data and sanitize inputs
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = $_POST['password'];
    $remember = isset($_POST['remember']) ? true : false;
    
    // Get redirect parameter if exists
    $redirect = isset($_POST['redirect']) ? $_POST['redirect'] : '';

    // Validate form data
    if (empty($email) || empty($password)) {
        $_SESSION['login_error'] = "Please enter both email and password";
        header("Location: login.php" . ($redirect ? "?redirect=$redirect" : ""));
        exit();
    }

    // Check if user exists in the database
    $query = "SELECT * FROM users WHERE email = '$email'";
    $result = mysqli_query($conn, $query);
    
    // Check if query was successful
    if (!$result) {
        $_SESSION['login_error'] = "Database error: " . mysqli_error($conn);
        header("Location: login.php" . ($redirect ? "?redirect=$redirect" : ""));
        exit();
    }

    if (mysqli_num_rows($result) == 1) {
        $user = mysqli_fetch_assoc($result);
        
        // Verify password
        if (password_verify($password, $user['password'])) {
            // Login successful
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['first_name'] = $user['first_name'];
            $_SESSION['last_name'] = $user['last_name'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['is_admin'] = $user['is_admin']; // Changed role to is_admin to match database
            
            // If remember me is checked, set cookies
            if ($remember) {
                $token = bin2hex(random_bytes(16));
                
                // Store token in database (you might want to create a separate table for this)
                $user_id = $user['user_id'];
                $expiry = date('Y-m-d H:i:s', strtotime('+30 days'));
                
                // You can implement token storage in a separate table if needed
                
                // Set cookies
                setcookie('remember_token', $token, time() + 60*60*24*30, '/');
                setcookie('user_id', $user_id, time() + 60*60*24*30, '/');
            }
            
            // Redirect based on the redirect parameter if it exists
            if ($redirect === 'checkout') {
                header("Location: checkout.php");
            } else {
                // Default redirect to index.html
                header("Location: index.html");
            }
            exit();
        } else {
            // Invalid password
            $_SESSION['login_error'] = "Invalid email or password";
            header("Location: login.php" . ($redirect ? "?redirect=$redirect" : ""));
            exit();
        }
    } else {
        // User not found
        $_SESSION['login_error'] = "Invalid email or password";
        header("Location: login.php" . ($redirect ? "?redirect=$redirect" : ""));
        exit();
    }
} else {
    // If someone tries to access this page directly, redirect to login page
    header("Location: login.php");
    exit();
}
?>
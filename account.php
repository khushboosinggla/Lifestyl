<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Get user details from session
$user_id = $_SESSION['user_id'];
$first_name = $_SESSION['first_name'];
$last_name = $_SESSION['last_name'];
$email = $_SESSION['email'];
$full_name = $first_name . ' ' . $last_name;
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>My Account - LIFE STYLE</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/remixicon/4.6.0/remixicon.min.css">
  <link href="https://cdn.jsdelivr.net/npm/remixicon@4.5.0/fonts/remixicon.css" rel="stylesheet">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Montserrat:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">
  
  <style>
    * {
      font-family: Montserrat, sans-serif;
    }
    .account-section {
      transition: all 0.3s ease;
    }
    .account-section:hover {
      transform: translateY(-2px);
    }
  </style>
</head>
<body class="bg-gray-50 min-h-screen">
  
  <!-- Header -->
  <?php include 'header.php'; ?>
  
  <!-- Main Content -->
  <main class="container mx-auto px-4 py-12">
    <h1 class="text-3xl font-bold mb-8 text-center">My Account</h1>
    
    <div class="max-w-4xl mx-auto">
      <!-- Welcome Message -->
      <div class="bg-white rounded-lg shadow-md p-8 mb-8">
        <div class="flex items-center justify-between mb-6">
          <div>
            <h2 class="text-2xl font-bold">Welcome, <?php echo $full_name; ?>!</h2>
            <p class="text-gray-600">Email: <?php echo $email; ?></p>
          </div>
          <!-- Removed the first logout button -->
        </div>
        <p class="text-gray-600">Manage your account details, orders, and preferences below.</p>
      </div>
      
      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Account Details -->
        <div class="account-section bg-white p-6 rounded-lg shadow-sm">
          <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-semibold">Account Details</h3>
            <button class="text-sm text-gray-500 hover:underline">Edit</button>
          </div>
          <div class="space-y-2">
            <p><span class="font-medium">Name:</span> <?php echo htmlspecialchars($full_name); ?></p>
            <p><span class="font-medium">Email:</span> <?php echo htmlspecialchars($email); ?></p>
            <p><span class="font-medium">Password:</span> ••••••••</p>
          </div>
        </div>
        
        <!-- Order History -->
        <div class="account-section bg-white p-6 rounded-lg shadow-sm">
          <h3 class="text-lg font-semibold mb-4">Order History</h3>
          <div class="text-center py-8">
            <p class="text-gray-500">You haven't placed any orders yet.</p>
            <a href="all-items.html" class="text-primary hover:underline mt-2 inline-block">Start Shopping</a>
          </div>
        </div>
        
        <!-- Saved Addresses -->
        <div class="account-section bg-white p-6 rounded-lg shadow-sm">
          <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-semibold">Saved Addresses</h3>
            <button class="text-sm text-gray-500 hover:underline">Add New</button>
          </div>
          <div class="text-center py-8">
            <p class="text-gray-500">You haven't saved any addresses yet.</p>
          </div>
        </div>
        
        <!-- Payment Methods -->
        <div class="account-section bg-white p-6 rounded-lg shadow-sm">
          <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-semibold">Payment Methods</h3>
            <button class="text-sm text-gray-500 hover:underline">Add New</button>
          </div>
          <div class="text-center py-8">
            <p class="text-gray-500">You haven't saved any payment methods yet.</p>
          </div>
        </div>
      </div>
      
      <!-- Log Out Button -->
      <div class="mt-8 text-center">
        <a href="logout.php" class="bg-black text-white px-6 py-3 rounded hover:bg-gray-800 inline-block">Log Out</a>
      </div>
    </div>
  </main>
  
  <!-- Footer -->
  <?php include 'footer.php'; ?>
  
</body>
</html>
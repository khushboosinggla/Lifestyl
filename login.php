<?php
session_start();

// Check if user is already logged in
if(isset($_SESSION['user_id'])) {
    // If there's a redirect parameter, redirect to that page
    if (isset($_GET['redirect']) && $_GET['redirect'] === 'checkout') {
        header("Location: checkout.php");
    } else {
        header("Location: account.php");
    }
    exit();
}

// Get redirect parameter if it exists
$redirect = isset($_GET['redirect']) ? $_GET['redirect'] : '';

// Flash messages for login/registration errors or success
$login_error = isset($_SESSION['login_error']) ? $_SESSION['login_error'] : '';
$register_error = isset($_SESSION['register_error']) ? $_SESSION['register_error'] : '';
$register_success = isset($_SESSION['register_success']) ? $_SESSION['register_success'] : '';

// Clear session variables
unset($_SESSION['login_error']);
unset($_SESSION['register_error']);
unset($_SESSION['register_success']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login/Signup - LIFE STYLE</title>
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
    .auth-form {
      transition: all 0.5s ease;
    }
    .auth-form:hover {
      transform: translateY(-2px);
    }
    .form-container {
      transition: all 0.5s ease;
      opacity: 1;
      transform: translateY(0);
    }
    .form-container.hidden {
      opacity: 0;
      transform: translateY(20px);
      pointer-events: none;
    }
    .slide-up {
      animation: slideUp 0.5s ease forwards;
    }
    .slide-down {
      animation: slideDown 0.5s ease forwards;
    }
    .alert {
      padding: 0.75rem;
      margin-bottom: 1rem;
      border-radius: 0.25rem;
    }
    .alert-danger {
      background-color: #f8d7da;
      color: #721c24;
      border: 1px solid #f5c6cb;
    }
    .alert-success {
      background-color: #d4edda;
      color: #155724;
      border: 1px solid #c3e6cb;
    }
    @keyframes slideUp {
      from {
        opacity: 0;
        transform: translateY(20px);
      }
      to {
        opacity: 1;
        transform: translateY(0);
      }
    }
    @keyframes slideDown {
      from {
        opacity: 1;
        transform: translateY(0);
      }
      to {
        opacity: 0;
        transform: translateY(20px);
      }
    }
  </style>
  <script>
    document.addEventListener('DOMContentLoaded', function() {
      const loginContainer = document.getElementById('loginContainer');
      const signupContainer = document.getElementById('signupContainer');
      const createAccountBtn = document.getElementById('createAccountBtn');
      const backToLoginBtn = document.getElementById('backToLoginBtn');

      // Initially hide signup form unless there's a register error
      <?php if($register_error): ?>
        loginContainer.classList.add('hidden');
        signupContainer.classList.remove('hidden');
        signupContainer.classList.add('slide-up');
      <?php else: ?>
        signupContainer.classList.add('hidden');
      <?php endif; ?>

      // Show signup form
      createAccountBtn.addEventListener('click', function() {
        loginContainer.classList.add('slide-down');
        setTimeout(() => {
          loginContainer.classList.add('hidden');
          loginContainer.classList.remove('slide-down');
          signupContainer.classList.remove('hidden');
          signupContainer.classList.add('slide-up');
        }, 500);
      });

      // Show login form
      backToLoginBtn.addEventListener('click', function() {
        signupContainer.classList.add('slide-down');
        setTimeout(() => {
          signupContainer.classList.add('hidden');
          signupContainer.classList.remove('slide-down');
          loginContainer.classList.remove('hidden');
          loginContainer.classList.add('slide-up');
        }, 500);
      });
    });
  </script>
</head>
<body class="bg-gray-50 min-h-screen">
  <!-- Header -->
  <!-- Add your header content here -->

  <!-- Main Content -->
  <main class="container mx-auto px-4 py-12">
    <div class="max-w-md mx-auto">
      <!-- Login Form -->
      <div id="loginContainer" class="form-container">
        <div class="text-center mb-8">
          <h1 class="text-3xl font-bold mb-2">Welcome Back</h1>
          <p class="text-gray-600">Sign in to your account</p>
        </div>

        <?php if($login_error): ?>
        <div class="alert alert-danger mb-4">
          <?php echo $login_error; ?>
        </div>
        <?php endif; ?>
        
        <?php if($register_success): ?>
        <div class="alert alert-success mb-4">
          <?php echo $register_success; ?>
        </div>
        <?php endif; ?>

        <div class="auth-form bg-white p-8 rounded-lg shadow-sm">
          <form id="signInForm" action="login_process.php" method="post" class="space-y-4">
            <div>
              <label class="block text-gray-700 mb-2" for="signInEmail">Email Address</label>
              <input type="email" id="signInEmail" name="email" class="w-full px-4 py-2 border rounded focus:outline-none focus:border-primary" placeholder="Enter your email" required>
            </div>
            <div>
              <label class="block text-gray-700 mb-2" for="signInPassword">Password</label>
              <input type="password" id="signInPassword" name="password" class="w-full px-4 py-2 border rounded focus:outline-none focus:border-primary" placeholder="Enter your password" required>
            </div>
            <?php if ($redirect): ?>
            <input type="hidden" name="redirect" value="<?php echo htmlspecialchars($redirect); ?>">
            <?php endif; ?>
            <div class="flex items-center justify-between">
              <div class="flex items-center">
                <input type="checkbox" id="remember" name="remember" class="mr-2">
                <label for="remember" class="text-sm text-gray-600">Remember me</label>
              </div>
              
            </div>
            <button type="submit" class="w-full bg-black text-white py-3 rounded hover:bg-gray-800">Sign In</button>
            <div class="text-center mt-4">
              <p class="text-gray-600">Don't have an account?</p>
              <button type="button" id="createAccountBtn" class="text-primary hover:underline mt-1">Create New Account</button>
            </div>
          </form>
        </div>
      </div>

      <!-- Sign Up Form -->
      <div id="signupContainer" class="form-container">
        <div class="text-center mb-8">
          <h1 class="text-3xl font-bold mb-2">Create Account</h1>
          <p class="text-gray-600">Join our community today</p>
        </div>

        <?php if($register_error): ?>
        <div class="alert alert-danger mb-4">
          <?php echo $register_error; ?>
        </div>
        <?php endif; ?>

        <div class="auth-form bg-white p-8 rounded-lg shadow-sm">
          <form id="signUpForm" action="register_process.php" method="post" class="space-y-4">
            <div>
              <label class="block text-gray-700 mb-2" for="signUpFirstName">First Name</label>
              <input type="text" id="signUpFirstName" name="first_name" class="w-full px-4 py-2 border rounded focus:outline-none focus:border-primary" placeholder="Enter your first name" required>
            </div>
            <div>
              <label class="block text-gray-700 mb-2" for="signUpLastName">Last Name</label>
              <input type="text" id="signUpLastName" name="last_name" class="w-full px-4 py-2 border rounded focus:outline-none focus:border-primary" placeholder="Enter your last name" required>
            </div>
            <div>
              <label class="block text-gray-700 mb-2" for="signUpEmail">Email Address</label>
              <input type="email" id="signUpEmail" name="email" class="w-full px-4 py-2 border rounded focus:outline-none focus:border-primary" placeholder="Enter your email" required>
            </div>
            <div>
              <label class="block text-gray-700 mb-2" for="signUpPassword">Password</label>
              <input type="password" id="signUpPassword" name="password" class="w-full px-4 py-2 border rounded focus:outline-none focus:border-primary" placeholder="Create a password" required>
            </div>
            <div>
              <label class="block text-gray-700 mb-2" for="confirmPassword">Confirm Password</label>
              <input type="password" id="confirmPassword" name="confirm_password" class="w-full px-4 py-2 border rounded focus:outline-none focus:border-primary" placeholder="Confirm your password" required>
            </div>
            <div class="flex items-center">
              <input type="checkbox" id="terms" name="terms" class="mr-2" required>
              <label for="terms" class="text-sm text-gray-600">I agree to the <a href="#" class="text-primary hover:underline">Terms of Service</a> and <a href="#" class="text-primary hover:underline">Privacy Policy</a></label>
            </div>
            <button type="submit" class="w-full bg-black text-white py-3 rounded hover:bg-gray-800">Create Account</button>
            <div class="text-center mt-4">
              <p class="text-gray-600">Already have an account?</p>
              <button type="button" id="backToLoginBtn" class="text-primary hover:underline mt-1">Back to Login</button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </main>

  <!-- Footer -->
  <!-- Add your footer content here -->
</body>
</html>
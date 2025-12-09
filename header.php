<?php
// Start session to access cart and wishlist data only if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Include cart functions to get cart count
require_once 'cart_functions.php';

// Get cart and wishlist counts
$cartCount = isset($_SESSION['cart']) ? count($_SESSION['cart']) : 0;
$wishlistCount = isset($_SESSION['wishlist']) ? count($_SESSION['wishlist']) : 0;
?>
<header class="border-b">
  <div class="container mx-auto px-4">
    <div class="h-16 flex items-center justify-between">
      <h1 class="font-['Montserrat, sans-serif;'] text-2xl"><b><a href="index.html">LIFE STYLE</a></b></h1>
      <div class="flex-1 max-w-xl mx-8">
        <div class="relative">
          <input type="text" placeholder="Search here..." class="w-full h-10 pl-10 pr-4 rounded-full border border-gray-200 focus:outline-none focus:border-primary">
          <i class="ri-search-line absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
        </div>
      </div>
      <div class="flex items-center gap-6">
        <div class="relative group cursor-pointer">
          <div class="font-['Montserrat, sans-serif;'] flex items-center gap-1">
            <a href="help.html" class="flex items-center gap-1">
              <i class="ri-customer-service-line text-xl"></i>
              <span>24/7 HELP</span>
              <i class="ri-arrow-down-s-line"></i>
            </a>
          </div>
        </div>
        <div class="font-['Montserrat, sans-serif;'] flex items-center gap-1 cursor-pointer">
          <a href="login.php" class="flex items-center gap-1">
            <i class="ri-user-line text-xl"></i>
            <span>YOUR ACCOUNT</span>
          </a>
        </div>
        <div class="flex items-center gap-1 cursor-pointer relative">
          <a href="wishlist.php" class="flex items-center gap-1">
            <i class="ri-heart-line text-xl"></i>
            <span>WISHLIST</span>
            <span class="wishlist-count absolute -top-2 -right-2 bg-black text-white text-xs rounded-full w-4 h-4 flex items-center justify-center"><?= $wishlistCount ?></span>
          </a>
        </div>
        <div class="relative cursor-pointer">
          <a href="cart.php" class="nav-btn flex items-center gap-1">
            <i class="ri-shopping-bag-line text-xl"></i>
            <span class="cart-count absolute -top-2 -right-2 bg-black text-white text-xs rounded-full w-4 h-4 flex items-center justify-center"><?= $cartCount ?></span>
            <span class="ml-1 font-medium">CART</span>
          </a>
        </div>
      </div>
    </div>
    <hr>
    <nav class="h-10 flex items-center justify-center">
      <ul class="flex gap-8">
        <li><a href="index.html" class="hover:text-primary">HOME</a></li>
        <li><a href="all-items.html" class="hover:text-primary">SHOP</a></li>
        <li><a href="a1.html" class="hover:text-primary">WOMEN</a></li>
        <li><a href="trythishitagain.html" class="hover:text-primary">MEN</a></li>
        <li><a href="mainpage.html" class="hover:text-primary">KIDS</a></li>
        <li><a href="sale1.html" class="hover:text-primary">SALE</a></li>
        <li><a href="aboutus.html" class="hover:text-primary">ABOUT US</a></li>
      </ul>
    </nav>
  </div>  
</header>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/remixicon/4.6.0/remixicon.min.css">
<link href="https://cdn.jsdelivr.net/npm/remixicon@4.5.0/fonts/remixicon.css" rel="stylesheet">
<!-- Font Awesome for wishlist icon -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

<style>
.nav-icon {
  position: relative;
  display: inline-flex;
  align-items: center;
  padding: 4px;
  cursor: pointer;
}

.nav-icon i {
  font-size: 1.25rem;
}

.nav-icon .badge {
  position: absolute;
  top: -8px;
  right: -8px;
  background-color: black;
  color: white;
  font-size: 0.75rem;
  width: 16px;
  height: 16px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
}

#wishlist-icon:hover {
  color: #e53e3e;
}
</style>

<!-- Cart and wishlist synchronization script -->
<script src="cart_sync.js"></script>
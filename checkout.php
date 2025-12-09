<?php
// Include cart functions
require_once 'cart_functions.php';

// Process form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['place_order'])) {
    // In a real implementation, you would:
    // 1. Validate all form inputs
    // 2. Process payment
    // 3. Save order to database
    // 4. Send confirmation email
    
    // For now, we'll just clear the cart and show success
    $orderPlaced = true;
    $orderNumber = 'ORD-' . date('YmdHis') . '-' . rand(1000, 9999);
    $_SESSION['last_order_number'] = $orderNumber;
    clearCart();
} else {
    $orderPlaced = false;
}

// Get cart data
$cart = getCart();
$subtotal = getCartSubtotal();
$tax = $subtotal * 0.18; // 18% tax
$shipping = ($subtotal > 999) ? 0 : 99; // Free shipping over ₹999
$total = $subtotal + $tax + $shipping;
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?php echo $orderPlaced ? 'Order Confirmation' : 'Checkout'; ?> - LIFE STYLE</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/remixicon/4.6.0/remixicon.min.css">
  <link href="https://fonts.googleapis.com/css2?family=Montserrat:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">
  <script src="cart.js"></script>
  <style>
    * {
      font-family: Montserrat, sans-serif;
    }
    .checkout-section {
      transition: all 0.3s ease;
    }
    .checkout-section:hover {
      transform: translateY(-2px);
    }
    .payment-option {
      transition: all 0.3s ease;
      border: 1px solid #e5e7eb;
    }
    .payment-option:hover, .payment-option.selected {
      border-color: #000;
    }
    .payment-logos img {
      height: 24px;
      margin: 0 8px;
      object-fit: contain;
    }
  </style>
</head>
<body class="bg-gray-50 min-h-screen">
  <!-- Header -->
  <header class="border-b bg-white">
    <div class="container mx-auto px-4">
      <div class="h-16 flex items-center justify-between">
        <h1 class="text-2xl font-bold"><a href="index.html">LIFE STYLE</a></h1>
        <div class="flex-1 max-w-xl mx-8">
          <div class="relative">
            <input type="text" placeholder="Search here..." class="w-full h-10 pl-10 pr-4 rounded-full border border-gray-200 focus:outline-none focus:border-primary">
            <i class="ri-search-line absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
          </div>
        </div>
        <div class="flex items-center gap-6">
          <div class="relative group cursor-pointer">
            <div class="flex items-center gap-1">
              <a href="help.html" class="flex items-center gap-1">
                <i class="ri-customer-service-line text-xl"></i>
                <span>24/7 HELP</span>
                <i class="ri-arrow-down-s-line"></i>
              </a>
            </div>
          </div>
          <div class="flex items-center gap-1 cursor-pointer">
            <a href="login.php" class="hover:text-primary-500 transition duration-300">Account</a>
          </div>
          <div class="flex items-center gap-1 cursor-pointer">
            <a href="cart.php" class="flex items-center gap-1">
              <i class="ri-heart-line text-xl"></i>
              <span>WISHLIST</span>
            </a>
          </div>
          <div class="relative cursor-pointer">
            <a href="cart.php" class="flex items-center gap-1" onclick="window.location.href='cart.php'; return false;">
              <i class="ri-shopping-bag-line text-xl"></i>
              <span class="cart-count absolute -top-2 -right-2 bg-black text-white text-xs rounded-full w-4 h-4 flex items-center justify-center"><?php echo getCartItemCount(); ?></span>
              <span class="ml-1">CART</span>
            </a>
          </div>
        </div>
      </div>
      <hr>
      <nav class="h-10 flex items-center justify-center">
        <ul class="flex gap-8">
          <li><a href="index.html" class="hover:text-primary">HOME</a></li>
          <li><a href="all-items.html" class="hover:text-primary">SHOP</a></li>
          <li><a href="#" class="hover:text-primary">WOMEN</a></li>
          <li><a href="#" class="hover:text-primary">MEN</a></li>
          <li><a href="#" class="hover:text-primary">KIDS</a></li>
          <li><a href="#sale" class="hover:text-primary">SALE</a></li>
          <li><a href="aboutus.html" class="hover:text-primary">ABOUT US</a></li>
        </ul>
      </nav>
    </div>
  </header>

  <main class="container mx-auto px-4 py-8">
    <div class="max-w-6xl mx-auto">

      <?php if ($orderPlaced): ?>
        <!-- Order Confirmation -->
        <div class="text-center py-10">
          <div class="text-4xl text-green-500 mb-4">
            <i class="ri-checkbox-circle-line"></i>
          </div>
          <h1 class="text-3xl font-bold mb-4">Order Placed Successfully!</h1>
          <p class="text-xl mb-2">Thank you for your purchase.</p>
          <p class="text-gray-600 mb-8">Your order number is: <span class="font-semibold"><?php echo $_SESSION['last_order_number']; ?></span></p>
          <p class="text-gray-600">A confirmation email has been sent to your registered email address.</p>
          
          <div class="mt-10 flex justify-center gap-4">
            <a href="index.html" class="bg-black text-white px-6 py-3 rounded hover:bg-gray-800">Continue Shopping</a>
            <a href="account.php" class="border border-black px-6 py-3 rounded hover:bg-gray-100">View My Orders</a>
          </div>
        </div>

      <?php elseif (empty($cart)): ?>
        <!-- Empty Cart -->
        <div class="text-center py-12">
          <i class="ri-shopping-cart-line text-5xl text-gray-300 mb-4"></i>
          <p class="text-xl text-gray-500 mb-6">Your cart is empty</p>
          <a href="index.html" class="inline-block bg-black text-white px-6 py-2 rounded hover:bg-gray-800">
            Continue Shopping
          </a>
        </div>

      <?php else: ?>
        <!-- Checkout Process -->
        <h1 class="text-3xl font-bold mb-8">Checkout</h1>
        
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
          <!-- Order Summary -->
          <div class="lg:col-span-2">
            <div class="bg-white p-6 rounded-lg shadow-sm mb-6">
              <h2 class="text-xl font-bold mb-4">Order Summary</h2>
              
              <div class="space-y-4">
                <?php foreach ($cart as $item): ?>
                <div class="flex items-center gap-4 py-4 border-b border-gray-100">
                  <img src="<?php echo htmlspecialchars($item['image']); ?>" alt="<?php echo htmlspecialchars($item['name']); ?>" class="w-16 h-16 object-cover rounded">
                  <div class="flex-grow">
                    <h3 class="font-semibold"><?php echo htmlspecialchars($item['name']); ?></h3>
                    <p class="text-gray-600 text-sm">Quantity: <?php echo $item['quantity']; ?></p>
                  </div>
                  <p class="font-semibold">₹<?php echo number_format($item['price'] * $item['quantity'], 2); ?></p>
                </div>
                <?php endforeach; ?>
              </div>
              
              <div class="mt-4 pt-4 border-t border-gray-200">
                <div class="flex justify-between mb-2">
                  <span>Subtotal:</span>
                  <span id="subtotal-amount">₹<?php echo number_format($subtotal, 2); ?></span>
                </div>
                <div class="flex justify-between mb-2">
                  <span>Tax (18%):</span>
                  <span id="tax-amount">₹<?php echo number_format($tax, 2); ?></span>
                </div>
                <div class="flex justify-between mb-2">
                  <span>Shipping:</span>
                  <span id="shipping-amount"><?php echo ($shipping > 0) ? '₹' . number_format($shipping, 2) : 'Free'; ?></span>
                </div>
                <div id="cod-fee-row" class="flex justify-between mb-2 hidden">
                  <span>Cash on Delivery Fee:</span>
                  <span id="cod-fee-amount">₹50.00</span>
                </div>
                <div class="flex justify-between text-lg font-bold">
                  <span>Total:</span>
                  <span id="total-amount">₹<?php echo number_format($total, 2); ?></span>
                </div>
              </div>
            </div>
            
            <div class="bg-white p-6 rounded-lg shadow-sm">
              <h2 class="text-xl font-bold mb-4">Shipping Method</h2>
              <div class="space-y-3">
                <div class="flex items-center gap-2">
                  <input type="radio" id="standard" name="shipping" value="standard" checked onchange="updateTotals()">
                  <label for="standard" class="flex justify-between w-full">
                    <span>Standard Shipping (3-5 business days)</span>
                    <span class="font-medium"><?php echo ($shipping > 0) ? '₹' . number_format($shipping, 2) : 'Free'; ?></span>
                  </label>
                </div>
                <div class="flex items-center gap-2">
                  <input type="radio" id="express" name="shipping" value="express" onchange="updateTotals()">
                  <label for="express" class="flex justify-between w-full">
                    <span>Express Shipping (1-2 business days)</span>
                    <span class="font-medium">₹199.00</span>
                  </label>
                </div>
              </div>
            </div>
          </div>
          
          <!-- Payment Form -->
          <div class="lg:col-span-1">
            <form method="post" class="bg-white p-6 rounded-lg shadow-sm">
              <h2 class="text-xl font-bold mb-4">Payment Method</h2>
              
              <!-- Payment Options -->
              <div class="space-y-3 mb-6">
                <!-- Credit Card -->
                <div class="payment-option p-4 rounded-lg cursor-pointer" onclick="selectPayment('credit-card')">
                  <input type="radio" id="credit-card" name="payment_method" value="credit-card" class="hidden payment-radio" onchange="updateTotals()">
                  <div class="flex items-center justify-between">
                    <label for="credit-card" class="font-medium cursor-pointer">Credit/Debit Card</label>
                    <div class="flex">
                      <img src="https://upload.wikimedia.org/wikipedia/commons/thumb/0/04/Visa.svg/1200px-Visa.svg.png" alt="Visa" class="h-6 mx-1">
                      <img src="https://upload.wikimedia.org/wikipedia/commons/thumb/a/a4/Mastercard_2019_logo.svg/1200px-Mastercard_2019_logo.svg.png" alt="Mastercard" class="h-6 mx-1">
                    </div>
                  </div>
                  <div class="payment-details credit-card-details hidden mt-4 space-y-4">
                    <div>
                      <label class="block text-sm font-medium text-gray-700 mb-1">Cardholder Name</label>
                      <input type="text" name="name" class="w-full px-3 py-2 border rounded focus:outline-none focus:ring-2 focus:ring-black">
                    </div>
                    <div>
                      <label class="block text-sm font-medium text-gray-700 mb-1">Card Number</label>
                      <input type="text" name="card_number" pattern="\d*" maxlength="16" placeholder="XXXX XXXX XXXX XXXX" class="w-full px-3 py-2 border rounded focus:outline-none focus:ring-2 focus:ring-black">
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                      <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Expiration Date</label>
                        <input type="text" name="expiry" placeholder="MM/YY" class="w-full px-3 py-2 border rounded focus:outline-none focus:ring-2 focus:ring-black">
                      </div>
                      <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">CVV</label>
                        <input type="text" name="cvv" pattern="\d*" maxlength="3" class="w-full px-3 py-2 border rounded focus:outline-none focus:ring-2 focus:ring-black">
                      </div>
                    </div>
                  </div>
                </div>
                
                <!-- UPI -->
                <div class="payment-option p-4 rounded-lg cursor-pointer" onclick="selectPayment('upi')">
                  <input type="radio" id="upi" name="payment_method" value="upi" class="hidden payment-radio" onchange="updateTotals()">
                  <div class="flex items-center justify-between">
                    <label for="upi" class="font-medium cursor-pointer">UPI Payment</label>
                    <div class="flex">
                      <img src="https://upload.wikimedia.org/wikipedia/commons/thumb/e/e1/UPI-Logo-vector.svg/1200px-UPI-Logo-vector.svg.png" alt="UPI" class="h-6 mx-1">
                    </div>
                  </div>
                  <div class="payment-details upi-details hidden mt-4 space-y-4">
                    <div>
                      <label class="block text-sm font-medium text-gray-700 mb-1">UPI ID</label>
                      <input type="text" name="upi_id" placeholder="example@upi" class="w-full px-3 py-2 border rounded focus:outline-none focus:ring-2 focus:ring-black">
                    </div>
                    
                    <div class="text-center mt-4">
                      <p class="text-sm text-gray-600 mb-2">Choose a UPI app:</p>
                      <div class="flex justify-center space-x-4">
                        <div class="flex flex-col items-center">
                          <img src="https://upload.wikimedia.org/wikipedia/commons/thumb/7/71/PhonePe_Logo.svg/1200px-PhonePe_Logo.svg.png" alt="PhonePe" class="h-8 mb-1">
                          <span class="text-xs">PhonePe</span>
                        </div>
                        <div class="flex flex-col items-center">
                          <img src="https://upload.wikimedia.org/wikipedia/commons/f/f2/Google_Pay_Logo.svg" alt="Google Pay" class="h-8 mb-1">
                          <span class="text-xs">Google Pay</span>
                        </div>
                        <div class="flex flex-col items-center">
                          <img src="https://upload.wikimedia.org/wikipedia/commons/thumb/2/24/Paytm_Logo_%28standalone%29.svg/1200px-Paytm_Logo_%28standalone%29.svg.png" alt="Paytm" class="h-8 mb-1">
                          <span class="text-xs">Paytm</span>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
                
                <!-- Cash on Delivery -->
                <div class="payment-option p-4 rounded-lg cursor-pointer" onclick="selectPayment('cod')">
                  <input type="radio" id="cod" name="payment_method" value="cod" class="hidden payment-radio" onchange="updateTotals()">
                  <div class="flex items-center justify-between">
                    <label for="cod" class="font-medium cursor-pointer">Cash on Delivery</label>
                    <div class="flex">
                      <i class="ri-cash-line text-xl"></i>
                    </div>
                  </div>
                  <div class="payment-details cod-details hidden mt-4">
                    <p class="text-sm text-gray-600">Pay with cash upon delivery. Additional fee of ₹50 will be charged for COD orders.</p>
                    <div class="flex justify-between mt-2 text-sm">
                      <span>COD Fee:</span>
                      <span>₹50.00</span>
                    </div>
                  </div>
                </div>
              </div>
              
              <h2 class="text-xl font-bold mb-4">Shipping Address</h2>
              <div class="space-y-4 mb-6">
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1">Full Name</label>
                  <input type="text" name="shipping_name" required class="w-full px-3 py-2 border rounded focus:outline-none focus:ring-2 focus:ring-black">
                </div>
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1">Address Line 1</label>
                  <input type="text" name="address_1" required class="w-full px-3 py-2 border rounded focus:outline-none focus:ring-2 focus:ring-black">
                </div>
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1">Address Line 2</label>
                  <input type="text" name="address_2" class="w-full px-3 py-2 border rounded focus:outline-none focus:ring-2 focus:ring-black">
                </div>
                <div class="grid grid-cols-2 gap-4">
                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">City</label>
                    <input type="text" name="city" required class="w-full px-3 py-2 border rounded focus:outline-none focus:ring-2 focus:ring-black">
                  </div>
                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Postal Code</label>
                    <input type="text" name="postal_code" required class="w-full px-3 py-2 border rounded focus:outline-none focus:ring-2 focus:ring-black">
                  </div>
                </div>
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1">State</label>
                  <input type="text" name="state" required class="w-full px-3 py-2 border rounded focus:outline-none focus:ring-2 focus:ring-black">
                </div>
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1">Phone Number</label>
                  <input type="tel" name="phone" required class="w-full px-3 py-2 border rounded focus:outline-none focus:ring-2 focus:ring-black">
                </div>
              </div>
              
              <div class="flex items-center gap-2 mb-6">
                <input type="checkbox" id="terms" name="terms" required>
                <label for="terms" class="text-sm text-gray-700">I agree to the <a href="#" class="underline">Terms and Conditions</a> and <a href="#" class="underline">Privacy Policy</a></label>
              </div>
              
              <button type="submit" name="place_order" class="w-full bg-black text-white py-3 rounded hover:bg-gray-800 transition-colors">
                Place Order (<span id="button-total-amount">₹<?php echo number_format($total, 2); ?></span>)
              </button>
              
              <div class="flex justify-center mt-6 payment-logos">
                <img src="https://upload.wikimedia.org/wikipedia/commons/thumb/0/04/Visa.svg/1200px-Visa.svg.png" alt="Visa">
                <img src="https://upload.wikimedia.org/wikipedia/commons/thumb/a/a4/Mastercard_2019_logo.svg/1200px-Mastercard_2019_logo.svg.png" alt="Mastercard">
                <img src="https://upload.wikimedia.org/wikipedia/commons/thumb/7/71/PhonePe_Logo.svg/1200px-PhonePe_Logo.svg.png" alt="PhonePe">
                <img src="https://upload.wikimedia.org/wikipedia/commons/thumb/e/e1/UPI-Logo-vector.svg/1200px-UPI-Logo-vector.svg.png" alt="UPI">
              </div>
            </form>
          </div>
        </div>
      <?php endif; ?>

    </div>
  </main>
  
  <!-- Footer -->
  <footer class="bg-gray-50 py-12 mt-12">
    <div class="container mx-auto px-4">
      <div class="grid grid-cols-1 md:grid-cols-4 gap-8 max-w-screen-xl mx-auto">
        <!-- Contact Info -->
        <div>
          <h3 class="font-bold text-gray-900 mb-4">LIFE STYLE</h3>
          <p>Line Baazar, <br>Allahabad, U.P </p>
          <p class="mt-2">(+91) 6306407861<br>info@lifestyle.com</p>
        </div>

        <!-- Shop Links -->
        <div>
          <h3 class="font-bold text-gray-900 mb-4">SHOP</h3>
          <ul class="space-y-2">
            <li><a href="#" class="hover:underline">Women</a></li>
            <li><a href="#" class="hover:underline">Men</a></li>
            <li><a href="#" class="hover:underline">Kids</a></li>
            <li><a href="#" class="hover:underline">Sale</a></li>
          </ul>
        </div>

        <!-- About Us Links -->
        <div>
          <h3 class="font-bold text-gray-900 mb-4">ABOUT US</h3>
          <ul class="space-y-2">
            <li><a href="aboutus.html" class="hover:underline">About</a></li>
            <li><a href="#" class="hover:underline">Press</a></li>
            <li><a href="#" class="hover:underline">Stores</a></li>
          </ul>
        </div>

        <!-- Customer Service Links -->
        <div>
          <h3 class="font-bold text-gray-900 mb-4">CUSTOMER SERVICE</h3>
          <ul class="space-y-2">
            <li><a href="#" class="hover:underline">Track Order</a></li>
            <li><a href="#" class="hover:underline">Return & Cancellations</a></li>
            <li><a href="help.html" class="hover:underline">FAQ</a></li>
            <li><a href="account.php" class="hover:underline">Manage Account</a></li>
          </ul>
        </div>
      </div>

      <!-- Footer Bottom -->
      <div class="flex flex-col md:flex-row justify-between items-center mt-12 pt-8 border-t border-gray-200 max-w-screen-xl mx-auto">
        <p class="text-sm text-gray-600">© 2025 LifeStyle. All rights reserved.</p>
        <div class="flex space-x-4 mt-4 md:mt-0 text-sm text-gray-600">
          <a href="#" class="hover:underline">Privacy Policy</a>
          <a href="#" class="hover:underline">Cookie Policy</a>
        </div>
      </div>
    </div>
  </footer>
  
  <script>
    // Store initial values
    const initialValues = {
      subtotal: <?php echo $subtotal; ?>,
      tax: <?php echo $tax; ?>,
      shipping: <?php echo $shipping; ?>,
      total: <?php echo $total; ?>
    };
    
    // Format currency
    function formatCurrency(amount) {
      return '₹' + parseFloat(amount).toFixed(2);
    }
    
    // Update totals based on shipping and payment method
    function updateTotals() {
      let shippingCost = 0;
      let codFee = 0;
      
      // Get shipping cost
      if (document.getElementById('standard').checked) {
        shippingCost = initialValues.subtotal > 999 ? 0 : 99;
        document.getElementById('shipping-amount').textContent = shippingCost > 0 ? formatCurrency(shippingCost) : 'Free';
      } else if (document.getElementById('express').checked) {
        shippingCost = 199;
        document.getElementById('shipping-amount').textContent = formatCurrency(shippingCost);
      }
      
      // Check if COD is selected
      if (document.getElementById('cod').checked) {
        codFee = 50;
        document.getElementById('cod-fee-row').classList.remove('hidden');
      } else {
        document.getElementById('cod-fee-row').classList.add('hidden');
      }
      
      // Calculate new total
      const newTotal = initialValues.subtotal + initialValues.tax + shippingCost + codFee;
      
      // Update total display
      document.getElementById('total-amount').textContent = formatCurrency(newTotal);
      document.getElementById('button-total-amount').textContent = formatCurrency(newTotal);
    }
    
    // Payment method selection functionality
    function selectPayment(method) {
      // Hide all payment details
      document.querySelectorAll('.payment-details').forEach(el => {
        el.classList.add('hidden');
      });
      
      // Remove selected class from all options
      document.querySelectorAll('.payment-option').forEach(el => {
        el.classList.remove('selected');
      });
      
      // Show selected payment details
      const selectedDetails = document.querySelector('.' + method + '-details');
      if (selectedDetails) {
        selectedDetails.classList.remove('hidden');
      }
      
      // Mark selected option
      const selectedOption = document.getElementById(method).closest('.payment-option');
      selectedOption.classList.add('selected');
      
      // Check the radio button
      document.getElementById(method).checked = true;
      
      // Update totals
      updateTotals();
    }
    
    // Initialize with credit card selected
    document.addEventListener('DOMContentLoaded', function() {
      selectPayment('credit-card');
    });
  </script>
</body>
</html>
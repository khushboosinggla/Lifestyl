<?php
// Include cart functions
require_once 'cart_functions.php';

// Process form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = isset($_POST['action']) ? $_POST['action'] : '';
    $productId = isset($_POST['productId']) ? intval($_POST['productId']) : 0;
    
    if ($action === 'update' && $productId > 0) {
        $quantity = isset($_POST['quantity']) ? intval($_POST['quantity']) : 1;
        updateCartItemQuantity($productId, $quantity);
    } elseif ($action === 'remove' && $productId > 0) {
        removeFromCart($productId);
    } elseif ($action === 'clear') {
        clearCart();
    }
}

// Calculate order summary
$cart = getCart();
$subtotal = getCartSubtotal();
$tax = $subtotal * 0.18; // 18% tax
$total = $subtotal + $tax;
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Shopping Cart - LIFE STYLE</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/remixicon/4.6.0/remixicon.min.css">
  <link href="https://fonts.googleapis.com/css2?family=Montserrat:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">
  <style>
    * {
      font-family: Montserrat, sans-serif;
    }
    .cart-container {
      max-width: 1200px;
      margin: 0 auto;
      padding: 40px 20px;
    }
    .cart-item {
      background: white;
      border-radius: 12px;
      padding: 20px;
      margin-bottom: 20px;
      box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
      transition: transform 0.3s ease, box-shadow 0.3s ease;
    }
    .cart-item:hover {
      transform: translateY(-2px);
      box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    }
    .quantity-control {
      display: flex;
      align-items: center;
      gap: 10px;
    }
    .quantity-btn {
      background: #f3f4f6;
      border: none;
      width: 30px;
      height: 30px;
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      cursor: pointer;
      transition: background-color 0.3s ease;
    }
    .quantity-btn:hover {
      background: #e5e7eb;
    }
    .remove-btn {
      color: #ef4444;
      transition: color 0.3s ease;
    }
    .remove-btn:hover {
      color: #dc2626;
    }
  </style>
</head>
<body class="bg-gray-50 min-h-screen">
  <header class="border-b bg-white">
    <div class="container mx-auto px-4">
      <div class="h-16 flex items-center justify-between">
        <h1 class="text-2xl font-bold"><a href="index.html">LIFE STYLE</a></h1>
        <div class="flex items-center gap-6">
          <a href="index.html" class="text-gray-600 hover:text-black">
            <i class="ri-arrow-left-line text-xl"></i>
            Continue Shopping
          </a>
        </div>
      </div>
    </div>
  </header>

  <main class="cart-container">
    <div class="flex flex-col lg:flex-row gap-8">
      <!-- Cart Items Section -->
      <div class="flex-grow">
        <h2 class="text-3xl font-bold mb-8">Shopping Cart (<?php echo getCartItemCount(); ?> items)</h2>
        <div id="cartItems">
          <?php if (empty($cart)): ?>
            <div class="text-center py-12">
              <i class="ri-shopping-cart-line text-5xl text-gray-300 mb-4"></i>
              <p class="text-xl text-gray-500">Your cart is empty</p>
              <a href="index.html" class="mt-6 inline-block bg-black text-white px-6 py-2 rounded hover:bg-gray-800">
                Continue Shopping
              </a>
            </div>
          <?php else: ?>
            <?php foreach ($cart as $item): ?>
              <div class="cart-item flex items-center gap-6">
                <img src="<?php echo htmlspecialchars($item['image']); ?>" alt="<?php echo htmlspecialchars($item['name']); ?>" class="w-24 h-24 object-cover">
                <div class="flex-grow">
                  <h3 class="text-lg font-semibold"><?php echo htmlspecialchars($item['name']); ?></h3>
                  <p class="text-gray-600">₹<?php echo number_format($item['price'], 2); ?></p>
                  <div class="flex items-center gap-6 mt-2">
                    <form method="post" class="flex items-center">
                      <input type="hidden" name="action" value="update">
                      <input type="hidden" name="productId" value="<?php echo $item['id']; ?>">
                      <div class="quantity-control">
                        <button type="submit" name="quantity" value="<?php echo max(1, $item['quantity'] - 1); ?>" class="quantity-btn">-</button>
                        <span><?php echo $item['quantity']; ?></span>
                        <button type="submit" name="quantity" value="<?php echo $item['quantity'] + 1; ?>" class="quantity-btn">+</button>
                      </div>
                    </form>
                    <form method="post">
                      <input type="hidden" name="action" value="remove">
                      <input type="hidden" name="productId" value="<?php echo $item['id']; ?>">
                      <button type="submit" class="remove-btn flex items-center gap-1">
                        <i class="ri-delete-bin-line"></i>
                        Remove
                      </button>
                    </form>
                  </div>
                </div>
                <div class="text-lg font-semibold">
                  ₹<?php echo number_format($item['price'] * $item['quantity'], 2); ?>
                </div>
              </div>
            <?php endforeach; ?>

            <div class="mt-8 flex justify-end">
              <form method="post">
                <input type="hidden" name="action" value="clear">
                <button type="submit" class="text-red-600 hover:text-red-800">Clear Cart</button>
              </form>
            </div>
          <?php endif; ?>
        </div>
      </div>

      <!-- Order Summary Section -->
      <div class="lg:w-1/3">
        <div class="bg-white p-6 shadow-sm sticky top-4">
          <h3 class="text-xl font-bold mb-4">Order Summary</h3>
          <div class="space-y-3 mb-6">
            <div class="flex justify-between">
              <span class="text-gray-600">Subtotal</span>
              <span id="subtotal" class="font-semibold">₹<?php echo number_format($subtotal, 2); ?></span>
            </div>
            <div class="flex justify-between">
              <span class="text-gray-600">Shipping</span>
              <span class="font-semibold">Free</span>
            </div>
            <div class="flex justify-between">
              <span class="text-gray-600">Tax</span>
              <span id="tax" class="font-semibold">₹<?php echo number_format($tax, 2); ?></span>
            </div>
            <hr>
            <div class="flex justify-between text-lg font-bold">
              <span>Total</span>
              <span id="total">₹<?php echo number_format($total, 2); ?></span>
            </div>
          </div>
          <?php if (!empty($cart)): ?>
            <form action="checkout.php" method="post">
              <button type="submit" class="w-full bg-black text-white py-3 hover:bg-gray-800 transition-colors">
                Proceed to Checkout
              </button>
            </form>
          <?php else: ?>
            <button disabled class="w-full bg-gray-300 text-gray-500 py-3 cursor-not-allowed">
              Proceed to Checkout
            </button>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </main>

</body>
</html>
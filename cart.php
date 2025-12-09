<?php
session_start();
require_once 'db_connect.php';
require_once 'cart_functions.php';
include 'header.php';

// Get cart items from database
$userId = getCurrentUserId();
$cartItems = [];
$cartTotal = 0;

try {
    $stmt = $conn->prepare("SELECT * FROM cart WHERE user_id = ?");
    $stmt->bind_param("s", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    while ($row = $result->fetch_assoc()) {
        $cartItems[] = [
            'id' => $row['product_id'],
            'name' => $row['product_name'],
            'price' => (float)$row['price'],
            'image' => $row['image'],
            'quantity' => (int)$row['quantity']
        ];
        $cartTotal += $row['price'] * $row['quantity'];
    }
    
    // Sync session cart with database cart
    $_SESSION['cart'] = $cartItems;
} catch (Exception $e) {
    error_log("Error retrieving cart: " . $e->getMessage());
    // Fallback to session cart if database fails
    $cartItems = isset($_SESSION['cart']) ? $_SESSION['cart'] : [];
    $cartTotal = 0;
    foreach ($cartItems as $item) {
        $cartTotal += $item['price'] * $item['quantity'];
    }
}

// Calculate tax and shipping
$tax = $cartTotal * 0.18; // 18% tax
$shipping = ($cartTotal > 999) ? 0 : 99; // Free shipping over ₹999
$orderTotal = $cartTotal + $tax + $shipping;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Shopping Cart - LifeStyl</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/remixicon/4.6.0/remixicon.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="main.css">
    <style>
        * {
            font-family: Montserrat, sans-serif;
        }
        
        .cart-item {
            transition: all 0.3s ease;
        }
        
        .cart-item:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
        }
        
        .quantity-btn {
            transition: all 0.3s ease;
        }
        
        .quantity-btn:hover {
            transform: scale(1.1);
        }
        
        .checkout-btn {
            transition: all 0.3s ease;
        }
        
        .checkout-btn:hover {
            transform: translateY(-2px);
        }
    </style>
</head>
<body class="bg-gray-50 min-h-screen">
    <main class="container mx-auto px-4 py-8">
        <div class="max-w-6xl mx-auto">
            <h1 class="text-3xl font-bold mb-8">Your Shopping Cart</h1>
            
            <?php if (!empty($cartItems)) : ?>
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                    <!-- Cart Items -->
                    <div class="lg:col-span-2">
                        <div class="bg-white p-6 rounded-lg shadow-sm mb-6">
                            <div class="flex justify-between items-center mb-4">
                                <h2 class="text-xl font-bold">Items in Your Cart</h2>
                                <span class="bg-black text-white px-3 py-1 rounded-full text-sm"><?= count($cartItems) ?> item<?= count($cartItems) > 1 ? 's' : '' ?></span>
                            </div>
                            
                            <div class="space-y-4">
                                <?php foreach ($cartItems as $item) : ?>
                                    <?php
                                    // Fix for broken image paths
                                    $imagePath = $item['image'];
                                    
                                    // First fix URLs that might have come from different pages
                                    if (strpos($imagePath, 'http://localhost/lifestyl/') !== false) {
                                        // Strip the domain part to get the relative path
                                        $imagePath = str_replace('http://localhost/lifestyl/', '', $imagePath);
                                    }

                                    // Special handling for "New folder" paths - use them directly
                                    if (strpos($imagePath, 'New folder/') !== false) {
                                        // Keep the path as-is, since these files exist in the "New folder" directory
                                        $fullPath = __DIR__ . '/' . $imagePath;
                                        
                                        if (file_exists($fullPath)) {
                                            // The file exists, keep the path as-is
                                            // No further processing needed for "New folder/" images
                                        } else {
                                            // If the file doesn't exist in "New folder/" (maybe there was a typo or case sensitivity issue)
                                            $filename = basename($imagePath);
                                            
                                            // Check with case-insensitive search in "New folder" directory
                                            $newFolder = __DIR__ . '/New folder/';
                                            if (is_dir($newFolder)) {
                                                $files = scandir($newFolder);
                                                foreach ($files as $file) {
                                                    if (strtolower($file) === strtolower($filename)) {
                                                        $imagePath = 'New folder/' . $file;
                                                        break;
                                                    }
                                                }
                                            }
                                        }
                                    } 
                                    // Fix for other image paths (keeping existing logic)
                                    else {
                                        // Check if the full path exists
                                        $fullPath = __DIR__ . '/' . $imagePath;
                                        
                                        if (!file_exists($fullPath)) {
                                            // Try in images directory
                                            if (file_exists(__DIR__ . '/images/' . basename($imagePath))) {
                                                $imagePath = 'images/' . basename($imagePath);
                                            }
                                            // Try in product images directory if you have one
                                            else if (file_exists(__DIR__ . '/products/' . basename($imagePath))) {
                                                $imagePath = 'products/' . basename($imagePath);
                                            }
                                            
                                            // Handle specific product types by their paths
                                            // Fix for kurta images with incorrect paths
                                            if (strpos($imagePath, 'li/kurta') !== false) {
                                                preg_match('/kurta(\d+)\.jpg/', $imagePath, $matches);
                                                if (isset($matches[1])) {
                                                    $imageNum = $matches[1];
                                                    $imagePath = "longkurta/k{$imageNum}.webp";
                                                }
                                            }
                                            // Fix for blazer images
                                            else if (strpos($imagePath, 'images/b') !== false || strpos($imagePath, 'blazer/b') !== false) {
                                                preg_match('/b(\d+)\.webp/', $imagePath, $matches);
                                                if (isset($matches[1])) {
                                                    $imageNum = $matches[1];
                                                    $imagePath = "blazer/b{$imageNum}.webp";
                                                }
                                            }
                                            // Fix for tops from extra.html (np*.webp or n*.webp pattern)
                                            else if (strpos($imagePath, 'images/np') !== false || strpos($imagePath, 'images/n') !== false) {
                                                // Keep the path as-is since these should be valid paths under images/
                                                // No further processing needed
                                            }
                                            // Fix for dress images (dr*.webp pattern)
                                            else if (strpos($imagePath, 'dres/dr') !== false) {
                                                // These paths should be correct as-is
                                                // No further processing needed
                                            }
                                            // Fix for kurta images (k*.webp pattern)
                                            else if (strpos($imagePath, 'longkurta/k') !== false) {
                                                // These paths should be correct as-is, but if there's still an issue
                                                // we can extract the kurta number
                                                preg_match('/k(\d+)\.webp/', $imagePath, $matches);
                                                if (isset($matches[1])) {
                                                    $imageNum = $matches[1];
                                                    $imagePath = "longkurta/k{$imageNum}.webp";
                                                }
                                            }
                                            // Fix for jeans images (j*.webp pattern)
                                            else if (strpos($imagePath, 'longkurta/j') !== false) {
                                                // Jeans images are also in the longkurta folder with j prefix
                                                preg_match('/j(\d+)\.webp/', $imagePath, $matches);
                                                if (isset($matches[1])) {
                                                    $imageNum = $matches[1];
                                                    $imagePath = "longkurta/j{$imageNum}.webp";
                                                }
                                            }
                                            // Fix for beauty product images (b*.avif pattern)
                                            else if (strpos($imagePath, 'beauty/b') !== false) {
                                                preg_match('/b(\d+)\.avif/', $imagePath, $matches);
                                                if (isset($matches[1])) {
                                                    $imageNum = $matches[1];
                                                    $imagePath = "beauty/b{$imageNum}.avif";
                                                }
                                            }
                                            
                                            // Check again if the corrected path exists
                                            $fullPath = __DIR__ . '/' . $imagePath;
                                            
                                            // If the image still doesn't exist, use a generic fallback based on product type
                                            if (!file_exists($fullPath)) {
                                                // Try to determine product type from name or path
                                                if (strpos(strtolower($item['name']), 'blazer') !== false || strpos($imagePath, 'blazer') !== false) {
                                                    $imagePath = "blazer/b1.webp";
                                                } 
                                                else if (strpos(strtolower($item['name']), 'kurta') !== false || strpos($imagePath, 'kurta') !== false) {
                                                    $imagePath = "longkurta/k1.webp";
                                                }
                                                else if (strpos(strtolower($item['name']), 'jean') !== false || strpos($imagePath, 'jean') !== false) {
                                                    $imagePath = "jeans/j1.webp";
                                                }
                                                else if (strpos(strtolower($item['name']), 'top') !== false || strpos($imagePath, 'np') !== false || strpos($imagePath, '/n') !== false) {
                                                    // This is likely a top from extra.html
                                                    $imagePath = "images/np5.webp"; // Use a known good image as fallback
                                                }
                                                else {
                                                    // Last resort generic fallback - use one of the existing images we know works
                                                    $imagePath = "images/np5.webp";
                                                }
                                                
                                                // Final check in case even our fallback doesn't exist
                                                $fullPath = __DIR__ . '/' . $imagePath;
                                                if (!file_exists($fullPath)) {
                                                    // If all else fails, try some other common image in the workspace
                                                    $imagePath = "blazer/b1.webp";
                                                }
                                            }
                                        }
                                    }
                                    ?>
                                    <div class="cart-item flex items-center gap-4 py-4 border-b border-gray-100" data-product-id="<?= htmlspecialchars($item['id']) ?>">
                                        <img src="<?= htmlspecialchars($imagePath) ?>" alt="<?= htmlspecialchars($item['name']) ?>" class="w-20 h-20 object-cover rounded">
                                        
                                        <div class="flex-grow">
                                            <h3 class="font-semibold"><?= htmlspecialchars($item['name']) ?></h3>
                                            <div class="text-gray-600 mt-1">₹<?= number_format($item['price'], 2) ?></div>
                                            
                                            <div class="flex items-center mt-3">
                                                <button class="quantity-btn flex items-center justify-center w-8 h-8 bg-gray-100 rounded-full" onclick="updateCartItemQuantity(<?= $item['id'] ?>, -1)">
                                                    <i class="ri-subtract-line"></i>
                                                </button>
                                                <input type="number" class="quantity-input mx-2 w-12 text-center border border-gray-200 rounded py-1" value="<?= $item['quantity'] ?>" min="1" onchange="updateCartQuantity(<?= $item['id'] ?>, this.value)">
                                                <button class="quantity-btn flex items-center justify-center w-8 h-8 bg-gray-100 rounded-full" onclick="updateCartItemQuantity(<?= $item['id'] ?>, 1)">
                                                    <i class="ri-add-line"></i>
                                                </button>
                                            </div>
                                        </div>
                                        
                                        <div class="flex flex-col items-end">
                                            <div class="font-semibold">₹<?= number_format($item['price'] * $item['quantity'], 2) ?></div>
                                            <button class="text-red-500 mt-2 flex items-center text-sm" onclick="removeFromCart(<?= $item['id'] ?>)">
                                                <i class="ri-delete-bin-line mr-1"></i> Remove
                                            </button>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            
                            <div class="mt-6 flex justify-between items-center">
                                <a href="index.html" class="inline-flex items-center text-black">
                                    <i class="ri-arrow-left-line mr-1"></i> Continue Shopping
                                </a>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Order Summary -->
                    <div class="lg:col-span-1">
                        <div class="bg-white p-6 rounded-lg shadow-sm sticky top-4">
                            <h2 class="text-xl font-bold mb-4">Order Summary</h2>
                            
                            <div class="space-y-3 py-4">
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Subtotal:</span>
                                    <span>₹<?= number_format($cartTotal, 2) ?></span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Tax (18%):</span>
                                    <span>₹<?= number_format($tax, 2) ?></span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Shipping:</span>
                                    <span><?= ($shipping > 0) ? '₹' . number_format($shipping, 2) : 'Free' ?></span>
                                </div>
                                
                                <div class="border-t border-gray-200 pt-3 mt-3">
                                    <div class="flex justify-between font-bold text-lg">
                                        <span>Total:</span>
                                        <span>₹<?= number_format($orderTotal, 2) ?></span>
                                    </div>
                                    <p class="text-xs text-gray-500 mt-1">
                                        <?= ($shipping === 0) ? 'Free shipping applied!' : 'Free shipping on orders above ₹999' ?>
                                    </p>
                                </div>
                            </div>
                            
                            <a href="<?php echo isset($_SESSION['user_id']) ? 'checkout.php' : 'login.php?redirect=checkout'; ?>" class="checkout-btn block w-full bg-black text-white text-center py-3 rounded-lg mt-6 font-bold hover:bg-gray-800 transition-colors">
                                Proceed to Checkout
                            </a>
                            
                            <div class="mt-6">
                                <h3 class="text-sm font-semibold mb-2">Secure Checkout</h3>
                                <div class="flex items-center justify-between">
                                    <div class="flex space-x-2">
                                        <i class="ri-shield-check-line text-green-500"></i>
                                        <span class="text-xs text-gray-600">100% Secure Payment</span>
                                    </div>
                                    <div class="flex items-center space-x-2">
                                        <i class="ri-truck-line text-blue-500"></i>
                                        <span class="text-xs text-gray-600">Free Returns</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
            <?php else : ?>
                <!-- Empty Cart -->
                <div class="text-center py-12 bg-white rounded-lg shadow-sm">
                    <i class="ri-shopping-cart-line text-5xl text-gray-300 mb-4"></i>
                    <h2 class="text-2xl font-bold mb-2">Your cart is empty</h2>
                    <p class="text-gray-600 mb-8">Looks like you haven't added any products to your cart yet.</p>
                    <a href="index.html" class="inline-block bg-black text-white px-6 py-3 rounded-lg hover:bg-gray-800 transition-colors">
                        Continue Shopping
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </main>

    <?php include 'footer.php'; ?>

    <script src="cart.js"></script>
    <script src="cart_sync.js"></script>
    <script src="counter-update.js"></script>
    <script>
        // Helper function to update item quantity by increment/decrement
        function updateCartItemQuantity(productId, change) {
            const input = document.querySelector(`.cart-item[data-product-id="${productId}"] .quantity-input`);
            const newValue = parseInt(input.value) + change;
            if (newValue >= 1) {
                input.value = newValue;
                updateCartQuantity(productId, newValue);
            }
        }
        
        // Initialize cart page functionality
        document.addEventListener('DOMContentLoaded', function() {
            // Add input event listeners for quantity changes
            document.querySelectorAll('.quantity-input').forEach(input => {
                input.addEventListener('input', function() {
                    const productId = this.closest('.cart-item').dataset.productId;
                    const quantity = parseInt(this.value) || 1;
                    if (quantity >= 1) {
                        updateCartQuantity(productId, quantity);
                    }
                });
            });
            
            // Make sure the prices update visually even before the server responds
            document.querySelectorAll('.quantity-btn').forEach(btn => {
                btn.addEventListener('click', function() {
                    setTimeout(function() {
                        // Force UI update immediately for better user experience
                        updateCartUI();
                    }, 50);
                });
            });
        });
    </script>
</body>
</html>
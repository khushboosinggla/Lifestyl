<?php
// Start or resume session
session_start();

// Include database connection
require_once('db_connect.php');

// Initialize response array
$response = [
    'success' => false,
    'message' => 'No action specified'
];

// Check if action is specified
if (isset($_POST['action'])) {
    $action = $_POST['action'];
    
    // Sync cart data from localStorage to session
    if ($action === 'sync_cart' && isset($_POST['cart_data'])) {
        $cartData = json_decode($_POST['cart_data'], true);
        
        // Initialize cart in session if it doesn't exist
        if (!isset($_SESSION['cart'])) {
            $_SESSION['cart'] = [];
        }
        
        // Process cart items
        if (is_array($cartData)) {
            // Replace session cart with localStorage cart
            $_SESSION['cart'] = [];
            
            foreach ($cartData as $item) {
                if (!empty($item['id'])) {
                    $productId = $item['id'];
                    $name = isset($item['name']) ? $item['name'] : 'Product';
                    $price = isset($item['price']) ? floatval($item['price']) : 0;
                    $image = isset($item['image']) ? $item['image'] : '';
                    $quantity = isset($item['quantity']) ? intval($item['quantity']) : 1;
                    
                    $_SESSION['cart'][$productId] = [
                        'id' => $productId,
                        'name' => $name,
                        'price' => $price,
                        'image' => $image,
                        'quantity' => $quantity
                    ];
                    
                    // Save to database if user is logged in
                    if (isset($_SESSION['user_id'])) {
                        $userId = $_SESSION['user_id'];
                        
                        // Check if item already exists in cart
                        $stmt = $conn->prepare("SELECT * FROM cart WHERE user_id = ? AND product_id = ?");
                        $stmt->bind_param("is", $userId, $productId);
                        $stmt->execute();
                        $result = $stmt->get_result();
                        
                        if ($result->num_rows > 0) {
                            // Update quantity
                            $stmt = $conn->prepare("UPDATE cart SET quantity = ? WHERE user_id = ? AND product_id = ?");
                            $stmt->bind_param("iis", $quantity, $userId, $productId);
                        } else {
                            // Insert new item
                            $stmt = $conn->prepare("INSERT INTO cart (user_id, product_id, name, price, image, quantity) VALUES (?, ?, ?, ?, ?, ?)");
                            $stmt->bind_param("issdsi", $userId, $productId, $name, $price, $image, $quantity);
                        }
                        
                        $stmt->execute();
                    }
                }
            }
            
            $response['success'] = true;
            $response['message'] = 'Cart synced successfully';
            $response['cartCount'] = count($_SESSION['cart']);
        }
    }
    
    // Sync wishlist data
    else if ($action === 'sync_wishlist' && isset($_POST['wishlist_data'])) {
        $wishlistData = json_decode($_POST['wishlist_data'], true);
        
        // Initialize wishlist in session if it doesn't exist
        if (!isset($_SESSION['wishlist'])) {
            $_SESSION['wishlist'] = [];
        }
        
        // Process wishlist items
        if (is_array($wishlistData)) {
            $_SESSION['wishlist'] = $wishlistData;
            
            $response['success'] = true;
            $response['message'] = 'Wishlist synced successfully';
            $response['wishlistCount'] = count($_SESSION['wishlist']);
        }
    }
}

// Return JSON response
header('Content-Type: application/json');
echo json_encode($response);
?>
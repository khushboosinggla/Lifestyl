<?php
// Start the session to access wishlist data
session_start();

// Set the content type to JSON
header('Content-Type: application/json');

// Initialize wishlist if it doesn't exist
if (!isset($_SESSION['wishlist'])) {
    $_SESSION['wishlist'] = [];
}

// Get the request data
$response = ['success' => false, 'message' => 'Invalid request'];

// Process wishlist actions
if (isset($_POST['action'])) {
    $action = $_POST['action'];
    
    // Add item to wishlist
    if ($action === 'add' && isset($_POST['productId'], $_POST['name'], $_POST['price'], $_POST['image'])) {
        $product_id = $_POST['productId'];
        
        // Check if product already exists in wishlist
        if (!isset($_SESSION['wishlist'][$product_id])) {
            $_SESSION['wishlist'][$product_id] = [
                'name' => $_POST['name'],
                'price' => $_POST['price'],
                'image' => $_POST['image']
            ];
            $response = [
                'success' => true, 
                'message' => 'Item added to wishlist',
                'count' => count($_SESSION['wishlist'])
            ];
        } else {
            $response = [
                'success' => true, 
                'message' => 'Item already in wishlist',
                'count' => count($_SESSION['wishlist'])
            ];
        }
    }
    // Remove item from wishlist
    else if ($action === 'remove' && isset($_POST['productId'])) {
        $product_id = $_POST['productId'];
        
        if (isset($_SESSION['wishlist'][$product_id])) {
            unset($_SESSION['wishlist'][$product_id]);
            $response = [
                'success' => true, 
                'message' => 'Item removed from wishlist',
                'count' => count($_SESSION['wishlist'])
            ];
        } else {
            $response = [
                'success' => false, 
                'message' => 'Item not found in wishlist',
                'count' => count($_SESSION['wishlist'])
            ];
        }
    }
    // Move item to cart
    else if ($action === 'move_to_cart' && isset($_POST['productId'])) {
        $product_id = $_POST['productId'];
        
        // Check if product exists in wishlist
        if (isset($_SESSION['wishlist'][$product_id])) {
            $item = $_SESSION['wishlist'][$product_id];
            
            // Initialize cart if it doesn't exist
            if (!isset($_SESSION['cart'])) {
                $_SESSION['cart'] = [];
            }
            
            // Add to cart
            if (isset($_SESSION['cart'][$product_id])) {
                $_SESSION['cart'][$product_id]['quantity']++;
            } else {
                $_SESSION['cart'][$product_id] = [
                    'name' => $item['name'],
                    'price' => $item['price'],
                    'image' => $item['image'],
                    'quantity' => 1
                ];
            }
            
            // Remove from wishlist
            unset($_SESSION['wishlist'][$product_id]);
            
            $response = [
                'success' => true, 
                'message' => 'Item moved to cart',
                'wishlistCount' => count($_SESSION['wishlist']),
                'cartCount' => array_sum(array_map(function($item) { 
                    return $item['quantity'];
                }, $_SESSION['cart']))
            ];
        } else {
            $response = [
                'success' => false, 
                'message' => 'Item not found in wishlist'
            ];
        }
    }
    // Get wishlist count
    else if ($action === 'get_count') {
        $response = [
            'success' => true,
            'count' => isset($_SESSION['wishlist']) ? count($_SESSION['wishlist']) : 0
        ];
    }
}

// Return JSON response
echo json_encode($response);
?>
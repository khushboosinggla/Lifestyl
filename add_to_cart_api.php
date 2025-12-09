<?php
// Start or resume session
session_start();

// Include database connection
require_once 'db_connect.php';

// Initialize cart if not exists
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Generate a unique user ID if not exist (for non-logged in users)
if (!isset($_SESSION['temp_user_id'])) {
    $_SESSION['temp_user_id'] = session_id();
}

// Get user ID (either logged in user ID or session ID)
function getCurrentUserId() {
    // If user is logged in, use their user ID
    if (isset($_SESSION['user_id'])) {
        return $_SESSION['user_id'];
    }
    
    // Otherwise use temp session ID
    return $_SESSION['temp_user_id'];
}

// Response array
$response = [
    'success' => false,
    'message' => '',
    'cartCount' => 0,
    'cartItems' => []
];

// For POST requests - adding or updating cart items
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Parse JSON input for POST requests
    $input = json_decode(file_get_contents('php://input'), true);
    
    // If no JSON data was sent, check for regular POST data
    if (!$input) {
        $input = $_POST;
    }
    
    // Check if this is an add/update/remove request
    if (isset($input['action'])) {
        $action = $input['action'];
        $userId = getCurrentUserId();
        
        // Add item to cart
        if ($action === 'add' && isset($input['product_id'])) {
            $productId = $input['product_id'];
            $name = isset($input['product_name']) ? $input['product_name'] : 'Product';
            $price = isset($input['price']) ? floatval($input['price']) : 0;
            $image = isset($input['image']) ? $input['image'] : '';
            $quantity = isset($input['quantity']) ? intval($input['quantity']) : 1;
            
            // Check if product already in cart (database)
            $stmt = $conn->prepare("SELECT * FROM cart WHERE user_id = ? AND product_id = ?");
            $stmt->bind_param("ss", $userId, $productId);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows > 0) {
                // Update quantity in database
                $cartItem = $result->fetch_assoc();
                $newQuantity = $cartItem['quantity'] + $quantity;
                
                $updateStmt = $conn->prepare("UPDATE cart SET quantity = ?, updated_at = NOW() WHERE user_id = ? AND product_id = ?");
                $updateStmt->bind_param("iss", $newQuantity, $userId, $productId);
                $updateStmt->execute();
            } else {
                // Insert new item into database
                $insertStmt = $conn->prepare("INSERT INTO cart (user_id, product_id, product_name, price, quantity, image) VALUES (?, ?, ?, ?, ?, ?)");
                $insertStmt->bind_param("sisdis", $userId, $productId, $name, $price, $quantity, $image);
                $insertStmt->execute();
            }
            
            // Update session cart
            $found = false;
            foreach ($_SESSION['cart'] as $key => $item) {
                if (isset($item['id']) && $item['id'] == $productId) {
                    $_SESSION['cart'][$key]['quantity'] += $quantity;
                    $found = true;
                    break;
                }
            }
            
            if (!$found) {
                $_SESSION['cart'][] = [
                    'id' => $productId,
                    'name' => $name,
                    'price' => $price,
                    'image' => $image,
                    'quantity' => $quantity
                ];
            }
            
            $response['success'] = true;
            $response['message'] = 'Product added to cart';
        }
        
        // Update quantity
        else if ($action === 'update' && isset($input['product_id']) && isset($input['quantity'])) {
            $productId = $input['product_id'];
            $quantity = intval($input['quantity']);
            
            if ($quantity <= 0) {
                // Remove item from database if quantity is 0
                $deleteStmt = $conn->prepare("DELETE FROM cart WHERE user_id = ? AND product_id = ?");
                $deleteStmt->bind_param("ss", $userId, $productId);
                $deleteStmt->execute();
                
                // Remove from session
                foreach ($_SESSION['cart'] as $key => $item) {
                    if (isset($item['id']) && $item['id'] == $productId) {
                        unset($_SESSION['cart'][$key]);
                        $_SESSION['cart'] = array_values($_SESSION['cart']); // Re-index array
                        break;
                    }
                }
            } else {
                // Update quantity in database
                $updateStmt = $conn->prepare("UPDATE cart SET quantity = ?, updated_at = NOW() WHERE user_id = ? AND product_id = ?");
                $updateStmt->bind_param("iss", $quantity, $userId, $productId);
                $updateStmt->execute();
                
                // Update in session
                foreach ($_SESSION['cart'] as $key => $item) {
                    if (isset($item['id']) && $item['id'] == $productId) {
                        $_SESSION['cart'][$key]['quantity'] = $quantity;
                        break;
                    }
                }
            }
            
            $response['success'] = true;
            $response['message'] = 'Cart updated';
        }
        
        // Remove item
        else if ($action === 'remove' && isset($input['product_id'])) {
            $productId = $input['product_id'];
            
            // Remove from database
            $deleteStmt = $conn->prepare("DELETE FROM cart WHERE user_id = ? AND product_id = ?");
            $deleteStmt->bind_param("ss", $userId, $productId);
            $deleteStmt->execute();
            
            // Remove from session
            foreach ($_SESSION['cart'] as $key => $item) {
                if (isset($item['id']) && $item['id'] == $productId) {
                    unset($_SESSION['cart'][$key]);
                    $_SESSION['cart'] = array_values($_SESSION['cart']); // Re-index array
                    break;
                }
            }
            
            $response['success'] = true;
            $response['message'] = 'Item removed from cart';
        }
        
        // Retrieve updated cart after any change
        $stmt = $conn->prepare("SELECT * FROM cart WHERE user_id = ?");
        $stmt->bind_param("s", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        $cartItems = [];
        $cartTotal = 0;
        
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
        
        // Update session cart to match database
        $_SESSION['cart'] = $cartItems;
        
        $response['cartItems'] = $cartItems;
        $response['cartCount'] = count($cartItems) > 0 ? array_sum(array_column($cartItems, 'quantity')) : 0;
        $response['cartSubtotal'] = $cartTotal;
    }
}

// For GET requests - getting cart count and contents
else {
    $userId = getCurrentUserId();
    
    // Get action from query string if it exists
    $action = isset($_GET['action']) ? $_GET['action'] : '';
    
    // Get cart count only
    if ($action === 'get_count') {
        // Get cart items from database
        $stmt = $conn->prepare("SELECT SUM(quantity) as total_items FROM cart WHERE user_id = ?");
        $stmt->bind_param("s", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        
        $response = [
            'success' => true,
            'cart_count' => $row['total_items'] ? (int)$row['total_items'] : 0
        ];
    } 
    // Get full cart contents
    else {
        // Get cart items from database
        $stmt = $conn->prepare("SELECT * FROM cart WHERE user_id = ?");
        $stmt->bind_param("s", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        $cartItems = [];
        $cartTotal = 0;
        
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
        
        // Update session to match database
        $_SESSION['cart'] = $cartItems;
        
        $response = [
            'success' => true,
            'cartCount' => count($cartItems) > 0 ? array_sum(array_column($cartItems, 'quantity')) : 0,
            'cartItems' => $cartItems,
            'cartSubtotal' => $cartTotal
        ];
    }
}

// Return JSON response
header('Content-Type: application/json');
echo json_encode($response);
?>
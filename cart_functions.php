<?php
// Error handling
error_reporting(0); // Disable error reporting
ini_set('display_errors', 0); // Don't display errors

session_start();

// Include database connection
function getDbConnection() {
    // Database connection parameters
    $host = 'localhost';
    $username = 'root';
    $password = '';
    $dbname = 'lifestyl';
    
    try {
        $conn = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $conn;
    } catch (PDOException $e) {
        // Log error but don't show to user
        error_log("Database connection error: " . $e->getMessage());
        return null;
    }
}

// Cart functions

/**
 * Initialize the cart if it doesn't exist
 */
function initializeCart() {
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }
    
    // Generate a unique user ID if not exist (for non-logged in users)
    if (!isset($_SESSION['temp_user_id'])) {
        $_SESSION['temp_user_id'] = session_id();
    }
}

/**
 * Get user ID (either logged in user ID or session ID)
 */
function getCurrentUserId() {
    // If user is logged in, use their user ID
    if (isset($_SESSION['user_id'])) {
        return $_SESSION['user_id'];
    }
    
    // Otherwise use temp session ID
    initializeCart();
    return $_SESSION['temp_user_id'];
}

/**
 * Add an item to the cart (both session and database)
 * 
 * @param int $productId - Product ID
 * @param string $name - Product name
 * @param float $price - Product price
 * @param string $image - Product image path
 * @param int $quantity - Quantity to add
 * @return array - Status and message
 */
function addToCart($productId, $name, $price, $image, $quantity = 1) {
    initializeCart();
    $userId = getCurrentUserId();
    
    // Add to session cart
    // Check if product already exists in cart
    $found = false;
    foreach ($_SESSION['cart'] as &$item) {
        if ($item['id'] == $productId) {
            $item['quantity'] += $quantity;
            $found = true;
            break;
        }
    }
    
    // If product not found, add it
    if (!$found) {
        $_SESSION['cart'][] = [
            'id' => $productId,
            'name' => $name,
            'price' => $price,
            'image' => $image,
            'quantity' => $quantity
        ];
    }
    
    // Add to database
    try {
        $conn = getDbConnection();
        if ($conn) {
            // Check if product already exists in cart table
            $stmt = $conn->prepare("SELECT * FROM cart WHERE user_id = :user_id AND product_id = :product_id");
            $stmt->bindParam(':user_id', $userId);
            $stmt->bindParam(':product_id', $productId);
            $stmt->execute();
            
            if ($stmt->rowCount() > 0) {
                // Update existing cart item
                $cartItem = $stmt->fetch(PDO::FETCH_ASSOC);
                $newQuantity = $cartItem['quantity'] + $quantity;
                
                $updateStmt = $conn->prepare("UPDATE cart SET quantity = :quantity, updated_at = NOW() WHERE user_id = :user_id AND product_id = :product_id");
                $updateStmt->bindParam(':quantity', $newQuantity);
                $updateStmt->bindParam(':user_id', $userId);
                $updateStmt->bindParam(':product_id', $productId);
                $updateStmt->execute();
            } else {
                // Insert new cart item
                $insertStmt = $conn->prepare("INSERT INTO cart (user_id, product_id, product_name, price, quantity, image) VALUES (:user_id, :product_id, :product_name, :price, :quantity, :image)");
                $insertStmt->bindParam(':user_id', $userId);
                $insertStmt->bindParam(':product_id', $productId);
                $insertStmt->bindParam(':product_name', $name);
                $insertStmt->bindParam(':price', $price);
                $insertStmt->bindParam(':quantity', $quantity);
                $insertStmt->bindParam(':image', $image);
                $insertStmt->execute();
            }
        }
    } catch (PDOException $e) {
        error_log("Database error in addToCart: " . $e->getMessage());
        // Continue with session cart only
    }
    
    return ['success' => true, 'message' => 'Product added to cart!'];
}

/**
 * Update item quantity in cart
 * 
 * @param int $productId - Product ID
 * @param int $quantity - New quantity
 * @return array - Status and message
 */
function updateCartItemQuantity($productId, $quantity) {
    initializeCart();
    $userId = getCurrentUserId();
    
    if ($quantity <= 0) {
        return removeFromCart($productId);
    }
    
    // Update session cart
    $found = false;
    foreach ($_SESSION['cart'] as &$item) {
        if ($item['id'] == $productId) {
            $item['quantity'] = $quantity;
            $found = true;
            break;
        }
    }
    
    if (!$found) {
        return ['success' => false, 'message' => 'Product not found in cart!'];
    }
    
    // Update database
    try {
        $conn = getDbConnection();
        if ($conn) {
            $stmt = $conn->prepare("UPDATE cart SET quantity = :quantity, updated_at = NOW() WHERE user_id = :user_id AND product_id = :product_id");
            $stmt->bindParam(':quantity', $quantity);
            $stmt->bindParam(':user_id', $userId);
            $stmt->bindParam(':product_id', $productId);
            $stmt->execute();
        }
    } catch (PDOException $e) {
        error_log("Database error in updateCartItemQuantity: " . $e->getMessage());
        // Continue with session cart only
    }
    
    return ['success' => true, 'message' => 'Cart updated!'];
}

/**
 * Remove item from cart
 * 
 * @param int $productId - Product ID
 * @return array - Status and message
 */
function removeFromCart($productId) {
    initializeCart();
    $userId = getCurrentUserId();
    
    // Remove from session cart
    $found = false;
    foreach ($_SESSION['cart'] as $key => $item) {
        if ($item['id'] == $productId) {
            unset($_SESSION['cart'][$key]);
            $_SESSION['cart'] = array_values($_SESSION['cart']); // Re-index array
            $found = true;
            break;
        }
    }
    
    if (!$found) {
        return ['success' => false, 'message' => 'Product not found in cart!'];
    }
    
    // Remove from database
    try {
        $conn = getDbConnection();
        if ($conn) {
            $stmt = $conn->prepare("DELETE FROM cart WHERE user_id = :user_id AND product_id = :product_id");
            $stmt->bindParam(':user_id', $userId);
            $stmt->bindParam(':product_id', $productId);
            $stmt->execute();
        }
    } catch (PDOException $e) {
        error_log("Database error in removeFromCart: " . $e->getMessage());
        // Continue with session cart only
    }
    
    return ['success' => true, 'message' => 'Product removed from cart!'];
}

/**
 * Get cart contents from both session and database
 * 
 * @return array - Cart items
 */
function getCart() {
    initializeCart();
    
    // Try to sync with database
    syncCartWithDatabase();
    
    return $_SESSION['cart'];
}

/**
 * Sync session cart with database
 */
function syncCartWithDatabase() {
    $userId = getCurrentUserId();
    
    try {
        $conn = getDbConnection();
        if ($conn) {
            $stmt = $conn->prepare("SELECT * FROM cart WHERE user_id = :user_id");
            $stmt->bindParam(':user_id', $userId);
            $stmt->execute();
            
            if ($stmt->rowCount() > 0) {
                // Replace session cart with database cart
                $dbCart = $stmt->fetchAll(PDO::FETCH_ASSOC);
                $_SESSION['cart'] = [];
                
                foreach ($dbCart as $item) {
                    $_SESSION['cart'][] = [
                        'id' => $item['product_id'],
                        'name' => $item['product_name'],
                        'price' => $item['price'],
                        'image' => $item['image'],
                        'quantity' => $item['quantity']
                    ];
                }
            }
        }
    } catch (PDOException $e) {
        error_log("Database error in syncCartWithDatabase: " . $e->getMessage());
        // Continue with session cart only
    }
}

/**
 * Get cart total items
 * 
 * @return int - Number of items in cart
 */
function getCartItemCount() {
    initializeCart();
    $count = 0;
    foreach ($_SESSION['cart'] as $item) {
        $count += $item['quantity'];
    }
    return $count;
}

/**
 * Get cart subtotal
 * 
 * @return float - Cart subtotal
 */
function getCartSubtotal() {
    initializeCart();
    $subtotal = 0;
    foreach ($_SESSION['cart'] as $item) {
        $subtotal += $item['price'] * $item['quantity'];
    }
    return $subtotal;
}

/**
 * Clear the cart
 * 
 * @return array - Status and message
 */
function clearCart() {
    initializeCart();
    $_SESSION['cart'] = [];
    $userId = getCurrentUserId();
    
    // Clear from database
    try {
        $conn = getDbConnection();
        if ($conn) {
            $stmt = $conn->prepare("DELETE FROM cart WHERE user_id = :user_id");
            $stmt->bindParam(':user_id', $userId);
            $stmt->execute();
        }
    } catch (PDOException $e) {
        error_log("Database error in clearCart: " . $e->getMessage());
        // Continue with session cart only
    }
    
    return ['success' => true, 'message' => 'Cart cleared!'];
}
?>
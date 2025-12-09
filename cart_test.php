<?php
// Display errors for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Cart API Test Page</h1>";

// Print request method
echo "<p>Request Method: " . $_SERVER['REQUEST_METHOD'] . "</p>";

// Test if the cart_functions.php file is readable
echo "<p>Testing cart_functions.php: ";
if (file_exists('cart_functions.php')) {
    echo "File exists";
    
    // Try to include it
    require_once 'cart_functions.php';
    echo " and was included successfully.</p>";
    
    // Initialize session
    echo "<p>Session ID: " . session_id() . "</p>";
    
    // Test cart functions
    echo "<h2>Cart Functions Test:</h2>";
    echo "<pre>";
    // Add a test item
    $result = addToCart(999, "Test Product", 19.99, "test.jpg", 1);
    echo "Add to cart result: ";
    print_r($result);
    
    // Get cart count
    echo "\nCart count: " . getCartItemCount();
    
    // Get cart contents
    echo "\nCart contents: ";
    print_r(getCart());
    
    echo "</pre>";
} else {
    echo "File NOT found!</p>";
}

// Form for manual testing
echo <<<HTML
<h2>Test Form</h2>
<form method="POST" action="add_to_cart_api.php">
    <input type="hidden" name="productId" value="123">
    <input type="hidden" name="name" value="Test Product">
    <input type="hidden" name="price" value="29.99">
    <input type="hidden" name="image" value="test.jpg">
    <input type="hidden" name="quantity" value="1">
    <input type="hidden" name="action" value="add">
    <button type="submit">Add To Cart via Direct Form Submit</button>
</form>

<h2>AJAX Test</h2>
<button id="testAjax">Test AJAX Add to Cart</button>

<div id="result" style="margin-top: 20px; padding: 10px; background: #f5f5f5;"></div>

<script>
document.getElementById('testAjax').addEventListener('click', function() {
    // Create form data
    const formData = new FormData();
    formData.append('productId', '456');
    formData.append('name', 'AJAX Test Product');
    formData.append('price', '39.99');
    formData.append('image', 'test.jpg');
    formData.append('quantity', '1');
    formData.append('action', 'add');
    
    // Log what we're sending
    console.log('Sending AJAX request to add_to_cart_api.php');
    
    // Display status
    document.getElementById('result').innerHTML = 'Sending request...';
    
    // Send AJAX request
    fetch('add_to_cart_api.php', {
        method: 'POST',
        body: formData
    })
    .then(response => {
        // Log response details
        console.log('Response status:', response.status);
        console.log('Response status text:', response.statusText);
        
        document.getElementById('result').innerHTML += '<br>Response status: ' + response.status + ' ' + response.statusText;
        
        // Check if response is ok
        if (!response.ok) {
            throw new Error('Network response was not ok: ' + response.status);
        }
        return response.text();
    })
    .then(text => {
        // Log the raw response
        console.log('Raw response:', text);
        document.getElementById('result').innerHTML += '<br>Raw response: ' + text;
        
        // Try to parse as JSON
        try {
            return JSON.parse(text);
        } catch (e) {
            console.error('Error parsing JSON:', e);
            document.getElementById('result').innerHTML += '<br>Error parsing JSON: ' + e.message;
            throw new Error('Invalid JSON response from server');
        }
    })
    .then(data => {
        console.log('Parsed data:', data);
        document.getElementById('result').innerHTML += '<br>Request successful! Response: ' + JSON.stringify(data);
    })
    .catch(error => {
        console.error('Error:', error);
        document.getElementById('result').innerHTML += '<br>Error: ' + error.message;
    });
});
</script>
HTML;
?>
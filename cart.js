// Cart.js - Handles all cart interactions via AJAX

document.addEventListener('DOMContentLoaded', function() {
    // Initialize cart count display in header
    updateCartCountDisplay();
});

/**
 * Add an item to the cart
 * @param {number} productId - The product ID
 * @param {string} productName - The product name
 * @param {number} price - The product price
 * @param {string} image - The product image URL
 * @param {number} quantity - The quantity to add (default: 1)
 */
function addToCart(productId, productName, price, image, quantity = 1) {
    const data = {
        action: 'add',
        product_id: productId,
        product_name: productName,
        price: price,
        image: image,
        quantity: quantity
    };

    // Send AJAX request to add item to cart
    fetch('add_to_cart_api.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(data)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification('Product added to cart!', 'success');
            updateCartCountDisplay(data.cartCount);
        } else {
            showNotification('Failed to add product to cart.', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('An error occurred. Please try again.', 'error');
    });
}

/**
 * Update cart item quantity
 * @param {number} productId - The product ID
 * @param {number} quantity - The new quantity
 */
function updateCartQuantity(productId, quantity) {
    const data = {
        action: 'update',
        product_id: productId,
        quantity: parseInt(quantity)
    };

    fetch('add_to_cart_api.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(data)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification('Cart updated!', 'success');
            updateCartCountDisplay(data.cartCount);
            updateCartUI();
        } else {
            showNotification('Failed to update cart.', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('An error occurred. Please try again.', 'error');
    });
}

/**
 * Remove item from cart
 * @param {number} productId - The product ID to remove
 */
function removeFromCart(productId) {
    const data = {
        action: 'remove',
        product_id: productId
    };

    fetch('add_to_cart_api.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(data)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification('Product removed from cart!', 'success');
            updateCartCountDisplay(data.cartCount);
            
            // Remove item from the UI
            const cartItem = document.querySelector(`.cart-item[data-product-id="${productId}"]`);
            if (cartItem) {
                cartItem.remove();
                updateCartUI();
                
                // Check if cart is empty now
                const cartItems = document.querySelectorAll('.cart-item');
                if (cartItems.length === 0) {
                    location.reload(); // Refresh to show empty cart message
                }
            }
        } else {
            showNotification('Failed to remove product from cart.', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('An error occurred. Please try again.', 'error');
    });
}

/**
 * Update the cart interface after changes
 */
function updateCartUI() {
    // Recalculate total
    let cartTotal = 0;
    let tax = 0;
    let shipping = 0;
    const cartItems = document.querySelectorAll('.cart-item');
    
    cartItems.forEach(item => {
        const productId = item.dataset.productId;
        // Get price element (the div with text-gray-600 class)
        const priceEl = item.querySelector('.text-gray-600');
        if (!priceEl) return;
        
        // Parse the price (remove currency symbol and commas)
        const price = parseFloat(priceEl.textContent.replace('₹', '').replace(/,/g, ''));
        const quantity = parseInt(item.querySelector('.quantity-input').value);
        const itemTotal = price * quantity;
        
        cartTotal += itemTotal;
        
        // Update the item total display
        const totalEl = item.querySelector('.flex-col.items-end .font-semibold');
        if (totalEl) {
            totalEl.textContent = '₹' + numberWithCommas(itemTotal.toFixed(2));
        }
    });
    
    // Calculate tax and shipping
    tax = cartTotal * 0.18; // 18% tax
    shipping = (cartTotal > 999) ? 0 : 99; // Free shipping over ₹999
    const orderTotal = cartTotal + tax + shipping;
    
    // Update the order summary
    const subtotalEl = document.querySelector('.space-y-3.py-4 div:nth-child(1) span:last-child');
    if (subtotalEl) {
        subtotalEl.textContent = '₹' + numberWithCommas(cartTotal.toFixed(2));
    }
    
    const taxEl = document.querySelector('.space-y-3.py-4 div:nth-child(2) span:last-child');
    if (taxEl) {
        taxEl.textContent = '₹' + numberWithCommas(tax.toFixed(2));
    }
    
    const shippingEl = document.querySelector('.space-y-3.py-4 div:nth-child(3) span:last-child');
    if (shippingEl) {
        shippingEl.textContent = shipping > 0 ? '₹' + numberWithCommas(shipping.toFixed(2)) : 'Free';
    }
    
    // Update order total
    const orderTotalEl = document.querySelector('.border-t.border-gray-200 .flex.justify-between.font-bold span:last-child');
    if (orderTotalEl) {
        orderTotalEl.textContent = '₹' + numberWithCommas(orderTotal.toFixed(2));
    }
    
    // Update shipping message
    const shippingMsgEl = document.querySelector('.text-xs.text-gray-500.mt-1');
    if (shippingMsgEl) {
        shippingMsgEl.textContent = shipping === 0 ? 'Free shipping applied!' : 'Free shipping on orders above ₹999';
    }
    
    // Update cart count in header
    updateCartCountDisplay(cartItems.length);
}

/**
 * Format number with commas for thousands
 * @param {string|number} x - The number to format
 * @return {string} Formatted number
 */
function numberWithCommas(x) {
    return x.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
}

/**
 * Update the cart count in the header
 * @param {number} count - The cart count (optional)
 */
function updateCartCountDisplay(count = null) {
    // If count is provided, update directly
    if (count !== null) {
        const cartCountElements = document.querySelectorAll('.cart-count');
        cartCountElements.forEach(element => {
            element.textContent = count;
        });
        return;
    }
    
    // Otherwise fetch the current count
    fetch('add_to_cart_api.php?action=get_count', {
        method: 'GET'
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const cartCountElements = document.querySelectorAll('.cart-count');
            cartCountElements.forEach(element => {
                element.textContent = data.cart_count;
            });
        }
    })
    .catch(error => {
        console.error('Error fetching cart count:', error);
    });
}

/**
 * Display a notification message
 * @param {string} message - The message to display
 * @param {string} type - The notification type ('success' or 'error')
 */
function showNotification(message, type = 'success') {
    // Check if notification container exists, create if not
    let notificationContainer = document.querySelector('.notification-container');
    if (!notificationContainer) {
        notificationContainer = document.createElement('div');
        notificationContainer.className = 'notification-container';
        notificationContainer.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 1000;
        `;
        document.body.appendChild(notificationContainer);
    }
    
    // Create notification element
    const notification = document.createElement('div');
    notification.className = `notification ${type}`;
    notification.style.cssText = `
        padding: 15px 20px;
        margin-bottom: 10px;
        border-radius: 4px;
        color: white;
        box-shadow: 0 2px 5px rgba(0,0,0,0.2);
        animation: slideIn 0.3s ease-out;
        background-color: ${type === 'success' ? '#4CAF50' : '#f44336'};
    `;
    notification.textContent = message;
    
    // Add to container
    notificationContainer.appendChild(notification);
    
    // Remove after 3 seconds
    setTimeout(() => {
        notification.style.animation = 'slideOut 0.3s ease-out';
        notification.addEventListener('animationend', () => {
            notification.remove();
        });
    }, 3000);
    
    // Add animation styles if not already added
    if (!document.querySelector('#notification-styles')) {
        const styleEl = document.createElement('style');
        styleEl.id = 'notification-styles';
        styleEl.textContent = `
            @keyframes slideIn {
                from { transform: translateX(100%); opacity: 0; }
                to { transform: translateX(0); opacity: 1; }
            }
            @keyframes slideOut {
                from { transform: translateX(0); opacity: 1; }
                to { transform: translateX(100%); opacity: 0; }
            }
        `;
        document.head.appendChild(styleEl);
    }
}
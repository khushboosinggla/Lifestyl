/**
 * LifeStyle Store - Wishlist System
 * Handles wishlist interactions with the PHP backend
 */

document.addEventListener('DOMContentLoaded', function() {
    // Initial wishlist count update
    updateWishlistCount();

    // Find and attach event listeners to all wishlist buttons
    const wishlistButtons = document.querySelectorAll('.add-to-wishlist-btn');
    
    wishlistButtons.forEach(button => {
        button.removeEventListener('click', handleAddToWishlistClick);
        button.addEventListener('click', handleAddToWishlistClick);
    });
});

/**
 * Handle add to wishlist button click
 */
function handleAddToWishlistClick(event) {
    event.preventDefault();
    
    // Prevent duplicate processing
    if (this.dataset.processing === 'true') {
        return;
    }
    
    this.dataset.processing = 'true';
    
    // Get product data from data attributes
    const productId = this.dataset.productId;
    const productName = this.dataset.productName;
    const productPrice = this.dataset.productPrice;
    const productImage = this.dataset.productImage;
    
    // Validate product data
    if (!productId || !productName || !productPrice) {
        showMessage('Missing product information', 'error');
        this.dataset.processing = 'false';
        return;
    }
    
    // Add to wishlist via AJAX
    addToWishlist(productId, productName, productPrice, productImage)
        .finally(() => {
            // Reset processing flag after AJAX completes
            setTimeout(() => {
                this.dataset.processing = 'false';
            }, 500);
        });
}

/**
 * Add product to wishlist via AJAX
 */
function addToWishlist(productId, name, price, image) {
    // Create form data
    const formData = new FormData();
    formData.append('productId', productId);
    formData.append('name', name);
    formData.append('price', price);
    formData.append('image', image);
    formData.append('action', 'add');
    
    // Send AJAX request
    return fetch('wishlist_api.php', {
        method: 'POST',
        body: formData
    })
    .then(response => {
        if (!response.ok) {
            throw new Error(`Network response was not ok: ${response.status}`);
        }
        return response.text();
    })
    .then(data => {
        try {
            const result = JSON.parse(data);
            
            if (result.success === true) {
                showMessage('Product added to wishlist!', 'success');
                updateWishlistCount();
            } else {
                showMessage(result.message || 'Failed to add to wishlist', 'error');
            }
        } catch (e) {
            console.error('JSON parse error:', e);
            showMessage('Unexpected response from server', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showMessage('Failed to add product to wishlist', 'error');
    });
}

/**
 * Update the wishlist count display
 */
function updateWishlistCount() {
    fetch('wishlist_api.php')
        .then(response => {
            if (!response.ok) {
                throw new Error(`Network response was not ok: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            // Update UI elements with wishlist count
            const wishlistCountElements = document.querySelectorAll('.wishlist-count');
            wishlistCountElements.forEach(element => {
                element.textContent = data.wishlistCount || 0;
            });
        })
        .catch(error => {
            console.error('Error updating wishlist count:', error);
        });
}

/**
 * Remove item from wishlist
 */
function removeFromWishlist(productId) {
    const formData = new FormData();
    formData.append('productId', productId);
    formData.append('action', 'remove');
    
    fetch('wishlist_api.php', {
        method: 'POST',
        body: formData
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Network response was not ok');
        }
        return response.json();
    })
    .then(data => {
        if (data.success === true) {
            updateWishlistCount();
            showMessage('Product removed from wishlist', 'success');
            
            // If on wishlist page, remove the item from DOM or reload page
            const wishlistItem = document.querySelector(`.wishlist-item[data-product-id="${productId}"]`);
            if (wishlistItem) {
                wishlistItem.remove();
                
                // Check if wishlist is empty
                const wishlistItems = document.querySelectorAll('.wishlist-item');
                if (wishlistItems.length === 0) {
                    window.location.reload();
                }
            }
        } else {
            showMessage(data.message || 'Failed to remove product', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showMessage('Failed to remove item from wishlist', 'error');
    });
}

/**
 * Show a message to the user (reusing the function from cart.js)
 */
function showMessage(message, type = 'info') {
    // Create or find message container
    let messageContainer = document.querySelector('.wishlist-message');
    
    if (!messageContainer) {
        messageContainer = document.createElement('div');
        messageContainer.className = 'wishlist-message';
        messageContainer.style.position = 'fixed';
        messageContainer.style.top = '20px';
        messageContainer.style.right = '20px';
        messageContainer.style.zIndex = '9999';
        document.body.appendChild(messageContainer);
    }
    
    // Create message element
    const messageElement = document.createElement('div');
    messageElement.className = `message ${type}`;
    messageElement.style.padding = '10px 15px';
    messageElement.style.marginBottom = '10px';
    messageElement.style.borderRadius = '4px';
    messageElement.style.backgroundColor = type === 'success' ? '#e8f5e9' : type === 'error' ? '#ffebee' : '#e3f2fd';
    messageElement.style.color = type === 'success' ? '#2e7d32' : type === 'error' ? '#c62828' : '#0d47a1';
    messageElement.style.boxShadow = '0 2px 5px rgba(0,0,0,0.1)';
    messageElement.style.transition = 'opacity 0.5s';
    messageElement.textContent = message;
    
    // Add to container
    messageContainer.appendChild(messageElement);
    
    // Remove after delay
    setTimeout(() => {
        messageElement.style.opacity = '0';
        setTimeout(() => messageElement.remove(), 500);
    }, 3000);
}
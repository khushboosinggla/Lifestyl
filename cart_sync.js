/**
 * LifeStyl Cart and Wishlist Synchronization
 * 
 * This script synchronizes data between localStorage and PHP sessions
 * for seamless integration between HTML and PHP pages
 */

document.addEventListener('DOMContentLoaded', function() {
    // Sync cart data
    syncCartData();
    
    // Sync wishlist data
    syncWishlistData();
    
    // Add event listener for the load complete event
    window.addEventListener('load', function() {
        // Update cart and wishlist counts in the navbar
        updateNavCounts();
    });
});

/**
 * Synchronize cart data from localStorage to server
 */
function syncCartData() {
    // Get cart data from localStorage
    const cartData = JSON.parse(localStorage.getItem('cart')) || [];
    
    // Only proceed if we have cart items
    if (cartData.length > 0) {
        // Prepare data for sending to server
        const formData = new FormData();
        formData.append('action', 'sync_cart');
        formData.append('cart_data', JSON.stringify(cartData));
        
        // Send data to server
        fetch('sync_data.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            console.log('Cart sync complete:', data);
        })
        .catch(error => {
            console.error('Error syncing cart:', error);
        });
    }
}

/**
 * Synchronize wishlist data from localStorage to server
 */
function syncWishlistData() {
    // Get wishlist data from localStorage
    const wishlistData = JSON.parse(localStorage.getItem('wishlist')) || [];
    
    // Only proceed if we have wishlist items
    if (wishlistData.length > 0) {
        // Prepare data for sending to server
        const formData = new FormData();
        formData.append('action', 'sync_wishlist');
        formData.append('wishlist_data', JSON.stringify(wishlistData));
        
        // Send data to server
        fetch('sync_data.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            console.log('Wishlist sync complete:', data);
        })
        .catch(error => {
            console.error('Error syncing wishlist:', error);
        });
    }
}

/**
 * Update cart and wishlist counts in the navbar
 */
function updateNavCounts() {
    // Get cart and wishlist data from localStorage
    const cart = JSON.parse(localStorage.getItem('cart')) || [];
    const wishlist = JSON.parse(localStorage.getItem('wishlist')) || [];
    
    // Count total cart items (accounting for quantities)
    const totalCartItems = cart.reduce((total, item) => total + (parseInt(item.quantity) || 1), 0);
    
    // Update all cart count elements
    const cartCountElements = document.querySelectorAll('.cart-count');
    cartCountElements.forEach(element => {
        element.textContent = totalCartItems;
        
        if (totalCartItems === 0) {
            element.style.display = 'none';
        } else {
            element.style.display = 'flex';
        }
    });
    
    // Update all wishlist count elements
    const wishlistCountElements = document.querySelectorAll('.wishlist-count');
    wishlistCountElements.forEach(element => {
        element.textContent = wishlist.length;
        
        if (wishlist.length === 0) {
            element.style.display = 'none';
        } else {
            element.style.display = 'flex';
        }
    });
    
    // Also update any other wishlist badge that might exist
    const wishlistBadge = document.getElementById('wishlist-count');
    if (wishlistBadge) {
        wishlistBadge.textContent = wishlist.length;
    }
}
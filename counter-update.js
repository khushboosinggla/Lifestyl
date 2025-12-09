/**
 * counter-update.js
 * Handles the dynamic updating of cart and wishlist counters across the site
 */

// Function to update all cart and wishlist count displays
function updateCountersFromStorage() {
  // Get current counts from localStorage
  const cartCount = localStorage.getItem('cartCount') || '0';
  const wishlistCount = localStorage.getItem('wishlistCount') || '0';
  
  // Update all cart count elements
  document.querySelectorAll('.cart-count').forEach(element => {
    element.textContent = cartCount;
    // Hide the badge if count is 0
    if (parseInt(cartCount) === 0) {
      element.style.display = 'none';
    } else {
      element.style.display = 'flex';
    }
  });
  
  // Update all wishlist count elements
  document.querySelectorAll('.wishlist-count').forEach(element => {
    element.textContent = wishlistCount;
    // Hide the badge if count is 0
    if (parseInt(wishlistCount) === 0) {
      element.style.display = 'none';
    } else {
      element.style.display = 'flex';
    }
  });
}

// Event listeners for changes in localStorage
window.addEventListener('storage', function(event) {
  if (event.key === 'cartCount' || event.key === 'wishlistCount') {
    updateCountersFromStorage();
  }
});

// Custom event for updating counts
document.addEventListener('update-counters', function() {
  updateCountersFromStorage();
});

// Run on page load
document.addEventListener('DOMContentLoaded', function() {
  updateCountersFromStorage();
  
  // If cart_sync.js has loaded and updateNavCounts exists, run it
  if (typeof updateNavCounts === 'function') {
    updateNavCounts();
  }
});
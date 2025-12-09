<?php
// Start the session to access wishlist data
session_start();

// Initialize wishlist if it doesn't exist
if (!isset($_SESSION['wishlist'])) {
    $_SESSION['wishlist'] = [];
}

// Include header
include 'header.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Wishlist - LIFE STYLE</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/remixicon/4.6.0/remixicon.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@100..900&display=swap" rel="stylesheet">
    <style>
        * {
            font-family: 'Montserrat', sans-serif;
        }
        .wishlist-container {
            max-width: 1200px;
            margin: 40px auto;
            padding: 0 20px;
        }
        .wishlist-item {
            background: white;
            border-radius: 8px;
            overflow: hidden;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            height: 100%;
            display: flex;
            flex-direction: column;
        }
        .wishlist-item:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
        }
        .wishlist-item-image {
            height: 220px;
            overflow: hidden;
            position: relative;
        }
        .wishlist-item-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.5s ease;
        }
        .wishlist-item:hover .wishlist-item-image img {
            transform: scale(1.05);
        }
        .wishlist-item-details {
            padding: 15px;
            flex-grow: 1;
            display: flex;
            flex-direction: column;
        }
        .wishlist-item-title {
            font-weight: 600;
            margin-bottom: 5px;
            color: #333;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
            line-height: 1.4;
        }
        .wishlist-item-price {
            color: #e8b4b8;
            font-weight: 700;
            margin: 4px 0 15px;
            font-size: 1.1rem;
        }
        .btn-move-to-cart {
            background-color: #2F2723;
            color: white;
            width: 100%;
            padding: 10px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-weight: 500;
            transition: background-color 0.3s;
            margin-bottom: 8px;
        }
        .btn-move-to-cart:hover {
            background-color: #4a3f38;
        }
        .btn-remove-wishlist {
            background-color: transparent;
            color: #2F2723;
            width: 100%;
            padding: 10px;
            border: 1px solid #2F2723;
            border-radius: 4px;
            cursor: pointer;
            font-weight: 500;
            transition: all 0.3s;
        }
        .btn-remove-wishlist:hover {
            background-color: #f9f9f9;
        }
        .empty-wishlist {
            text-align: center;
            padding: 50px 0;
        }
        .empty-wishlist i {
            font-size: 4rem;
            color: #d1d5db;
            margin-bottom: 20px;
        }
        .wishlist-actions {
            margin-top: auto;
        }
        #wishlist-items {
            transition: opacity 0.3s ease;
        }
    </style>
</head>
<body class="bg-gray-50">

<div class="wishlist-container">
    <h1 class="text-3xl font-bold mb-8">My Wishlist</h1>
    
    <div id="empty-wishlist-template" style="display: none;">
        <div class="empty-wishlist">
            <i class="ri-heart-line"></i>
            <h2 class="text-xl font-medium mb-2">Your Wishlist is Empty</h2>
            <p class="text-gray-600 mb-6">Save items you love to your wishlist and they'll appear here.</p>
            <a href="index.html" class="bg-black text-white px-8 py-3 inline-block hover:bg-gray-800">Continue Shopping</a>
        </div>
    </div>
    
    <div id="wishlist-container">
        <?php if (empty($_SESSION['wishlist'])): ?>
            <div class="empty-wishlist">
                <i class="ri-heart-line"></i>
                <h2 class="text-xl font-medium mb-2">Your Wishlist is Empty</h2>
                <p class="text-gray-600 mb-6">Save items you love to your wishlist and they'll appear here.</p>
                <a href="index.html" class="bg-black text-white px-8 py-3 inline-block hover:bg-gray-800">Continue Shopping</a>
            </div>
        <?php else: ?>
            <div id="wishlist-items" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
                <?php foreach ($_SESSION['wishlist'] as $id => $item): ?>
                    <div class="wishlist-item" data-id="<?= $id ?>">
                        <div class="wishlist-item-image">
                            <img src="<?= htmlspecialchars($item['image']) ?>" alt="<?= htmlspecialchars($item['name']) ?>">
                        </div>
                        <div class="wishlist-item-details">
                            <h3 class="wishlist-item-title"><?= htmlspecialchars($item['name']) ?></h3>
                            <p class="wishlist-item-price">₹<?= number_format($item['price'], 2) ?></p>
                            <div class="wishlist-actions">
                                <button type="button" class="btn-move-to-cart" data-id="<?= $id ?>">
                                    <i class="ri-shopping-cart-line mr-1"></i> Add to Cart
                                </button>
                                <button type="button" class="btn-remove-wishlist" data-id="<?= $id ?>">
                                    <i class="ri-delete-bin-line mr-1"></i> Remove
                                </button>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
    $(document).ready(function() {
        // Move item to cart using AJAX
        $(document).on('click', '.btn-move-to-cart', function() {
            const productId = $(this).data('id');
            const $item = $(this).closest('.wishlist-item');
            
            $.ajax({
                url: 'wishlist_api.php',
                method: 'POST',
                data: {
                    action: 'move_to_cart',
                    productId: productId
                },
                success: function(response) {
                    if (response.success) {
                        // Update wishlist count in the header
                        $('.wishlist-count').text(response.wishlistCount);
                        
                        // Update cart count in the header
                        $('.cart-count').text(response.cartCount);
                        
                        // Remove item from view with animation
                        $item.fadeOut(300, function() {
                            $(this).remove();
                            
                            // Check if wishlist is empty
                            if ($('#wishlist-items .wishlist-item').length === 0) {
                                $('#wishlist-items').remove();
                                $('#wishlist-container').html($('#empty-wishlist-template').html());
                            }
                        });
                    }
                }
            });
        });
        
        // Remove item from wishlist using AJAX
        $(document).on('click', '.btn-remove-wishlist', function() {
            const productId = $(this).data('id');
            const $item = $(this).closest('.wishlist-item');
            
            $.ajax({
                url: 'wishlist_api.php',
                method: 'POST',
                data: {
                    action: 'remove',
                    productId: productId
                },
                success: function(response) {
                    if (response.success) {
                        // Update wishlist count in header
                        $('.wishlist-count').text(response.count);
                        
                        // Remove item from view with animation
                        $item.fadeOut(300, function() {
                            $(this).remove();
                            
                            // Check if wishlist is empty
                            if ($('#wishlist-items .wishlist-item').length === 0) {
                                $('#wishlist-items').remove();
                                $('#wishlist-container').html($('#empty-wishlist-template').html());
                            }
                        });
                    }
                }
            });
        });
    });
    
    // Update wishlist count on page load
    $.post('wishlist_api.php', { action: 'get_count' }, function(response) {
        if (response.success) {
            $('.wishlist-count').text(response.count);
        }
    });
</script>

<?php include 'footer.php'; ?>
</body>
</html>
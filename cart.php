<?php
// cart.php - Shopping cart page

// Start the session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Include your cart functions
require_once 'cart_functions.php';

// Handle cart actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'remove':
                if (isset($_POST['product_id'], $_POST['collection'])) {
                    removeFromCart($_POST['collection'], $_POST['product_id']);
                    // Redirect to avoid form resubmission
                    header("Location: cart.php?removed=1");
                    exit;
                }
                break;
            
            case 'update':
                // Handle quantity updates (non-AJAX version)
                if (isset($_POST['quantities'])) {
                    foreach ($_POST['quantities'] as $key => $quantity) {
                        if (isset($_SESSION["shopping_cart"][$key])) {
                            $_SESSION["shopping_cart"][$key]['product_quantity'] = max(1, (int)$quantity);
                        }
                    }
                    // Redirect to avoid form resubmission
                    header("Location: cart.php?updated=1");
                    exit;
                }
                break;
        }
    }
}

// Calculate cart total
$cart_total = calculateCartTotal();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shopping Cart</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            margin: 0;
            padding: 20px;
            background-color: #f5f5f5;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
            background-color: #fff;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        h1, h2 {
            color: #333;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        th, td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        th {
            background-color: #f9f9f9;
        }
        .quantity-input {
            width: 60px;
            padding: 8px;
            text-align: center;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        .btn {
            background-color: #4CAF50;
            color: white;
            padding: 10px 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            text-decoration: none;
            display: inline-block;
        }
        .btn-danger {
            background-color: #f44336;
        }
        .btn-update {
            background-color: #2196F3;
        }
        .btn:hover {
            opacity: 0.9;
        }
        .cart-actions {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 20px;
        }
        .cart-summary {
            margin-top: 30px;
            padding: 20px;
            background-color: #f9f9f9;
            border-radius: 5px;
        }
        .cart-total {
            font-size: 18px;
            font-weight: bold;
            margin: 10px 0;
            text-align: right;
        }
        .message {
            padding: 10px;
            margin: 10px 0;
            border-radius: 4px;
        }
        .message-success {
            background-color: #e8f5e9;
            color: #4CAF50;
        }
        .message-error {
            background-color: #ffebee;
            color: #f44336;
        }
        .empty-cart {
            text-align: center;
            padding: 40px;
            color: #777;
        }
    </style>
    <script>
        // JavaScript function to update cart items via AJAX
        function updateCartItem(productId, collection, quantity) {
            // Create form data
            const formData = new FormData();
            formData.append('action', 'update_quantity');
            formData.append('product_id', productId);
            formData.append('collection', collection);
            formData.append('quantity', quantity);
            
            // Send AJAX request
            fetch('update_cart.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    // Update displayed total
                    document.getElementById('cart-total-value').textContent = '$' + data.cart_total.toFixed(2);
                    
                    // Update item subtotal (if element exists)
                    const subtotalElement = document.getElementById('subtotal-' + productId);
                    if (subtotalElement) {
                        const price = parseFloat(document.getElementById('price-' + productId).dataset.price);
                        subtotalElement.textContent = '$' + (price * quantity).toFixed(2);
                    }
                }
            })
            .catch(error => {
                console.error('Error updating cart:', error);
            });
        }
        
        // JavaScript function to remove cart item via AJAX
        function removeCartItem(productId, collection) {
            // Create form data
            const formData = new FormData();
            formData.append('action', 'remove_item');
            formData.append('product_id', productId);
            formData.append('collection', collection);
            
            // Send AJAX request
            fetch('update_cart.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    // Remove row from table
                    document.getElementById('cart-item-' + productId).remove();
                    
                    // Update displayed total
                    document.getElementById('cart-total-value').textContent = '$' + data.cart_total.toFixed(2);
                    
                    // If cart is empty, reload page to show empty cart message
                    if (data.cart_count === 0) {
                        window.location.reload();
                    }
                }
            })
            .catch(error => {
                console.error('Error removing item:', error);
            });
        }
    </script>
</head>
<body>
    <div class="container">
        <h1>Shopping Cart</h1>
        
        <?php if (isset($_GET['removed']) && $_GET['removed'] == 1): ?>
            <div class="message message-success">Item removed from cart successfully.</div>
        <?php endif; ?>
        
        <?php if (isset($_GET['updated']) && $_GET['updated'] == 1): ?>
            <div class="message message-success">Cart updated successfully.</div>
        <?php endif; ?>
        
        <?php if (empty($_SESSION["shopping_cart"])): ?>
            <div class="empty-cart">
                <h2>Your cart is empty</h2>
                <p>Looks like you haven't added any items to your cart yet.</p>
                <p><a href="index.php" class="btn">Continue Shopping</a></p>
            </div>
        <?php else: ?>
            <form method="post" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>">
                <input type="hidden" name="action" value="update">
                
                <table>
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>Price</th>
                            <th>Quantity</th>
                            <th>Subtotal</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($_SESSION["shopping_cart"] as $key => $item): ?>
                            <tr id="cart-item-<?php echo $item['product_id']; ?>">
                                <td>
                                    <strong><?php echo htmlspecialchars($item['product_name']); ?></strong>
                                    <?php if (!empty($item['product_color'])): ?>
                                        <br>Color: <?php echo htmlspecialchars($item['product_color']); ?>
                                    <?php endif; ?>
                                    <?php if (!empty($item['product_size'])): ?>
                                        <br>Size: <?php echo htmlspecialchars($item['product_size']); ?>
                                    <?php endif; ?>
                                </td>
                                <td id="price-<?php echo $item['product_id']; ?>" data-price="<?php echo $item['product_price']; ?>">
                                    $<?php echo number_format($item['product_price'], 2); ?>
                                </td>
                                <td>
                                    <input type="number" name="quantities[<?php echo $key; ?>]" value="<?php echo $item['product_quantity']; ?>" min="1" class="quantity-input" 
                                        onchange="updateCartItem('<?php echo $item['product_id']; ?>', '<?php echo $item['collection']; ?>', this.value)">
                                </td>
                                <td id="subtotal-<?php echo $item['product_id']; ?>">
                                    $<?php echo number_format($item['product_price'] * $item['product_quantity'], 2); ?>
                                </td>
                                <td>
                                    <button type="button" class="btn btn-danger" onclick="removeCartItem('<?php echo $item['product_id']; ?>', '<?php echo $item['collection']; ?>')">
                                        Remove
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                
                <div class="cart-actions">
                    <a href="index.php" class="btn">Continue Shopping</a>
                    <button type="submit" class="btn btn-update">Update Cart</button>
                </div>
            </form>
            
            <div class="cart-summary">
                <div class="cart-total">
                    Total: <span id="cart-total-value">$<?php echo number_format($cart_total, 2); ?></span>
                </div>
                
                <div style="text-align: right; margin-top: 20px;">
                    <a href="checkout.php" class="btn">Proceed to Checkout</a>
                </div>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
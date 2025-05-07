<?php
// checkout.php - The checkout page for your e-commerce site

// Start the session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Include your cart functions
require_once 'cart_functions.php';
require_once 'payment_processing.php';

// Process form submission
$payment_message = '';
$payment_status = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_payment'])) {
    // Process the payment
    $payment_data = [
        'payment_method' => $_POST['payment_method'],
        'billing_name' => $_POST['billing_name'],
        'billing_email' => $_POST['billing_email'],
        'billing_address' => $_POST['billing_address'],
        'billing_city' => $_POST['billing_city'],
        'billing_state' => $_POST['billing_state'],
        'billing_zip' => $_POST['billing_zip'],
        'billing_country' => $_POST['billing_country'],
    ];
    
    // Add credit card details if applicable
    if ($_POST['payment_method'] === 'credit_card') {
        $payment_data['card_number'] = $_POST['card_number'];
        $payment_data['card_name'] = $_POST['card_name'];
        $payment_data['card_expiry'] = $_POST['card_expiry'];
        $payment_data['card_cvv'] = $_POST['card_cvv'];
    }
    
    $result = processPayment($payment_data);
    $payment_status = $result['status'];
    $payment_message = $result['message'];
    
    if ($payment_status === 'success') {
        // Redirect to order confirmation page
        header("Location: order_confirmation.php?order_id=" . $result['order_id']);
        exit;
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
    <title>Checkout</title>
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
        .cart-summary {
            margin-bottom: 30px;
            border-bottom: 1px solid #ddd;
            padding-bottom: 20px;
        }
        .cart-item {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px solid #eee;
        }
        .checkout-form {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
        }
        .form-section {
            margin-bottom: 20px;
        }
        .form-group {
            margin-bottom: 15px;
        }
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        input, select {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        .payment-methods {
            margin: 20px 0;
        }
        .payment-method {
            margin: 10px 0;
        }
        .btn {
            background-color: #4CAF50;
            color: white;
            padding: 12px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
        }
        .btn:hover {
            background-color: #45a049;
        }
        .error-message {
            color: #f44336;
            padding: 10px;
            margin: 10px 0;
            background-color: #ffebee;
            border-radius: 4px;
        }
        .success-message {
            color: #4CAF50;
            padding: 10px;
            margin: 10px 0;
            background-color: #e8f5e9;
            border-radius: 4px;
        }
        .total-section {
            font-size: 18px;
            font-weight: bold;
            margin: 20px 0;
            text-align: right;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Checkout</h1>
        
        <?php if ($payment_status === 'error' && !empty($payment_message)): ?>
            <div class="error-message">
                <?php echo htmlspecialchars($payment_message); ?>
            </div>
        <?php endif; ?>
        
        <div class="cart-summary">
            <h2>Order Summary</h2>
            
            <?php if (empty($_SESSION["shopping_cart"])): ?>
                <p>Your cart is empty. <a href="products.php">Continue shopping</a></p>
            <?php else: ?>
                <?php foreach ($_SESSION["shopping_cart"] as $item): ?>
                    <div class="cart-item">
                        <div>
                            <strong><?php echo htmlspecialchars($item['product_name']); ?></strong>
                            <?php if (!empty($item['product_color'])): ?>
                                <br>Color: <?php echo htmlspecialchars($item['product_color']); ?>
                            <?php endif; ?>
                            <?php if (!empty($item['product_size'])): ?>
                                <br>Size: <?php echo htmlspecialchars($item['product_size']); ?>
                            <?php endif; ?>
                            <br>Quantity: <?php echo htmlspecialchars($item['product_quantity']); ?>
                        </div>
                        <div>
                            $<?php echo number_format($item['product_price'] * $item['product_quantity'], 2); ?>
                        </div>
                    </div>
                <?php endforeach; ?>
                
                <div class="total-section">
                    Total: $<?php echo number_format($cart_total, 2); ?>
                </div>
            <?php endif; ?>
        </div>
        
        <?php if (!empty($_SESSION["shopping_cart"])): ?>
            <form method="post" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>">
                <div class="checkout-form">
                    <div class="form-section">
                        <h2>Billing Information</h2>
                        
                        <div class="form-group">
                            <label for="billing_name">Full Name</label>
                            <input type="text" id="billing_name" name="billing_name" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="billing_email">Email</label>
                            <input type="email" id="billing_email" name="billing_email" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="billing_address">Address</label>
                            <input type="text" id="billing_address" name="billing_address" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="billing_city">City</label>
                            <input type="text" id="billing_city" name="billing_city" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="billing_state">State/Province</label>
                            <input type="text" id="billing_state" name="billing_state" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="billing_zip">ZIP/Postal Code</label>
                            <input type="text" id="billing_zip" name="billing_zip" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="billing_country">Country</label>
                            <select id="billing_country" name="billing_country" required>
                                <option value="">Select Country</option>
                                <option value="US">United States</option>
                                <option value="CA">Canada</option>
                                <option value="UK">United Kingdom</option>
                                <!-- Add more countries as needed -->
                            </select>
                        </div>
                    </div>
                    
                    <div class="form-section">
                        <h2>Payment Method</h2>
                        
                        <div class="payment-methods">
                            <div class="payment-method">
                                <input type="radio" id="credit_card" name="payment_method" value="credit_card" checked>
                                <label for="credit_card">Credit Card</label>
                            </div>
                            
                            <div class="payment-method">
                                <input type="radio" id="paypal" name="payment_method" value="paypal">
                                <label for="paypal">PayPal</label>
                            </div>
                            
                            <div class="payment-method">
                                <input type="radio" id="stripe" name="payment_method" value="stripe">
                                <label for="stripe">Stripe</label>
                            </div>
                        </div>
                        
                        <div id="credit-card-fields">
                            <div class="form-group">
                                <label for="card_name">Name on Card</label>
                                <input type="text" id="card_name" name="card_name">
                            </div>
                            
                            <div class="form-group">
                                <label for="card_number">Card Number</label>
                                <input type="text" id="card_number" name="card_number" placeholder="1234 5678 9012 3456">
                            </div>
                            
                            <div class="form-group">
                                <label for="card_expiry">Expiry Date (MM/YY)</label>
                                <input type="text" id="card_expiry" name="card_expiry" placeholder="MM/YY">
                            </div>
                            
                            <div class="form-group">
                                <label for="card_cvv">CVV</label>
                                <input type="text" id="card_cvv" name="card_cvv" placeholder="123">
                            </div>
                        </div>
                    </div>
                </div>
                
                <div style="text-align: right; margin-top: 20px;">
                    <a href="cart.php" style="margin-right: 10px;">Back to Cart</a>
                    <button type="submit" name="submit_payment" class="btn">Complete Payment</button>
                </div>
            </form>
            
            <script>
                // Show/hide credit card fields based on payment method selection
                document.addEventListener('DOMContentLoaded', function() {
                    const paymentMethods = document.querySelectorAll('input[name="payment_method"]');
                    const creditCardFields = document.getElementById('credit-card-fields');
                    
                    paymentMethods.forEach(function(method) {
                        method.addEventListener('change', function() {
                            if (this.value === 'credit_card') {
                                creditCardFields.style.display = 'block';
                            } else {
                                creditCardFields.style.display = 'none';
                            }
                        });
                    });
                });
            </script>
        <?php endif; ?>
    </div>
</body>
</html>
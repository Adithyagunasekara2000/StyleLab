<?php
// order_confirmation.php - Order confirmation page

// Start the session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Check if order ID exists in URL
if (!isset($_GET['order_id'])) {
    // Redirect to home page if no order ID is provided
    header("Location: index.php");
    exit;
}

$order_id = $_GET['order_id'];

// In a real application, you would fetch order details from your database
// For demonstration purposes, we'll use sample data
$order_date = date('Y-m-d H:i:s');
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Confirmation</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            margin: 0;
            padding: 20px;
            background-color: #f5f5f5;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
            background-color: #fff;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        h1, h2 {
            color: #333;
        }
        .success-icon {
            text-align: center;
            font-size: 48px;
            color: #4CAF50;
            margin: 20px 0;
        }
        .order-details {
            margin: 20px 0;
            padding: 20px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }
        .detail-row {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px solid #eee;
        }
        .detail-row:last-child {
            border-bottom: none;
        }
        .btn {
            background-color: #4CAF50;
            color: white;
            padding: 12px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            text-decoration: none;
            display: inline-block;
        }
        .btn:hover {
            background-color: #45a049;
        }
        .text-center {
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="success-icon">✓</div>
        
        <h1 class="text-center">Order Confirmation</h1>
        <p class="text-center">Thank you for your purchase! Your order has been successfully placed.</p>
        
        <div class="order-details">
            <h2>Order Details</h2>
            
            <div class="detail-row">
                <strong>Order Number:</strong>
                <span><?php echo htmlspecialchars($order_id); ?></span>
            </div>
            
            <div class="detail-row">
                <strong>Date:</strong>
                <span><?php echo htmlspecialchars($order_date); ?></span>
            </div>
            
            <div class="detail-row">
                <strong>Payment Status:</strong>
                <span>Successful</span>
            </div>
            
        </div>
        
        <p>A confirmation email has been sent to your email address. If you have any questions about your order, please contact our customer support.</p>
        
        <div class="text-center" style="margin-top: 30px;">
            <a href="index.php" class="btn">Continue Shopping</a>
        </div>
    </div>
</body>
</html>
<?php
// update_cart.php - AJAX handler for cart updates

// Start the session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Include your cart functions
require_once 'cart_functions.php';

// Array to store response data
$response = [
    'status' => 'error',
    'message' => 'No action specified',
    'cart_count' => 0,
    'cart_total' => 0
];

// Check if this is an AJAX request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Handle different actions
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'update_quantity':
                // Update item quantity
                if (isset($_POST['product_id'], $_POST['collection'], $_POST['quantity'])) {
                    $product_id = $_POST['product_id'];
                    $collection = $_POST['collection'];
                    $quantity = (int)$_POST['quantity'];
                    
                    // Make sure quantity is at least 1
                    if ($quantity < 1) {
                        $quantity = 1;
                    }
                    
                    // Update the cart item
                    $updated = false;
                    foreach ($_SESSION["shopping_cart"] as $key => $item) {
                        if ($item['product_id'] == $product_id && $item['collection'] == $collection) {
                            $_SESSION["shopping_cart"][$key]['product_quantity'] = $quantity;
                            $updated = true;
                            break;
                        }
                    }
                    
                    if ($updated) {
                        $response['status'] = 'success';
                        $response['message'] = 'Cart updated successfully';
                    } else {
                        $response['message'] = 'Item not found in cart';
                    }
                } else {
                    $response['message'] = 'Missing required parameters';
                }
                break;
                
            case 'remove_item':
                // Remove item from cart
                if (isset($_POST['product_id'], $_POST['collection'])) {
                    $product_id = $_POST['product_id'];
                    $collection = $_POST['collection'];
                    
                    removeFromCart($collection, $product_id);
                    
                    $response['status'] = 'success';
                    $response['message'] = 'Item removed from cart';
                } else {
                    $response['message'] = 'Missing required parameters';
                }
                break;
                
            default:
                $response['message'] = 'Invalid action';
                break;
        }
    }
    
    // Calculate updated cart info
    $response['cart_count'] = count($_SESSION["shopping_cart"]);
    $response['cart_total'] = calculateCartTotal();
}

// Send JSON response
header('Content-Type: application/json');
echo json_encode($response);
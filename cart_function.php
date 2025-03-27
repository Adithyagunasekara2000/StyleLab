<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Initialize shopping cart if not exists
if (!isset($_SESSION["shopping_cart"])) {
    $_SESSION["shopping_cart"] = [];
}


if (!function_exists('addToCart')) {
    function addToCart($item) {
        // Your existing add to cart logic
        if(!isset($_SESSION["shopping_cart"])){
            $_SESSION["shopping_cart"] = array();
        }
        
        $item_exists = false;
        foreach($_SESSION["shopping_cart"] as $key => $existing_item){
            if($existing_item['product_id'] == $item['product_id'] && 
               $existing_item['product_color'] == $item['product_color'] && 
               $existing_item['product_size'] == $item['product_size']){
                $_SESSION["shopping_cart"][$key]['product_quantity'] += $item['product_quantity'];
                $item_exists = true;
                break;
            }
        }
        
        if(!$item_exists){
            $_SESSION["shopping_cart"][] = $item;
        }
    }
}

if (!function_exists('removeFromCart')) {
    function removeFromCart($collection, $product_id) {
        if(!empty($_SESSION["shopping_cart"])){
            foreach($_SESSION["shopping_cart"] as $key => $item){
                if($item['product_id'] == $product_id && $item['collection'] == $collection){
                    unset($_SESSION["shopping_cart"][$key]);
                    $_SESSION["shopping_cart"] = array_values($_SESSION["shopping_cart"]);
                    break;
                }
            }
        }
    }
}

function calculateCartTotal() {
    $total = 0;
    foreach ($_SESSION["shopping_cart"] as $item) {
        // Safely calculate total with fallback values
        $price = $item['product_price'] ?? 0;
        $quantity = $item['product_quantity'] ?? 1;
        $total += $price * $quantity;
    }
    return $total;
}

// Optional: Debug function to print cart contents
function debugCart() {
    echo "<pre>";
    print_r($_SESSION["shopping_cart"]);
    echo "</pre>";
}
?>
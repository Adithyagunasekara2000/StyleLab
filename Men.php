<?php
session_start();
$db_name = "shopping";
$connection = mysqli_connect("localhost","root","",$db_name);

// Cart handling functions
function addToCart($product) {
    if (!isset($_SESSION["shopping_cart"])) {
        $_SESSION["shopping_cart"] = [];
    }

    // Check if product already exists
    $exists = false;
    foreach ($_SESSION["shopping_cart"] as &$item) {
        if ($item['product_id'] == $product['product_id']) {
            $item['product_quantity'] += $product['product_quantity'];
            $exists = true;
            break;
        }
    }

    if (!$exists) {
        $_SESSION["shopping_cart"][] = $product;
    }
}

// Handle add to cart
if(isset($_POST["add"])){
    $newItem = [
        'product_id' => $_GET["id"],
        'product_name' => $_POST["hidden_name"],
        'product_price' => $_POST["hidden_price"],
        'product_quantity' => $_POST["quantity"],
        'product_color' => $_POST["Colours"],
        'product_size' => $_POST["Size"],
        'product_image' => $_POST["hidden_image"]
    ];
    addToCart($newItem);
}

// Handle remove from cart
if(isset($_GET["action"]) && $_GET["action"] == "delete"){
    foreach($_SESSION["shopping_cart"] as $keys => $value){
        if($value["product_id"] == $_GET["id"]){
            unset($_SESSION["shopping_cart"][$keys]);
            $_SESSION["shopping_cart"] = array_values($_SESSION["shopping_cart"]);
            break;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Men's Collection</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
</head>
<body class="bg-gray-50">
    <!-- Navigation -->
    <nav class="bg-white shadow-md">
        <div class="container mx-auto px-4 py-3 flex justify-between items-center">
            <h1 class="text-2xl font-bold text-gray-800">Men's Collection</h1>
            <div class="relative" x-data="{ cartOpen: false }">
                <button 
                    @click="cartOpen = !cartOpen" 
                    class="text-gray-700 hover:text-gray-900 focus:outline-none relative"
                >
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                    </svg>
                    <?php if(!empty($_SESSION["shopping_cart"])): ?>
                        <span class="absolute -top-2 -right-2 bg-red-500 text-white rounded-full px-2 py-0.5 text-xs">
                            <?php echo count($_SESSION["shopping_cart"]); ?>
                        </span>
                    <?php endif; ?>
                </button>

                <!-- Cart Dropdown -->
                <div 
                    x-show="cartOpen" 
                    @click.away="cartOpen = false"
                    class="absolute right-0 mt-2 w-80 bg-white rounded-lg shadow-xl z-20 max-h-96 overflow-y-auto"
                >
                    <div class="p-4">
                        <h3 class="text-lg font-semibold mb-4">Shopping Cart</h3>
                        <?php if(!empty($_SESSION["shopping_cart"])): ?>
                            <?php 
                            $total = 0;
                            foreach($_SESSION["shopping_cart"] as $item): 
                            $total += $item['product_price'] * $item['product_quantity'];
                            ?>
                            <div class="flex items-center mb-4 pb-4 border-b">
                                <img src="<?php echo $item['product_image']; ?>" class="w-16 h-16 object-cover mr-4">
                                <div class="flex-grow">
                                    <h4 class="font-medium"><?php echo $item['product_name']; ?></h4>
                                    <p class="text-sm text-gray-600">
                                        <?php echo $item['product_color']; ?> | 
                                        <?php echo $item['product_size']; ?> | 
                                        Qty: <?php echo $item['product_quantity']; ?>
                                    </p>
                                    <p class="text-sm font-semibold">
                                        $<?php echo number_format($item['product_price'] * $item['product_quantity'], 2); ?>
                                    </p>
                                </div>
                                <a href="Men.php?action=delete&id=<?php echo $item['product_id']; ?>" class="text-red-500 hover:text-red-700">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                    </svg>
                                </a>
                            </div>
                            <?php endforeach; ?>
                            <div class="mt-4">
                                <div class="flex justify-between font-semibold">
                                    <span>Total:</span>
                                    <span>$<?php echo number_format($total, 2); ?></span>
                                </div>
                                <a href="cart.php" class="mt-4 block w-full bg-blue-500 text-white py-2 text-center rounded-lg hover:bg-blue-600 transition">
                                    Proceed to Checkout
                                </a>
                            </div>
                        <?php else: ?>
                            <p class="text-center text-gray-500">Your cart is empty</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <!-- Product Grid -->
    <div class="container mx-auto px-4 py-8">
        <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
            <?php
            $query = "select * from product1 order by id asc";
            $result = mysqli_query($connection,$query);
            if(mysqli_num_rows($result)>0){
                while($row = mysqli_fetch_array($result)){
            ?>
                <div class="bg-white rounded-lg shadow-md overflow-hidden transform transition hover:scale-105">
                    <img src="<?php echo $row["image"];?>" alt="<?php echo $row["description"];?>" class="w-full h-64 object-cover">
                    <div class="p-4">
                        <h3 class="text-lg font-semibold mb-2"><?php echo $row["description"];?></h3>
                        <p class="text-gray-600 mb-2">$<?php echo $row["price"];?></p>
                        <form method="post" action="Men.php?action=add&id=<?php echo $row["id"];?>">
                            <div class="grid grid-cols-2 gap-2 mb-4">
                                <select name="Colours" required class="border rounded px-2 py-1 text-sm">
                                    <option value="" selected disabled>Color</option>
                                    <option>Black</option>
                                    <option>White</option>
                                    <option>Blue</option>
                                    <option>Brown</option>
                                </select>
                                <select name="Size" required class="border rounded px-2 py-1 text-sm">
                                    <option value="" selected disabled>Size</option>
                                    <option>S</option>
                                    <option>M</option>
                                    <option>L</option>
                                    <option>XL</option>
                                    <option>XXL</option>
                                </select>
                            </div>
                            <input type="hidden" name="hidden_name" value="<?php echo $row["description"];?>">
                            <input type="hidden" name="hidden_price" value="<?php echo $row["price"];?>">
                            <input type="hidden" name="hidden_image" value="<?php echo $row["image"];?>">
                            <div class="flex items-center">
                                <input type="number" name="quantity" value="1" min="1" class="w-16 border rounded px-2 py-1 mr-2 text-sm">
                                <button type="submit" name="add" class="flex-grow bg-blue-500 text-white py-2 rounded hover:bg-blue-600 transition">
                                    Add to Cart
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            <?php
                }
            }
            ?>
        </div>
    </div>
</body>
</html>
<?php 
session_start(); 
require_once 'config.php'; 
require_once 'cart_function.php'; // Ensure this is included
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>StyleLab - Modern Fashion Marketplace</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    <style>
        body { font-family: 'Inter', sans-serif; }
        .hero-gradient {
            background: linear-gradient(135deg, #f6d365 0%, #fda085 100%);
        }
        .collection-hover {
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        .collection-hover:hover {
            transform: scale(1.05);
            box-shadow: 0 10px 20px rgba(0,0,0,0.12);
        }
    </style>
</head>
<body class="bg-gray-50">
    <!-- Navigation -->
    <nav class="bg-white shadow-md sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center py-4">
                <div class="flex items-center">
                    <a href="index.php" class="text-2xl font-bold text-gray-800">StyleLab</a>
                </div>
                <div class="hidden md:flex space-x-6">
                    <a href="men.php" class="text-gray-600 hover:text-gray-900 transition">Men</a>
                    <a href="women.php" class="text-gray-600 hover:text-gray-900 transition">Women</a>
                    <a href="kids.php" class="text-gray-600 hover:text-gray-900 transition">Kids</a>
                    <a href="accessories.php" class="text-gray-600 hover:text-gray-900 transition">Accessories</a>
                </div>
                <div class="flex items-center space-x-4">
                    <form action="search.php" method="GET" class="flex items-center">
                        <input type="text" name="query" placeholder="Search" class="border rounded-md px-3 py-2 w-48">
                        <button type="submit" class="ml-2">🔍</button>
                    </form>
                    <a href="login.php" class="text-gray-600 hover:text-gray-900">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                        </svg>
                    </a>

                    <!-- Dynamic Cart Dropdown -->
                    <div class="relative" x-data="{ cartOpen: false }">
                        <button 
                            @click="cartOpen = !cartOpen" 
                            class="text-gray-600 hover:text-gray-900 relative"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
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
                            x-transition:enter="transition ease-out duration-300"
                            x-transition:enter-start="opacity-0 transform scale-90"
                            x-transition:enter-end="opacity-100 transform scale-100"
                            x-transition:leave="transition ease-in duration-200"
                            x-transition:leave-start="opacity-100 transform scale-100"
                            x-transition:leave-end="opacity-0 transform scale-90"
                            @click.away="cartOpen = false"
                            class="absolute right-0 mt-2 w-80 bg-white rounded-lg shadow-xl z-50 max-h-96 overflow-y-auto"
                        >
                            <div class="p-4">
                                <div class="flex justify-between items-center mb-4">
                                    <h3 class="text-lg font-semibold">Shopping Cart</h3>
                                    <button 
                                        @click="cartOpen = false" 
                                        class="text-gray-500 hover:text-gray-700"
                                    >
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                        </svg>
                                    </button>
                                </div>

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
                                        <a href="<?php 
                                            echo isset($item['collection']) && $item['collection'] === 'men' 
                                                ? 'Men.php' 
                                                : 'Women.php'; 
                                        ?>?action=delete&id=<?php echo $item['product_id']; ?>" class="text-red-500 hover:text-red-700">
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
                                        <a href="cart_function.php" class="mt-4 block w-full bg-blue-500 text-white py-2 text-center rounded-lg hover:bg-blue-600 transition">
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
            </div>
        </div>
    </nav>
</body>
</html>



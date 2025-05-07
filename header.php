<?php 
// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start(); 
}

// Include necessary files
require_once 'config.php'; 
if (!function_exists('addToCart')) {
    require_once 'cart_functions.php';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>masixSL - Modern Fashion Marketplace</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    
    <style>
    body { font-family: 'Inter', sans-serif; }
    .collection-hover {
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }
    .collection-hover:hover {
        transform: scale(1.05);
        box-shadow: 0 10px 20px rgba(0,0,0,0.12);
    }
    
    /* Improved dropdown styles */
    [x-cloak] { display: none !important; }
    
    /* Ensure dropdowns always stay visible and don't get cut off */
    .relative { overflow: visible; }
    
    /* Better responsive cart dropdown positioning */
    @media (max-width: 640px) {
        .absolute.right-0 {
            right: 0;
            max-width: calc(100vw - 2rem);
        }
    }
    
    /* Add to cart feedback animation */
    @keyframes addedToCart {
        0% { transform: scale(1); }
        50% { transform: scale(1.2); }
        100% { transform: scale(1); }
    }
    .added-to-cart {
        animation: addedToCart 0.5s ease;
    }
</style>
</head>
<body class="bg-white">
   <!-- Navigation -->
<nav class="bg-black text-white sticky top-0 z-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center py-4">
            <!-- Logo -->
            <div class="flex items-center">
                <a href="index.php" class="flex items-center">
                    <img src="images_home/ch2.png" alt="masixSL" class="h-8 sm:h-10 w-auto object-contain">
                </a>
            </div>
           

            <!-- Mobile Menu Button (hidden on larger screens) -->
            <div class="md:hidden" x-data="{ mobileMenuOpen: false }">
                <button @click="mobileMenuOpen = !mobileMenuOpen" class="text-white">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                </button>
                
                <!-- Mobile Menu - Right-aligned with improved styling -->
                <div x-show="mobileMenuOpen" @click.away="mobileMenuOpen = false" 
                    class="absolute top-16 right-0 w-64 bg-black z-50 p-4 rounded-bl-lg shadow-lg">
                    <form action="search.php" method="GET" class="flex items-center my-4 px-4 ">
                        <input type="text" name="query" placeholder="Search" class="border rounded-md px-3 py-2 w-full text-black">
                        <button type="submit" class="ml-2 text-white">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="white">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                        </button>
                    </form> 
                    <a href="Men.php" class="block text-gray-300 hover:text-white py-3 px-4 border-b border-gray-800">Men</a>
                    <a href="Women.php" class="block text-gray-300 hover:text-white py-3 px-4 border-b border-gray-800">Women</a>

                    
                    <div class="flex justify-between items-center px-4 pt-2">
                    <?php if (isset($_SESSION['user_id'])): ?>
    <!-- If user is logged in, show logout -->
    <a href="logout.php" class="text-gray-300 hover:text-white flex items-center">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7" />
        </svg>
        Logout
    </a>
<?php else: ?>
    <!-- If not logged in, show login -->
    <a href="login.php" class="text-gray-300 hover:text-white flex items-center">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
        </svg>
        Login
    </a>
<?php endif; ?>

                    </div>
                </div>
            </div>

            <!-- Desktop Navigation (hidden on mobile) -->
            <div class="hidden md:flex items-center space-x-4">
                <a href="Men.php" class="text-gray-300 hover:text-white font-semibold">Men</a>
                <a href="Women.php" class="text-gray-300 hover:text-white font-semibold">Women</a>
                <a href="index.php" class="text-gray-300 hover:text-white font-semibold">Home</a>

             <form action="search.php" method="GET" class="flex items-center">
                    <input type="text" name="query" placeholder="Search" class="border rounded-md px-3 py-2 w-48 text-black">
                    <button type="submit" class="ml-2 text-white">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="white">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </button>
                </form> 
                <a href="login.php" class="text-gray-300 hover:text-white">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                    </svg>
                </a>
            </div>

           <!-- Shopping Cart (visible on all screen sizes) -->
<div class="relative" x-data="{ cartOpen: false }">
    <button 
        @click="cartOpen = !cartOpen" 
        class="text-gray-300 hover:text-white relative"
    >
        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="-1 -1 22 22" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
        </svg>
        <?php if(!empty($_SESSION["shopping_cart"])): ?>
            <span class="absolute -top-2 -right-2 bg-white text-black rounded-full px-2 py-0.5 text-xs font-bold">
                <?php echo count($_SESSION["shopping_cart"]); ?>
            </span>
        <?php endif; ?>
    </button>

    <!-- Cart Dropdown - Now right-aligned -->
    <div 
        x-show="cartOpen" 
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0 transform scale-90"
        x-transition:enter-end="opacity-100 transform scale-100"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100 transform scale-100"
        x-transition:leave-end="opacity-0 transform scale-90"
        @click.away="cartOpen = false"
        class="absolute right-0 mt-2 w-64 sm:w-80 bg-white rounded-lg shadow-xl z-50 max-h-96 overflow-y-auto text-black"
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
                <div class="flex items-center mb-4 pb-4 border-b border-gray-200">
                    <img src="<?php echo $item['product_image']; ?>" class="w-12 h-12 sm:w-16 sm:h-16 object-cover mr-2 sm:mr-4">
                    <div class="flex-grow">
                        <h4 class="font-medium text-sm sm:text-base"><?php echo $item['product_name']; ?></h4>
                        <p class="text-xs sm:text-sm text-gray-600">
                            <?php echo $item['product_color']; ?> | 
                            <?php echo $item['product_size']; ?> | 
                            Qty: <?php echo $item['product_quantity']; ?>
                        </p>
                        <p class="text-xs sm:text-sm font-semibold">
                            $<?php echo number_format($item['product_price'] * $item['product_quantity'], 2); ?>
                        </p>
                    </div>
                    <a href="<?php 
                        echo isset($item['collection']) && $item['collection'] === 'men' 
                            ? 'Men.php' 
                            : 'Women.php'; 
                    ?>?action=delete&id=<?php echo $item['product_id']; ?>" class="text-black hover:text-gray-700">
                        <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
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
                    <a href="cart.php" class="mt-4 block w-full bg-black text-white py-2 text-center rounded-lg hover:bg-gray-800 transition">
                        Checkout
                    </a>
                </div>
            <?php else: ?>
                <p class="text-center text-gray-500">Your cart is empty</p>
            <?php endif; ?>
        </div>
    </div>
</div>
</nav>
<?php
include 'header.php';
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

$db_name = "clothingshop";
$connection = mysqli_connect("localhost", "root", "", $db_name);

// Include cart functions
include_once 'cart_function.php';

// Handle add to cart
if(isset($_POST["add"])){
    $newItem = [
        'collection' => 'men',
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
if (isset($_GET["action"]) && $_GET["action"] == "delete") {
    removeFromCart('men', $_GET["id"]);
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
    <!-- Navigation Cart Section -->
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
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 transform scale-90"
            x-transition:enter-end="opacity-100 transform scale-100"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100 transform scale-100"
            x-transition:leave-end="opacity-0 transform scale-90"
            @click.away="cartOpen = false"
            class="absolute right-0 mt-2 w-80 bg-white rounded-lg shadow-xl z-20 max-h-96 overflow-y-auto"
        >
            <!-- Cart content - same as original -->
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
                        <img src="uploads/<?php echo $item['product_image']; ?>" class="w-16 h-16 object-cover mr-4">
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

    <!-- Product Grid -->
    <div class="container mx-auto px-4 py-8">
        <h1 class="text-3xl font-bold mb-8 text-center">Men's Collection</h1>
        
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php
            // Updated query to fetch from products table
            $query = "SELECT * FROM products WHERE category='Men' ORDER BY id DESC";
            $result = mysqli_query($connection, $query);
            
            if(mysqli_num_rows($result) > 0) {
                while($row = mysqli_fetch_array($result)) {
                    // Get available sizes from JSON
                    $available_sizes = json_decode($row["available_sizes"], true);
                    if(!is_array($available_sizes)) {
                        $available_sizes = ["S", "M", "L", "XL", "XXL"]; // Default if no sizes stored
                    }
                    
                   
                   // After fetching the row from the database
// After fetching the row from the database
$gallery_images = [];

// Get the main image first
$main_image = $row["image"];

// Then get any gallery images
if(isset($row["gallery"]) && !empty($row["gallery"])) {
    $gallery_json = $row["gallery"];
    $decoded_gallery = json_decode($gallery_json, true);
    
    // Make sure it's an array
    if(is_array($decoded_gallery)) {
        $gallery_images = $decoded_gallery;
    }
}

// If there are no gallery images or decoding failed, at least use the main image
if(empty($gallery_images)) {
    $gallery_images = [$main_image];
} else {
    // Ensure the main image is included (at the beginning)
    array_unshift($gallery_images, $main_image);
    // Remove any potential duplicates
    $gallery_images = array_unique($gallery_images);
}
            ?>
                <div class="bg-white rounded-lg shadow-md overflow-hidden">
                    <!-- Define the Alpine component with a unique ID -->
                    <div 
    x-data="{
        currentIndex: 0,
        productImages: [
            <?php 
            // Create a proper JavaScript array of image paths
            $js_array = [];
            foreach($gallery_images as $img) {
                $js_array[] = "'uploads/" . addslashes($img) . "'";
            }
            echo implode(", ", $js_array);
            ?>
        ],
        
        prev() {
            this.currentIndex = this.currentIndex > 0 ? this.currentIndex - 1 : this.productImages.length - 1;
        },
        
        next() {
            this.currentIndex = this.currentIndex < this.productImages.length - 1 ? this.currentIndex + 1 : 0;
        },
        
        goToSlide(slideIndex) {
            this.currentIndex = slideIndex;
        }
    }"
    class="flex flex-col"
>
                        <!-- Main Product Image with Navigation Arrows -->
                        <div class="relative">
                            <!-- Previous Image Arrow -->
                            <button 
                                @click="prev()" 
                                class="absolute left-2 top-1/2 transform -translate-y-1/2 bg-white bg-opacity-70 rounded-full p-1 shadow-md z-10"
                                aria-label="Previous image"
                            >
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                                </svg>
                            </button>
                            
                         <!-- Image Container -->
                        <div class="overflow-hidden relative" style="height: 300px;">
                            <template x-for="(image, index) in productImages" :key="index">
                                <img
                                    :src="image"
                                    :alt="'<?php echo htmlspecialchars($row["name"]); ?> - Image ' + (index + 1)"
                                    class="w-full h-full object-cover transition-all duration-300 absolute top-0 left-0"
                                    :class="currentIndex === index ? 'opacity-100 z-10' : 'opacity-0 z-0'"
                                >
                            </template>
                        </div>
                            
                            <!-- Next Image Arrow -->
                            <button 
                                @click="next()" 
                                class="absolute right-2 top-1/2 transform -translate-y-1/2 bg-white bg-opacity-70 rounded-full p-1 shadow-md z-10"
                                aria-label="Next image"
                            >
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                </svg>
                            </button>
                        </div>
                        
                        <!-- Product Thumbnails -->
                      <!-- Product Thumbnails -->
<div class="flex px-2 py-2 overflow-x-auto space-x-2">
    <template x-for="(image, index) in productImages" :key="index">
        <div 
            @click="goToSlide(index)" 
            class="flex-shrink-0 w-12 border cursor-pointer transition-all duration-300"
            :class="currentIndex === index ? 'border-black' : 'border-gray-200 hover:border-gray-400'"
        >
            <img :src="image" alt="Thumbnail" class="w-full">
        </div>
    </template>
</div>
                        
                        <!-- Dot indicators (optional) -->
                        <div class="flex justify-center my-2">
                            <?php foreach($gallery_images as $dotIndex => $dot): ?>
                            <button 
                                @click="goToSlide(<?php echo $dotIndex; ?>)" 
                                class="w-2 h-2 mx-1 rounded-full transition-colors duration-200"
                                :class="currentIndex === <?php echo $dotIndex; ?> ? 'bg-black' : 'bg-gray-300'"
                            ></button>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <div class="p-4">
                        <h3 class="text-lg font-semibold mb-2"><?php echo $row["name"]; ?></h3>
                        <p class="text-gray-600 mb-2">$<?php echo number_format($row["price"], 2); ?></p>
                        <form method="post" action="Men.php?action=add&id=<?php echo $row["id"]; ?>">
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
                                    <?php foreach($available_sizes as $size): ?>
                                        <option><?php echo $size; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <input type="hidden" name="hidden_name" value="<?php echo $row["name"]; ?>">
                            <input type="hidden" name="hidden_price" value="<?php echo $row["price"]; ?>">
                            <input type="hidden" name="hidden_image" value="<?php echo $row["image"]; ?>">
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
            } else {
                echo '<div class="col-span-full text-center py-16 text-gray-500">No men\'s products available at the moment.</div>';
            }
            ?>
        </div>
    </div>
</body>
</html>
<?php include 'footer.php'; ?>
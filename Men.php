<?php
// Start session at the very beginning
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Include cart functions
include_once 'cart_functions.php';

// Database connection
$db_name = "clothingshop";
$connection = mysqli_connect("localhost", "root", "", $db_name);

// Handle add to cart BEFORE any output
if(isset($_POST["add"])){
    $newItem = [
        'collection' => 'men',
        'product_id' => $_POST["product_id"],
        'product_name' => $_POST["hidden_name"],
        'product_price' => $_POST["hidden_price"],
        'product_quantity' => $_POST["quantity"],
        'product_size' => $_POST["Size"],
        'product_image' => "uploads/" . $_POST["hidden_image"],
        'product_color' => isset($_POST["color"]) ? $_POST["color"] : "Default"
    ];
    addToCart($newItem);
    
    // Redirect to prevent form resubmission
    header("Location: Men.php");
    exit();
}

// Handle remove from cart BEFORE any output
if (isset($_GET["action"]) && $_GET["action"] == "delete") {
    removeFromCart('men', $_GET["id"]);
    // Redirect to prevent pagination issues
    header("Location: Men.php");
    exit();
}

// NOW include the header (which outputs HTML)
include 'header.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Men's Collection - masixSL</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
</head>
<body class="bg-gray-50">
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
                        <form method="post" action="Men.php">
                            <div class="mb-4">
                                <h4 class="text-sm font-semibold mb-2">SIZE</h4>
                                <div class="grid grid-cols-4 gap-2" x-data="{ selectedSize: '' }">
                                    <?php foreach($available_sizes as $size): ?>
                                    <div 
                                        @click="selectedSize = '<?php echo $size; ?>'" 
                                        :class="selectedSize === '<?php echo $size; ?>' ? 'border-black bg-gray-100' : 'border-gray-300 hover:border-black'"
                                        class="border text-center py-2 cursor-pointer text-sm transition-colors"
                                    >
                                        <?php echo $size; ?>
                                    </div>
                                    <?php endforeach; ?>
                                    <input type="hidden" name="Size" x-model="selectedSize" required>
                                </div>
                            </div>
                            <input type="hidden" name="product_id" value="<?php echo $row["id"]; ?>">
                            <input type="hidden" name="hidden_name" value="<?php echo $row["name"]; ?>">
                            <input type="hidden" name="hidden_price" value="<?php echo $row["price"]; ?>">
                            <input type="hidden" name="hidden_image" value="<?php echo $row["image"]; ?>">
                            <div class="space-y-3">
                                <div class="flex items-center mb-3">
                                    <label for="quantity" class="text-sm font-semibold mr-3">QUANTITY:</label>
                                    <input type="number" id="quantity" name="quantity" value="1" min="1" class="w-16 border rounded px-2 py-1 text-sm">
                                </div>
                                <button type="submit" name="add" class="w-full bg-black text-white py-3 font-medium hover:bg-gray-800 transition">
                                    ADD TO CART
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
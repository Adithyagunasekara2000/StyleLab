<?php include 'header.php'; 

$featuredProducts = [
    [
        'name' => 'WHITE/NAVY T', 
        'price' => 90.00, 
        'image' => 'images_men/1.jpg',
        'gallery' => [
            'images_men/1.jpg',
            'images_men/2.jpg',
            'images_men/3.jpg',
            'images_men/4.jpg'
        ],
        'link' => 'product.php?id=1'
    ],
    [
        'name' => 'CLASSIC DENIM JACKET - BLUE', 
        'price' => 75.00, 
        'image' => 'images_men/7.jpg',
        'gallery' => [
            'images_men/1.jpg',
            'images_men/2.jpg',
            'images_men/3.jpg',
            'images_men/4.jpg'
        ],
        'link' => 'product.php?id=2'
    ],
];

$collections = [
    ['name' => 'Men', 'image_url' => 'images_home/men.jpg', 'link' => 'men.php'],
    ['name' => 'Women', 'image_url' => 'images_home/women.jpeg', 'link' => 'women.php'],
]; 
?>

<!-- Featured Product Showcase (replacing hero section) -->
<section class="py-8 md:py-12 bg-white">
    <div class="max-w-7xl mx-auto px-4">
        <h2 class="text-2xl md:text-3xl font-bold mb-8 text-center">Featured Products</h2>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
        <?php foreach($featuredProducts as $index => $product): ?>
<div class="bg-white rounded-lg overflow-hidden shadow-md">
    <!-- Define the Alpine component with a unique ID -->
    <div 
        x-data="{
            currentIndex: 0,
            productImages: [
                '<?php echo implode("', '", $product['gallery']); ?>'
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
        class="flex flex-col md:flex-row"
    >
        <!-- Product Thumbnails (visible on desktop) -->
        <div class="hidden md:flex flex-col w-24 p-2 border-r border-gray-200">
            <?php foreach($product['gallery'] as $thumbIndex => $thumb): ?>
            <div 
                @click="goToSlide(<?php echo $thumbIndex; ?>)" 
                class="mb-3 border cursor-pointer transition-all duration-300"
                :class="currentIndex === <?php echo $thumbIndex; ?> ? 'border-black' : 'border-gray-200 hover:border-gray-400'"
            >
                <img src="<?php echo htmlspecialchars($thumb); ?>" alt="Thumbnail" class="w-full">
            </div>
            <?php endforeach; ?>
        </div>
        
        <!-- Main Product Image with Navigation Arrows -->
        <div class="md:flex-1 relative">
            <!-- Previous Image Arrow -->
            <button 
                @click="prev()" 
                class="absolute left-2 top-1/2 transform -translate-y-1/2 bg-white bg-opacity-70 rounded-full p-2 shadow-md z-10"
                aria-label="Previous image"
            >
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
            </button>
            
            <!-- Image Container -->
            <div class="overflow-hidden relative" style="height: 400px;">
                <?php foreach($product['gallery'] as $imgIndex => $img): ?>
                <img
                    src="<?php echo htmlspecialchars($img); ?>"
                    alt="<?php echo htmlspecialchars($product['name']); ?> - Image <?php echo $imgIndex + 1; ?>"
                    class="w-full h-full object-cover transition-all duration-300 absolute top-0 left-0"
                    :class="currentIndex === <?php echo $imgIndex; ?> ? 'opacity-100 z-10' : 'opacity-0 z-0'"
                >
                <?php endforeach; ?>
            </div>
            
            <!-- Next Image Arrow -->
            <button 
                @click="next()" 
                class="absolute right-2 top-1/2 transform -translate-y-1/2 bg-white bg-opacity-70 rounded-full p-2 shadow-md z-10"
                aria-label="Next image"
            >
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                </svg>
            </button>
        </div>
        
        <!-- Product Details -->
        <div class="p-6 md:w-1/3">
            <h3 class="text-xl md:text-2xl font-bold mb-2"><?= htmlspecialchars($product['name']) ?></h3>
            <p class="text-lg font-semibold mb-4">$<?= number_format($product['price'], 2) ?></p>
            <p class="text-sm text-gray-600 mb-4">Shipping calculated at checkout.</p>
            
            <!-- Size Selection -->
            <div class="mb-4">
                <h4 class="text-sm font-semibold mb-2">SIZE</h4>
                <div class="grid grid-cols-4 gap-2">
                    <?php 
                    $sizes = ['XS', 'S', 'M', 'L', 'XL', '2XL'];
                    foreach($sizes as $size): 
                    ?>
                    <div class="border border-gray-300 hover:border-black text-center py-2 cursor-pointer text-sm">
                        <?= $size ?>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            
            <!-- Action Buttons -->
            <div class="space-y-3">
                <a href="<?= htmlspecialchars($product['link']) ?>" class="block w-full bg-white border border-black text-black text-center py-3 font-medium hover:bg-gray-100">
                    VIEW DETAILS
                </a>
                <button class="w-full bg-black text-white py-3 font-medium hover:bg-gray-800">
                    ADD TO CART
                </button>
            </div>
        </div>
    </div>
    
    <!-- Product Thumbnails (visible on mobile) -->
    <div class="flex md:hidden px-4 py-2 overflow-x-auto space-x-2">
        <?php foreach($product['gallery'] as $thumbIndex => $thumb): ?>
        <div 
            @click="goToSlide(<?php echo $thumbIndex; ?>)" 
            class="flex-shrink-0 w-16 border cursor-pointer"
            :class="currentIndex === <?php echo $thumbIndex; ?> ? 'border-black' : 'border-gray-200 hover:border-gray-400'"
        >
            <img src="<?php echo htmlspecialchars($thumb); ?>" alt="Thumbnail" class="w-full">
        </div>
        <?php endforeach; ?>
    </div>
    
   
    <div class="md:hidden flex justify-center my-2">
        <?php foreach($product['gallery'] as $dotIndex => $dot): ?>
        <button 
            @click="goToSlide(<?php echo $dotIndex; ?>)" 
            class="w-2 h-2 mx-1 rounded-full transition-colors duration-200"
            :class="currentIndex === <?php echo $dotIndex; ?> ? 'bg-black' : 'bg-gray-300'"
        ></button>
        <?php endforeach; ?>
    </div>
</div>
<?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Collections -->
<section class="py-10 sm:py-12 md:py-16 max-w-7xl mx-auto px-4 bg-white text-black">
    <h2 class="text-2xl sm:text-3xl font-bold text-center mb-8 sm:mb-12">Our Collections</h2>
    
    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6 md:gap-8">
        <?php foreach($collections as $collection): ?>
            <div style="background-color:black;" class="rounded-lg overflow-hidden shadow-lg hover:shadow-xl transition collection-hover">
                <div class="aspect-w-1 aspect-h-1 w-full">
                    <img src="<?= htmlspecialchars($collection['image_url']) ?>" 
                         alt="<?= htmlspecialchars($collection['name']) ?>" 
                         class="w-full h-full object-cover">
                </div>
                <div class="p-4 text-center">
                    <a href="<?= htmlspecialchars($collection['link']) ?>" class="text-white hover:text-yellow-300 transition">
                        <h3 class="font-semibold text-lg sm:text-xl mb-2"><?= htmlspecialchars($collection['name']) ?></h3>
                    </a>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</section>

<!-- Trending Products -->
<section class="py-10 sm:py-16 bg-gray-50">
    <div class="max-w-7xl mx-auto px-4">
        <h2 class="text-2xl sm:text-3xl font-bold text-center mb-8">Trending Now</h2>
        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-4 sm:gap-6">
            <?php
            // Sample trending products - replace with actual data
            $trendingProducts = [
                ['name' => 'Graphic T-Shirt', 'price' => 29.99, 'image' => 'images_men/1.jpg'],
                ['name' => 'Slim Fit Jeans', 'price' => 59.99, 'image' => 'images_men/2.jpg'],
                ['name' => 'Hoodie', 'price' => 49.99, 'image' => 'images_men/3.jpg'],
                ['name' => 'Sneakers', 'price' => 79.99, 'image' => 'images_men/4.jpg'],
            ];
            
            // Comment out this loop if you don't have the images yet
            
            foreach($trendingProducts as $product): 
            ?>
                <div class="bg-white rounded-lg overflow-hidden shadow-sm hover:shadow-md transition">
                    <a href="product.php">
                        <img src="<?= htmlspecialchars($product['image']) ?>" 
                             alt="<?= htmlspecialchars($product['name']) ?>" 
                             class="w-full h-52 sm:h-64 object-cover">
                        <div class="p-3 sm:p-4">
                            <h3 class="font-medium text-xs sm:text-sm truncate"><?= htmlspecialchars($product['name']) ?></h3>
                            <p class="font-semibold text-sm sm:text-base mt-1">$<?= number_format($product['price'], 2) ?></p>
                        </div>
                    </a>
                </div>
            <?php endforeach;
            
            ?>
        </div>
    </div>
</section>

<?php include 'footer.php'; ?>
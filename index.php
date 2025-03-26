<?php
include 'header.php';


$collections = [
    [
        'name' => 'Men',
        'image_url' => 'images_home/men.jpg',
        'link' => 'men.php'
    ],
    [
        'name' => 'Women',
        'image_url' => 'images_home/women.jpeg',
        'link' => 'women.php'
    ],
    [
        'name' => 'Kids',
        'image_url' => 'images_home/kids.jpg',
        'link' => 'kids.php'
    ],
    [
        'name' => 'Bags',
        'image_url' => 'images_home/bags.jpg',
        'link' => 'bags.php'
    ]
];

// Hardcoded seasonal offers based on the original HTML file

function getSeasonalOffers() {
    // Get current month and date
    $currentMonth = date('n');
    $currentDay = date('j');
    
    // Array of seasonal collections with their specific time periods
    $seasonalCollections = [
        // Sinhala and Tamil New Year (Avurudu)
        'avurudu' => [
            'name' => 'Avurudu Collection',
            'description' => 'Traditional and modern festive wear for Sinhala and Tamil New Year',
            'image_url' => 'images_season/new_year.jpg',
            'link' => 'avurudu-collection.php',
            'start_month' => 3, // April
            'start_day' => 1,
            'end_month' => 4,
            'end_day' => 15
        ],
        
        // Vesak Festival (Buddhist celebration)
        'vesak' => [
            'name' => 'Vesak Festive Wear',
            'description' => 'Elegant and serene clothing for the sacred Vesak celebration',
            'image_url' => 'images_home/vesak-festival.jpg',
            'link' => 'vesak-collection.php',
            'start_month' => 5, // May
            'start_day' => 1,
            'end_month' => 5,
            'end_day' => 31
        ],
        
        
        // Deepavali (Festival of Lights)
        'deepavali' => [
            'name' => 'Deepavali Celebration',
            'description' => 'Vibrant and festive wear for the Festival of Lights',
            'image_url' => 'images_home/deepavali-collection.jpg',
            'link' => 'deepavali-collection.php',
            'start_month' => 11, // November
            'start_day' => 1,
            'end_month' => 11,
            'end_day' => 30
        ],
        
        // Christmas Collection
        'christmas' => [
            'name' => 'Christmas Collection',
            'description' => 'Festive and elegant wear for the holiday season',
            'image_url' => 'images_home/christmas-collection.jpg',
            'link' => 'christmas-collection.php',
            'start_month' => 12, // December
            'start_day' => 1,
            'end_month' => 12,
            'end_day' => 31
        ]
    ];
    
    
    function isInDateRange($collection) {
        $currentMonth = date('n');
        $currentDay = date('j');
        
        
        if ($collection['start_month'] > $collection['end_month']) {
            return ($currentMonth >= $collection['start_month'] || $currentMonth <= $collection['end_month']) &&
                   (($currentMonth == $collection['start_month'] && $currentDay >= $collection['start_day']) ||
                    ($currentMonth == $collection['end_month'] && $currentDay <= $collection['end_day']) ||
                    ($currentMonth > $collection['start_month'] || $currentMonth < $collection['end_month']));
        }
        
        return ($currentMonth == $collection['start_month'] && $currentDay >= $collection['start_day']) ||
               ($currentMonth == $collection['end_month'] && $currentDay <= $collection['end_day']) ||
               ($currentMonth > $collection['start_month'] && $currentMonth < $collection['end_month']);
    }
    
    $activeCollections = array_filter($seasonalCollections, 'isInDateRange');
    

    if (empty($activeCollections)) {
        return [
            [
                'name' => 'Classic Collection',
                'description' => 'Timeless styles for every season',
                'image_url' => 'images_home/classic-collection.jpg',
                'link' => 'classic-collection.php'
            ]
        ];
    }
    
    return array_values($activeCollections);
}

//Get upcoming collections
function getUpcomingCollections() {
    $currentMonth = date('n');
    $seasonalCollections = [
    ];
    
    function isUpcomingCollection($collection) {
        $currentMonth = date('n');
        
        
        if ($collection['start_month'] > $collection['end_month']) {
            return $currentMonth < $collection['start_month'] && $currentMonth > $collection['end_month'];
        }
        
        
        return $currentMonth < $collection['start_month'];
    }
    
    $upcomingCollections = array_filter($seasonalCollections, 'isUpcomingCollection');
    return array_values($upcomingCollections);
}

?>

<!-- Hero Section -->
<header class="hero-gradient py-20 text-center">
    <div class="max-w-4xl mx-auto px-4">
        <h1 class="text-4xl md:text-6xl font-bold text-white mb-6">Redefine Your Style</h1>
        <p class="text-xl text-white mb-8">Discover timeless fashion that tells your unique story</p>
        <a href="shop.php" class="bg-white text-gray-900 px-8 py-3 rounded-full font-semibold hover:bg-gray-100 transition">Shop Now</a>
    </div>
</header>

<!-- Collections -->
<section class="py-16 max-w-7xl mx-auto px-4">
    <h2 class="text-3xl font-bold text-center mb-12">Our Collections</h2>
    <div class="grid md:grid-cols-4 gap-8">
        <?php foreach($collections as $collection): ?>
        <div class="bg-white rounded-lg overflow-hidden shadow-lg collection-hover">
            <img src="<?= htmlspecialchars($collection['image_url']) ?>" alt="<?= htmlspecialchars($collection['name']) ?>" class="w-full">
            <div class="p-4 text-center">
                <h3 class="font-semibold text-xl mb-2"><?= htmlspecialchars($collection['name']) ?></h3>
                <a href="<?= htmlspecialchars($collection['link']) ?>" class="text-gray-600 hover:text-gray-900">Explore Now</a>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</section>

<!-- Seasonal Offers -->
<section class="bg-gray-100 py-16">
    <div class="max-w-7xl mx-auto px-4">
        <h2 class="text-3xl font-bold text-center mb-12">Seasonal Offers</h2>
        <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-8">
            <?php 
            $seasonalOffers = getSeasonalOffers(); 
            foreach($seasonalOffers as $offer): 
            ?>
             <div class="bg-white p-6 rounded-lg text-center shadow-md">
                <img src="<?= htmlspecialchars($offer['image_url']) ?>" alt="<?= htmlspecialchars($offer['name']) ?>" class="mx-auto mb-4">
                <h3 class="font-semibold text-xl mb-2"><?= htmlspecialchars($offer['name']) ?></h3>
                <p class="text-gray-600 mb-4"><?= htmlspecialchars($offer['description']) ?></p>
                <a href="<?= htmlspecialchars($offer['link']) ?>" class="text-blue-600 hover:underline">Shop <?= htmlspecialchars($offer['name']) ?></a>
            </div>
			 
			 <div class="bg-white p-8 rounded-lg shadow-md">
                <h3 class="text-2xl font-bold mb-4">Spring into Style: Unbeatable Deals Await!</h3>
                <div class="text-gray-700 space-y-4">
                    <p>🌟 Limited Time Offer: Get up to 50% OFF on our latest collections!</p>
                    <p>Refresh your wardrobe without breaking the bank. Our seasonal sale brings you the hottest trends at incredible prices.</p>
                    <ul class="list-disc list-inside text-gray-600">
                        <li>Free shipping on orders over $100</li>
                        <li>Extra 10% off for newsletter subscribers</li>
                        <li>Mix and match across all collections</li>
                    </ul>
                    <p class="font-semibold text-blue-600">Hurry! Offer ends soon.</p>
                    <a href="sale.php" class="inline-block bg-blue-500 text-white px-6 py-2 rounded-full hover:bg-blue-600 transition">Shop Sale Now</a>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<?php include 'footer.php'; ?>
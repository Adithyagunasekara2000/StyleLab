<?php
session_start();
require_once '../config.php';

// Check if user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

// Initialize variables
$errorMessage = '';
$successMessage = '';
$products = [];
$categories = ["Men", "Women", "Kids", "Accessories"];
$sizes = ["XS", "S", "M", "L", "XL", "2XL"];

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $conn = connectDatabase();
    
    // Handle product deletion
    if (isset($_POST['delete_product'])) {
        $productId = $_POST['product_id'];
        
        // Get image filenames before deletion
        $stmt = $conn->prepare("SELECT image, gallery FROM products WHERE id = ?");
        $stmt->bind_param("i", $productId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($row = $result->fetch_assoc()) {
            // Delete main image
            if (file_exists("../uploads/" . $row['image'])) {
                unlink("../uploads/" . $row['image']);
            }
            
            // Delete gallery images
            $gallery = json_decode($row['gallery'], true);
            if (is_array($gallery)) {
                foreach ($gallery as $img) {
                    if (file_exists("../uploads/" . $img)) {
                        unlink("../uploads/" . $img);
                    }
                }
            }
        }
        
        // Delete product from database
        $stmt = $conn->prepare("DELETE FROM products WHERE id = ?");
        $stmt->bind_param("i", $productId);
        
        if ($stmt->execute()) {
            $successMessage = "Product deleted successfully.";
        } else {
            $errorMessage = "Error deleting product: " . $conn->error;
            error_log("SQL Error: " . $conn->error);
        }
    }
    
    // Handle product addition
    elseif (isset($_POST['add_product'])) {
        $name = $_POST['name'];
        $price = $_POST['price'];
        $description = $_POST['description'];
        $category = $_POST['category'];
        $stock_quantity = $_POST['stock_quantity'];
        $available_sizes = isset($_POST['sizes']) ? json_encode($_POST['sizes']) : json_encode([]);
        
        // Upload main product image
        $mainImage = '';
        if (isset($_FILES['main_image']) && $_FILES['main_image']['error'] == 0) {
            $mainImage = uploadSingleImage($_FILES['main_image']);
            if ($mainImage === false) {
                $uploadError = error_get_last();
                $errorMessage = "Error uploading main image. Check file type and size. ";
                if ($uploadError) {
                    $errorMessage .= "System error: " . $uploadError['message'];
                }
                error_log("Image upload failed: " . print_r($_FILES['main_image'], true));
            }
        } else {
            $errorCode = isset($_FILES['main_image']['error']) ? $_FILES['main_image']['error'] : 'unknown';
            $errorMessage = "Main product image upload failed. Error code: " . $errorCode;
            error_log("Image upload error code: " . $errorCode);
        }
        
        // Upload gallery images - FIXED SECTION
        $gallery = [];
        if (isset($_FILES['gallery_images']) && !empty($_FILES['gallery_images']['name'][0])) {
            $gallery = uploadMultipleImages($_FILES['gallery_images']);
        }
        
        $galleryJson = json_encode($gallery);
        
        // If no errors, insert into database
        if (empty($errorMessage) && $mainImage !== false) {
            $stmt = $conn->prepare("INSERT INTO products (name, price, description, category, image, gallery, stock_quantity, available_sizes) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("sdssssss", $name, $price, $description, $category, $mainImage, $galleryJson, $stock_quantity, $available_sizes);
            
            if ($stmt->execute()) {
                $successMessage = "Product added successfully.";
            } else {
                $errorMessage = "Error adding product: " . $conn->error;
            }
        }
    }
    
    $conn->close();
}

// Function to handle single image upload
function uploadSingleImage($file) {
    $targetDir = "../uploads/";
    
    // Create directory if it doesn't exist
    if (!file_exists($targetDir)) {
        if (!mkdir($targetDir, 0777, true)) {
            error_log("Failed to create directory: " . $targetDir);
            return false;
        }
    }
    
    // Check if directory is writable
    if (!is_writable($targetDir)) {
        error_log("Upload directory is not writable: " . $targetDir);
        chmod($targetDir, 0777);
        if (!is_writable($targetDir)) {
            return false;
        }
    }
    
    // Generate unique filename
    $fileName = uniqid() . "_" . basename($file['name']);
    $targetFilePath = $targetDir . $fileName;
    
    // Check file type
    $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/webp'];
    if (!in_array($file['type'], $allowedTypes)) {
        error_log("Invalid file type: " . $file['type']);
        return false;
    }
    
    // Check file size (max 5MB)
    if ($file['size'] > 5000000) {
        error_log("File too large: " . $file['size'] . " bytes");
        return false;
    }
    
    // Debug info
    error_log("Attempting to upload file: " . $file['name'] . " to " . $targetFilePath);
    
    if (!move_uploaded_file($file['tmp_name'], $targetFilePath)) {
        error_log("Failed to move uploaded file: " . $file['name'] . " - PHP Error: " . error_get_last()['message']);
        return false;
    }
    
    return $fileName;
}

// NEW FUNCTION: Function to handle multiple image uploads
function uploadMultipleImages($files) {
    $uploadedFiles = [];
    $fileCount = count($files['name']);
    
    for ($i = 0; $i < $fileCount; $i++) {
        // Skip if there was an error or no file was uploaded
        if ($files['error'][$i] !== 0 || empty($files['name'][$i])) {
            continue;
        }
        
        $targetDir = "../uploads/";
        
        // Create directory if it doesn't exist
        if (!file_exists($targetDir)) {
            if (!mkdir($targetDir, 0777, true)) {
                error_log("Failed to create directory: " . $targetDir);
                continue;
            }
        }
        
        // Generate unique filename
        $fileName = uniqid() . "_" . basename($files['name'][$i]);
        $targetFilePath = $targetDir . $fileName;
        
        // Check file type
        $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/webp'];
        if (!in_array($files['type'][$i], $allowedTypes)) {
            error_log("Invalid file type: " . $files['type'][$i]);
            continue;
        }
        
        // Check file size (max 5MB)
        if ($files['size'][$i] > 5000000) {
            error_log("File too large: " . $files['size'][$i] . " bytes");
            continue;
        }
        
        // Debug info
        error_log("Attempting to upload gallery file: " . $files['name'][$i] . " to " . $targetFilePath);
        
        if (move_uploaded_file($files['tmp_name'][$i], $targetFilePath)) {
            $uploadedFiles[] = $fileName;
        } else {
            error_log("Failed to move uploaded gallery file: " . $files['name'][$i]);
        }
    }
    
    return $uploadedFiles;
}

function copyImagesToUploads() {
    // Define source and destination directories
    $sourceDir = '../images_men/';
    $destDir = '../uploads/';
    
    // Create uploads directory if it doesn't exist
    if (!file_exists($destDir)) {
        mkdir($destDir, 0777, true);
    }
    
    // Get all files from source directory
    $files = scandir($sourceDir);
    
    foreach ($files as $file) {
        // Skip . and .. directories
        if ($file === '.' || $file === '..') {
            continue;
        }
        
        // Copy the file
        $sourcePath = $sourceDir . $file;
        $destPath = $destDir . $file;
        
        if (file_exists($sourcePath) && is_file($sourcePath)) {
            copy($sourcePath, $destPath);
        }
    }
    
    echo "Images copied to uploads directory successfully!";
}

// Fetch all products
$conn = connectDatabase();
$result = $conn->query("SELECT * FROM products ORDER BY id DESC");

if ($result) {
    while ($row = $result->fetch_assoc()) {
        $products[] = $row;
    }
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Product Management - masixSL</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.10.3/dist/cdn.min.js" defer></script>
    <style>
        body { font-family: 'Inter', sans-serif; }
        .sidebar { transition: all 0.3s ease; }
    </style>
</head>
<body class="bg-gray-100 min-h-screen flex flex-col md:flex-row">
    <!-- Sidebar -->
    <aside class="bg-black text-white w-full md:w-64 flex-shrink-0 md:min-h-screen" x-data="{ isOpen: false }">
    <div class="p-4 flex justify-between items-center md:justify-center">
        <a href="AdminDashboard.php" class="flex items-center">
            <img src="../images_home/ch2.png" alt="masixSL" class="h-8 w-auto">
        </a>
        <button @click="isOpen = !isOpen" class="md:hidden text-white">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16m-7 6h7" />
            </svg>
        </button>
    </div>
    
    <!-- Fix the navigation section by adding md:block to ensure it's always visible on desktop -->
    <nav class="mt-6 md:block" :class="{'hidden': !isOpen}">
        <a href="AdminDashboard.php" class="flex items-center py-3 px-4 text-gray-300 hover:bg-gray-700 hover:text-white">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
            </svg>
            Dashboard
        </a>
            <a href="product.php" class="flex items-center py-3 px-4 bg-gray-800 text-white hover:bg-gray-700">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                </svg>
                Products
            </a>
            <a href="orders.php" class="flex items-center py-3 px-4 text-gray-300 hover:bg-gray-700 hover:text-white">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                </svg>
                Orders
            </a>
            <a href="users.php" class="flex items-center py-3 px-4 text-gray-300 hover:bg-gray-700 hover:text-white">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                </svg>
                Users
            </a>
            <a href="settings.php" class="flex items-center py-3 px-4 text-gray-300 hover:bg-gray-700 hover:text-white">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
                Settings
            </a>
            <div class="border-t border-gray-700 mt-6 pt-4 px-4">
                <a href="../logout.php" class="flex items-center py-2 text-gray-300 hover:text-white">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                    </svg>
                    Logout
                </a>
            </div>
        </nav>
    </aside>

    <!-- Main Content -->
    <main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-100">
        <!-- Top Bar -->
        <div class="bg-white shadow-md px-6 py-4">
            <div class="flex items-center justify-between">
                <h1 class="text-xl font-semibold">Product Management</h1>
                <div class="flex items-center">
                    <span class="mr-2 text-sm">Welcome, <?php echo htmlspecialchars($_SESSION['user_name']); ?></span>
                    <div class="bg-gray-200 rounded-full h-8 w-8 flex items-center justify-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        <!-- Content Area -->
        <div class="px-6 py-8">
            <?php if (!empty($errorMessage)): ?>
                <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6" role="alert">
                    <p><?php echo htmlspecialchars($errorMessage); ?></p>
                </div>
            <?php endif; ?>

            <?php if (!empty($successMessage)): ?>
                <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6" role="alert">
                    <p><?php echo htmlspecialchars($successMessage); ?></p>
                </div>
            <?php endif; ?>

            <!-- Tabs -->
            <div x-data="{ activeTab: 'products' }">
                <div class="flex border-b mb-6">
                    <button @click="activeTab = 'products'" :class="{ 'border-b-2 border-black font-semibold': activeTab === 'products' }" class="px-4 py-2 text-gray-600 hover:text-black">
                        View Products
                    </button>
                    <button @click="activeTab = 'add'" :class="{ 'border-b-2 border-black font-semibold': activeTab === 'add' }" class="px-4 py-2 text-gray-600 hover:text-black">
                        Add New Product
                    </button>
                </div>

                <!-- Products List Tab -->
                <div x-show="activeTab === 'products'">
                    <div class="flex justify-between mb-6">
                        <h2 class="text-xl font-semibold">All Products</h2>
                        <div class="flex">
                            <input type="text" placeholder="Search products..." class="border rounded-l px-4 py-2 focus:outline-none focus:ring-2 focus:ring-black">
                            <button class="bg-black text-white px-4 py-2 rounded-r hover:bg-gray-800">Search</button>
                        </div>
                    </div>

                    <div class="bg-white shadow-md rounded-lg overflow-hidden">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Image
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Name
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Price
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Category
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Stock
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Actions
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <?php if (empty($products)): ?>
                                    <tr>
                                        <td colspan="6" class="px-6 py-4 text-center text-gray-500">
                                            No products found. Add your first product.
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($products as $product): ?>
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="flex-shrink-0 h-16 w-16">
                                                <img class="h-30 w-30 object-cover" src="../uploads/<?php echo htmlspecialchars($product['image']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>">                                           
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($product['name']); ?></div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm text-gray-900">$<?php echo number_format($product['price'], 2); ?></div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">
                                                    <?php echo htmlspecialchars($product['category']); ?>
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm text-gray-900"><?php echo htmlspecialchars($product['stock_quantity']); ?></div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                                <div class="flex space-x-2">
                                                    <a href="edit_product.php?id=<?php echo $product['id']; ?>" class="text-indigo-600 hover:text-indigo-900">Edit</a>
                                                    <button 
                                                        onclick="document.getElementById('product-id-<?php echo $product['id']; ?>').submit();" 
                                                        class="text-red-600 hover:text-red-900">
                                                        Delete
                                                    </button>
                                                    <form id="product-id-<?php echo $product['id']; ?>" method="POST" style="display: none;">
                                                        <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                                                        <input type="hidden" name="delete_product" value="1">
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Add Product Tab -->
                <div x-show="activeTab === 'add'">
                    <h2 class="text-xl font-semibold mb-6">Add New Product</h2>
                    
                    <form method="POST" enctype="multipart/form-data" class="bg-white shadow-md rounded-lg p-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Product Name</label>
                                <input type="text" id="name" name="name" required class="w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-black focus:border-black">
                            </div>
                            
                            <div>
                                <label for="price" class="block text-sm font-medium text-gray-700 mb-1">Price ($)</label>
                                <input type="number" id="price" name="price" step="0.01" min="0" required class="w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-black focus:border-black">
                            </div>
                            
                            <div>
                                <label for="category" class="block text-sm font-medium text-gray-700 mb-1">Category</label>
                                <select id="category" name="category" required class="w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-black focus:border-black">
                                    <option value="">Select a category</option>
                                    <?php foreach ($categories as $category): ?>
                                        <option value="<?php echo htmlspecialchars($category); ?>"><?php echo htmlspecialchars($category); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <div>
                                <label for="stock_quantity" class="block text-sm font-medium text-gray-700 mb-1">Stock Quantity</label>
                                <input type="number" id="stock_quantity" name="stock_quantity" min="0" required class="w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-black focus:border-black">
                            </div>
                            
                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Available Sizes</label>
                                <div class="grid grid-cols-3 sm:grid-cols-6 gap-2">
                                    <?php foreach ($sizes as $size): ?>
                                        <label class="flex items-center space-x-2">
                                            <input type="checkbox" name="sizes[]" value="<?php echo htmlspecialchars($size); ?>" class="rounded border-gray-300 text-black focus:ring-black">
                                            <span><?php echo htmlspecialchars($size); ?></span>
                                        </label>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                            
                            <div class="md:col-span-2">
                                <label for="description" class="block text-sm font-medium text-gray-700 mb-1">Product Description</label>
                                <textarea id="description" name="description" rows="4" required class="w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-black focus:border-black"></textarea>
                            </div>
                            
                            <div>
                                <label for="main_image" class="block text-sm font-medium text-gray-700 mb-1">Main Product Image</label>
                                <input type="file" id="main_image" name="main_image" accept="image/jpeg,image/png,image/webp" required class="w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none">
                                <p class="mt-1 text-xs text-gray-500">Upload an image (JPG, PNG, WEBP). Max 5MB.</p>
                            </div>
                            
                            <div>
                            <label for="gallery_images" class="block text-sm font-medium text-gray-700 mb-1">Gallery Images</label>
                                <input type="file" id="gallery_images" name="gallery_images[]" accept="image/jpeg,image/png,image/webp" multiple class="w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none">
                                <p class="mt-1 text-xs text-gray-500">Upload up to 4 additional images. Max 5MB each.</p>
                            </div>
                        </div>
                        
                        <div class="mt-6">
                            <button type="submit" name="add_product" class="w-full bg-black text-white py-3 px-4 rounded-md hover:bg-gray-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-black">
                                Add Product
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </main>
</body>
</html>
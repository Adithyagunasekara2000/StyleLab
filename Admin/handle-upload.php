<?php
session_start();
require_once '../config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $conn = connectDatabase();
    
    $name = $conn->real_escape_string($_POST['name']);
    $description = $conn->real_escape_string($_POST['description']);
    $price = (float)$_POST['price'];
    $quantity = (int)$_POST['stock_quantity']; // Updated to match form field name
    $category = $conn->real_escape_string($_POST['category']);
    
    // Handle sizes if provided
    $sizes = [];
    if (isset($_POST['sizes']) && is_array($_POST['sizes'])) {
        $sizes = $_POST['sizes'];
    }
    $serialized_sizes = serialize($sizes);
    
    // Handle main image upload
    $mainImagePath = '';
    if (isset($_FILES['main_image']) && $_FILES['main_image']['error'] === 0) {
        $uploadDir = '../uploads/';
        $fileName = uniqid() . '_' . basename($_FILES['main_image']['name']);
        $targetFile = $uploadDir . $fileName;
        
        if (move_uploaded_file($_FILES['main_image']['tmp_name'], $targetFile)) {
            $mainImagePath = $fileName;
        }
    }
    
    // Handle gallery images upload
    $galleryImages = [];
    if (isset($_FILES['gallery_images'])) {
        $total_files = count($_FILES['gallery_images']['name']);
        $uploadDir = '../uploads/gallery/';
        
        // Create directory if it doesn't exist
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        
        for ($i = 0; $i < $total_files; $i++) {
            if ($_FILES['gallery_images']['error'][$i] === 0) {
                $fileName = uniqid() . '_' . basename($_FILES['gallery_images']['name'][$i]);
                $targetFile = $uploadDir . $fileName;
                
                if (move_uploaded_file($_FILES['gallery_images']['tmp_name'][$i], $targetFile)) {
                    $galleryImages[] = $fileName;
                }
            }
        }
    }
    $serialized_gallery = serialize($galleryImages);
    
    // Prepare SQL statement - assuming you want to update your database schema
    $stmt = $conn->prepare("INSERT INTO products (name, description, price, quantity, category, sizes, main_image, gallery_images) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssdissss", $name, $description, $price, $quantity, $category, $serialized_sizes, $mainImagePath, $serialized_gallery);
    
    if ($stmt->execute()) {
        header("Location: products.php?upload=success");
    } else {
        echo "Error uploading product: " . $stmt->error;
    }
    
    $stmt->close();
    $conn->close();
} else {
    echo "Invalid request method!";
}
?>
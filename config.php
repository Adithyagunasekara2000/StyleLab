<?php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'StyleLab');

// Enhanced Database Connection Function
function connectDatabase() {
    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    if (!$conn) {
        // Log the error
        error_log("Database Connection Error: " . mysqli_connect_error());
        die("Sorry, we're experiencing technical difficulties. Please try again later.");
    }
    return $conn;
}




?>
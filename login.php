<?php
session_start();
require_once 'config.php';

$error_message = "";

// Redirect if already logged in
if (isset($_SESSION['user_id'])) {
    // Redirect admins to dashboard, customers to home page
    if ($_SESSION['user_role'] === 'admin') {
        header("Location: Admin/AdminDashboard.php");
    } else {
        header("Location: index.php");
    }
    exit();
}

// Process login form submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['login'])) {
    // Get form data
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'];
    
    // Basic validation
    if (empty($email) || empty($password)) {
        $error_message = "Email and password are required.";
    } else {
        $conn = connectDatabase();
        
        // Prepare SQL statement to prevent SQL injection
        $stmt = $conn->prepare("SELECT id, name, email, password, role, is_verified FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            
            // Check if account is verified (optional based on your setup)
            if (!$user['is_verified']) {
                $error_message = "Please verify your email before logging in.";
            } elseif (password_verify($password, $user['password'])) {
                // Set session variables
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['name'];
                $_SESSION['user_role'] = $user['role'];

                // Redirect based on role
                if ($user['role'] === 'admin') {
                    header("Location: Admin/AdminDashboard.php");
                } else {
                    header("Location: index.php");
                }
                exit();
            } else {
                $error_message = "Invalid email or password.";
            }
        } else {
            $error_message = "Invalid email or password.";
        }

        $stmt->close();
        $conn->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - masixSL</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
        .form-control:focus {
            box-shadow: 0 0 0 3px rgba(0, 0, 0, 0.1);
        }
    </style>
</head>
<body class="bg-gray-50 min-h-screen flex items-center justify-center">
    <!-- Navigation included -->
    <!-- <?php include 'nav_include.php'; ?> -->

    <div class="container mx-auto p-4">
        <div class="max-w-md mx-auto bg-white rounded-lg shadow-md overflow-hidden">
            <div class="py-4 px-6 bg-black text-white text-center">
                <h2 class="text-xl font-bold">Sign In</h2>
            </div>
            
            <div class="p-6">
                <?php if (!empty($error_message)): ?>
                    <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4" role="alert">
                        <p><?php echo $error_message; ?></p>
                    </div>
                <?php endif; ?>
                
                <?php if (isset($_SESSION['registration_success'])): ?>
                    <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-4" role="alert">
                        <p>Registration successful! Please log in with your credentials.</p>
                    </div>
                    <?php unset($_SESSION['registration_success']); ?>
                <?php endif; ?>
                
                <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                    <div class="mb-4">
                        <label for="email" class="block text-gray-700 text-sm font-bold mb-2">Email Address</label>
                        <input type="email" id="email" name="email" class="form-control w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:border-black" required>
                    </div>
                    
                    <div class="mb-6">
                        <label for="password" class="block text-gray-700 text-sm font-bold mb-2">Password</label>
                        <input type="password" id="password" name="password" class="form-control w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:border-black" required>
                        <a href="forgot_password.php" class="block text-right text-sm text-black hover:underline mt-1">Forgot Password?</a>
                    </div>
                    
                    <div class="flex items-center justify-between mb-4">
                        <button type="submit" name="login" class="w-full bg-black hover:bg-gray-800 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline transition duration-150">
                            Sign In
                        </button>
                    </div>
                    
                    <p class="text-center text-gray-600 text-sm">
                        Don't have an account? <a href="register.php" class="text-black font-semibold hover:underline">Register here</a>
                    </p>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
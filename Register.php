<?php
session_start();
require_once 'config.php';

$error_message = "";
$success_message = "";

// Process registration form submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['register'])) {
    // Get form data and sanitize inputs
    $name = filter_input(INPUT_POST, 'name', FILTER_SANITIZE_SPECIAL_CHARS);
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    
    // Basic validation
    if (empty($name) || empty($email) || empty($password) || empty($confirm_password)) {
        $error_message = "All fields are required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_message = "Please enter a valid email address.";
    } elseif (strlen($password) < 8) {
        $error_message = "Password must be at least 8 characters long.";
    } elseif ($password !== $confirm_password) {
        $error_message = "Passwords do not match.";
    } else {
        // Check if email already exists
        $conn = connectDatabase();
        
        // Check for connection errors
        if ($conn->connect_error) {
            error_log("Connection failed: " . $conn->connect_error);
            $error_message = "Database connection error. Please try again later.";
        } else {
            $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
            
            if ($stmt === false) {
                // Log the error
                error_log("Prepare failed: " . $conn->error);
                $error_message = "Database error occurred. Please try again later.";
            } else {
                $stmt->bind_param("s", $email);
                $stmt->execute();
                $result = $stmt->get_result();
                
                if ($result->num_rows > 0) {
                    $error_message = "Email already exists. Please use a different email address.";
                } else {
                    // Hash the password
                    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                    
                    // Set user role (default: customer)
                    $role = 'customer';
                    
                    // Generate a verification token
                    $verification_token = bin2hex(random_bytes(32));
                    
                    // Insert new user into database
                    $insert_stmt = $conn->prepare("INSERT INTO users (name, email, password, role, verification_token, is_verified, created_at) VALUES (?, ?, ?, ?, ?, 0, NOW())");
                    
                    if ($insert_stmt === false) {
                        // Log the error
                        error_log("Prepare insert failed: " . $conn->error);
                        $error_message = "Database error occurred during registration. Please try again later.";
                    } else {
                        $insert_stmt->bind_param("sssss", $name, $email, $hashed_password, $role, $verification_token);
                        
                        if ($insert_stmt->execute()) {
                            // Registration successful
                            $success_message = "Registration successful! Please check your email to verify your account.";
                            
                            // In a production environment, you would send verification email here
                            // sendVerificationEmail($email, $verification_token);
                            
                            // For demo purposes, you might want to automatically verify the account
                            // or redirect to login page
                            $_SESSION['registration_success'] = true;
                            header("Location: login.php");
                            exit();
                        } else {
                            $error_message = "Registration failed. Please try again.";
                            // Log the error for debugging
                            error_log("Registration error: " . $insert_stmt->error);
                        }
                        
                        $insert_stmt->close();
                    }
                }
                
                $stmt->close();
            }
            
            $conn->close();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - masixSL</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    <style>
        body { font-family: 'Inter', sans-serif; }
        .form-control:focus {
            box-shadow: 0 0 0 3px rgba(0, 0, 0, 0.1);
        }
        .password-strength-meter {
            height: 4px;
            transition: all 0.3s;
        }
    </style>
</head>
<body class="bg-gray-50 min-h-screen flex items-center justify-center">
    <!-- Navigation included -->
    <!-- <?php include 'nav_include.php'; ?> -->

    <div class="container mx-auto p-4">
        <div class="max-w-md mx-auto bg-white rounded-lg shadow-md overflow-hidden">
            <div class="py-4 px-6 bg-black text-white text-center">
                <h2 class="text-xl font-bold">Create an Account</h2>
            </div>
            
            <div class="p-6">
                <?php if (!empty($error_message)): ?>
                    <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4" role="alert">
                        <p><?php echo $error_message; ?></p>
                    </div>
                <?php endif; ?>
                
                <?php if (!empty($success_message)): ?>
                    <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-4" role="alert">
                        <p><?php echo $success_message; ?></p>
                    </div>
                <?php endif; ?>
                
                <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" x-data="{ password: '', confirmPassword: '', strengthLevel: 0 }">
                    <div class="mb-4">
                        <label for="name" class="block text-gray-700 text-sm font-bold mb-2">Full Name</label>
                        <input type="text" id="name" name="name" class="form-control w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:border-black" required>
                    </div>
                    
                    <div class="mb-4">
                        <label for="email" class="block text-gray-700 text-sm font-bold mb-2">Email Address</label>
                        <input type="email" id="email" name="email" class="form-control w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:border-black" required>
                    </div>
                    
                    <div class="mb-4">
                        <label for="password" class="block text-gray-700 text-sm font-bold mb-2">Password</label>
                        <input 
                            type="password" 
                            id="password" 
                            name="password" 
                            x-model="password" 
                            @input="strengthLevel = checkPasswordStrength(password)"
                            class="form-control w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:border-black" 
                            required
                            minlength="8"
                        >
                        <div class="flex space-x-1 mt-1">
                            <div class="password-strength-meter bg-gray-200 flex-1 rounded-sm" :class="{'bg-red-500': strengthLevel >= 1}"></div>
                            <div class="password-strength-meter bg-gray-200 flex-1 rounded-sm" :class="{'bg-yellow-500': strengthLevel >= 2}"></div>
                            <div class="password-strength-meter bg-gray-200 flex-1 rounded-sm" :class="{'bg-green-500': strengthLevel >= 3}"></div>
                        </div>
                        <p class="text-xs text-gray-500 mt-1">Password must be at least 8 characters long</p>
                    </div>
                    
                    <div class="mb-6">
                        <label for="confirm_password" class="block text-gray-700 text-sm font-bold mb-2">Confirm Password</label>
                        <input 
                            type="password" 
                            id="confirm_password" 
                            name="confirm_password" 
                            x-model="confirmPassword"
                            class="form-control w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none"
                            :class="{'border-red-500': confirmPassword && password !== confirmPassword, 'border-green-500': confirmPassword && password === confirmPassword}" 
                            required
                        >
                        <p class="text-xs text-red-500 mt-1" x-show="confirmPassword && password !== confirmPassword">Passwords do not match</p>
                    </div>
                    
                    <div class="flex items-center justify-between mb-4">
                        <button type="submit" name="register" class="w-full bg-black hover:bg-gray-800 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline transition duration-150">
                            Register
                        </button>
                    </div>
                    
                    <p class="text-center text-gray-600 text-sm">
                        Already have an account? <a href="login.php" class="text-black font-semibold hover:underline">Login here</a>
                    </p>
                </form>
            </div>
        </div>
    </div>

    <script>
        function checkPasswordStrength(password) {
            // Initialize strength level
            let strength = 0;
            
            // Check password length
            if (password.length >= 8) {
                strength += 1;
                
                // Check for mixed case
                if (/[A-Z]/.test(password) && /[a-z]/.test(password)) {
                    strength += 1;
                }
                
                // Check for numbers and special characters
                if (/[0-9]/.test(password) && /[^A-Za-z0-9]/.test(password)) {
                    strength += 1;
                }
            }
            
            return strength;
        }
    </script>
</body>
</html>
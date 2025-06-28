<?php
session_start();
require_once '../config.php';

// Check if user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

// Initialize stats variables
$totalUsers = 0;
$totalOrders = 0;
$totalProducts = 0;
$totalRevenue = 0;
$recentOrders = [];
$monthlyRevenue = [];

// Get dashboard statistics
$conn = connectDatabase();

// Get total users
$result = $conn->query("SELECT COUNT(*) as total FROM users WHERE role = 'customer'");
if ($result) {
    $row = $result->fetch_assoc();
    $totalUsers = $row['total'];
}

// Get total orders
$result = $conn->query("SELECT COUNT(*) as total FROM orders");
if ($result) {
    $row = $result->fetch_assoc();
    $totalOrders = $row['total'];
}

// Get total products
$result = $conn->query("SELECT COUNT(*) as total FROM products");
if ($result) {
    $row = $result->fetch_assoc();
    $totalProducts = $row['total'];
}

// Get total revenue
$result = $conn->query("SELECT SUM(total_amount) as revenue FROM orders WHERE status != 'cancelled'");
if ($result) {
    $row = $result->fetch_assoc();
    $totalRevenue = $row['revenue'] ?? 0;
}

// Get recent orders
$result = $conn->query("SELECT o.id, o.total_amount, o.status, o.created_at, u.name as customer_name 
                       FROM orders o 
                       JOIN users u ON o.user_id = u.id 
                       ORDER BY o.created_at DESC LIMIT 5");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $recentOrders[] = $row;
    }
}

// Get monthly revenue for chart (last 6 months)
$result = $conn->query("SELECT 
                          DATE_FORMAT(created_at, '%Y-%m') as month,
                          SUM(total_amount) as revenue
                       FROM orders 
                       WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
                         AND status != 'cancelled'
                       GROUP BY DATE_FORMAT(created_at, '%Y-%m')
                       ORDER BY month ASC");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $monthlyRevenue[] = $row;
    }
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - masixSL</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@3.7.0/dist/chart.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    <style>
        body { font-family: 'Inter', sans-serif; }
        .sidebar { transition: all 0.3s ease; }
    </style>
</head>
<body class="bg-gray-100 min-h-screen flex flex-col md:flex-row">
    <!-- Sidebar -->
    <aside class="bg-black text-white w-full md:w-64 flex-shrink-0 md:min-h-screen" x-data="{ isOpen: false }">
        <div class="p-4 flex justify-between items-center md:justify-center">
            <a href="dashboard.php" class="flex items-center">
                <img src="../images_home/ch2.png" alt="masixSL" class="h-8 w-auto">
            </a>
            <button @click="isOpen = !isOpen" class="md:hidden text-white">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16m-7 6h7" />
                </svg>
            </button>
        </div>
        
        <nav class="mt-6 md:block" :class="{'hidden': !isOpen}">
            <a href="AdminDashboard.php" class="flex items-center py-3 px-4 bg-gray-800 text-white hover:bg-gray-700">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                </svg>
                Dashboard
            </a>
            <a href="product.php" class="flex items-center py-3 px-4 text-gray-300 hover:bg-gray-700 hover:text-white">
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
                <h1 class="text-2xl font-semibold text-gray-800">Dashboard</h1>
                <div class="flex items-center">
                    <span class="mr-2 text-sm text-gray-600">Welcome, <?php echo htmlspecialchars($_SESSION['user_name']); ?></span>
                    <div class="bg-gray-200 rounded-full h-8 w-8 flex items-center justify-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 px-6 py-8">
            <div class="bg-white rounded-lg shadow-md p-6 hover:shadow-lg transition-shadow">
                <div class="flex items-center">
                    <div class="bg-blue-100 rounded-full p-3">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                        </svg>
                    </div>
                    <div class="ml-4">
                        <h2 class="text-gray-600 text-sm font-medium">Total Users</h2>
                        <p class="text-2xl font-bold text-gray-800"><?php echo number_format($totalUsers); ?></p>
                    </div>
                </div>
            </div>
            
            <div class="bg-white rounded-lg shadow-md p-6 hover:shadow-lg transition-shadow">
                <div class="flex items-center">
                    <div class="bg-green-100 rounded-full p-3">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                        </svg>
                    </div>
                    <div class="ml-4">
                        <h2 class="text-gray-600 text-sm font-medium">Total Orders</h2>
                        <p class="text-2xl font-bold text-gray-800"><?php echo number_format($totalOrders); ?></p>
                    </div>
                </div>
            </div>
            
            <div class="bg-white rounded-lg shadow-md p-6 hover:shadow-lg transition-shadow">
                <div class="flex items-center">
                    <div class="bg-purple-100 rounded-full p-3">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-purple-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                        </svg>
                    </div>
                    <div class="ml-4">
                        <h2 class="text-gray-600 text-sm font-medium">Total Products</h2>
                        <p class="text-2xl font-bold text-gray-800"><?php echo number_format($totalProducts); ?></p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-md p-6 hover:shadow-lg transition-shadow">
                <div class="flex items-center">
                    <div class="bg-yellow-100 rounded-full p-3">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-yellow-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1" />
                        </svg>
                    </div>
                    <div class="ml-4">
                        <h2 class="text-gray-600 text-sm font-medium">Total Revenue</h2>
                        <p class="text-2xl font-bold text-gray-800">Rs. <?php echo number_format($totalRevenue, 2); ?></p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Charts and Tables Row -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 px-6 pb-8">
            <!-- Revenue Chart -->
            <div class="bg-white rounded-lg shadow-md p-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Monthly Revenue</h3>
                <canvas id="revenueChart" width="400" height="200"></canvas>
            </div>

            <!-- Recent Orders -->
            <div class="bg-white rounded-lg shadow-md p-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Recent Orders</h3>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b">
                                <th class="text-left py-2 px-3 font-medium text-gray-600">Order ID</th>
                                <th class="text-left py-2 px-3 font-medium text-gray-600">Customer</th>
                                <th class="text-left py-2 px-3 font-medium text-gray-600">Amount</th>
                                <th class="text-left py-2 px-3 font-medium text-gray-600">Status</th>
                                <th class="text-left py-2 px-3 font-medium text-gray-600">Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($recentOrders)): ?>
                                <tr>
                                    <td colspan="5" class="text-center py-4 text-gray-500">No recent orders found</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($recentOrders as $order): ?>
                                    <tr class="border-b hover:bg-gray-50">
                                        <td class="py-2 px-3 font-mono text-xs">#<?php echo htmlspecialchars($order['id']); ?></td>
                                        <td class="py-2 px-3"><?php echo htmlspecialchars($order['customer_name']); ?></td>
                                        <td class="py-2 px-3 font-semibold">Rs. <?php echo number_format($order['total_amount'], 2); ?></td>
                                        <td class="py-2 px-3">
                                            <span class="px-2 py-1 rounded-full text-xs font-medium
                                                <?php 
                                                    switch($order['status']) {
                                                        case 'pending': echo 'bg-yellow-100 text-yellow-800'; break;
                                                        case 'processing': echo 'bg-blue-100 text-blue-800'; break;
                                                        case 'shipped': echo 'bg-purple-100 text-purple-800'; break;
                                                        case 'delivered': echo 'bg-green-100 text-green-800'; break;
                                                        case 'cancelled': echo 'bg-red-100 text-red-800'; break;
                                                        default: echo 'bg-gray-100 text-gray-800';
                                                    }
                                                ?>">
                                                <?php echo ucfirst(htmlspecialchars($order['status'])); ?>
                                            </span>
                                        </td>
                                        <td class="py-2 px-3 text-gray-600"><?php echo date('M j, Y', strtotime($order['created_at'])); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
                <div class="mt-4">
                    <a href="orders.php" class="text-blue-600 hover:text-blue-800 text-sm font-medium">View all orders →</a>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="px-6 pb-8">
            <div class="bg-white rounded-lg shadow-md p-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Quick Actions</h3>
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <a href="product.php?action=add" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg text-center transition-colors">
                        Add New Product
                    </a>
                    <a href="orders.php" class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded-lg text-center transition-colors">
                        Manage Orders
                    </a>
                    <a href="users.php" class="bg-purple-500 hover:bg-purple-600 text-white px-4 py-2 rounded-lg text-center transition-colors">
                        View Users
                    </a>
                    <a href="settings.php" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg text-center transition-colors">
                        Site Settings
                    </a>
                </div>
            </div>
        </div>
    </main>

    <script>
        // Revenue Chart
        const ctx = document.getElementById('revenueChart').getContext('2d');
        const monthlyData = <?php echo json_encode($monthlyRevenue); ?>;
        
        const labels = monthlyData.map(item => {
            const date = new Date(item.month + '-01');
            return date.toLocaleDateString('en-US', { month: 'short', year: 'numeric' });
        });
        const revenues = monthlyData.map(item => parseFloat(item.revenue));

        new Chart(ctx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Revenue (Rs.)',
                    data: revenues,
                    borderColor: 'rgb(99, 102, 241)',
                    backgroundColor: 'rgba(99, 102, 241, 0.1)',
                    tension: 0.4,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return 'Rs. ' + value.toLocaleString();
                            }
                        }
                    }
                }
            }
        });
    </script>
</body>
</html>
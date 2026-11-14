<?php
require_once '../config/database.php';
require_once '../includes/functions.php';
requireLogin();

$database = new Database();
$db = $database->getConnection();
$userId = $_SESSION['user_id'];

// Get user statistics
$statsQuery = "SELECT 
    (SELECT COUNT(*) FROM documents WHERE uploaded_by = :user_id AND type != 'book') as total_uploads,
    (SELECT COUNT(*) FROM documents WHERE uploaded_by = :user_id AND type = 'premium') as premium_uploads,
    (SELECT COUNT(*) FROM downloads WHERE user_id = :user_id) as my_downloads,
    (SELECT COUNT(*) FROM book_orders WHERE user_id = :user_id) as my_orders";
$statsStmt = $db->prepare($statsQuery);
$statsStmt->bindParam(':user_id', $userId);
$statsStmt->execute();
$stats = $statsStmt->fetch(PDO::FETCH_ASSOC);

// Get recent downloads
$downloadsQuery = "SELECT d.*, doc.title as doc_title, doc.file_type 
                   FROM downloads d 
                   JOIN documents doc ON d.document_id = doc.id 
                   WHERE d.user_id = :user_id 
                   ORDER BY d.downloaded_at DESC LIMIT 5";
$downloadsStmt = $db->prepare($downloadsQuery);
$downloadsStmt->bindParam(':user_id', $userId);
$downloadsStmt->execute();
$recentDownloads = $downloadsStmt->fetchAll(PDO::FETCH_ASSOC);

// Get recent orders
$ordersQuery = "SELECT * FROM book_orders WHERE user_id = :user_id ORDER BY created_at DESC LIMIT 5";
$ordersStmt = $db->prepare($ordersQuery);
$ordersStmt->bindParam(':user_id', $userId);
$ordersStmt->execute();
$recentOrders = $ordersStmt->fetchAll(PDO::FETCH_ASSOC);

// Get user profile
$profileQuery = "SELECT u.*, up.* FROM users u 
                 LEFT JOIN user_profiles up ON u.id = up.user_id 
                 WHERE u.id = :user_id";
$profileStmt = $db->prepare($profileQuery);
$profileStmt->bindParam(':user_id', $userId);
$profileStmt->execute();
$user = $profileStmt->fetch(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Ezeria Library</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { font-family: 'Inter', sans-serif; }
        .gradient-bg {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        .stat-card {
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 25px -12px rgba(0, 0, 0, 0.2);
        }
        .sidebar-link {
            transition: all 0.3s ease;
            border-left: 3px solid transparent;
        }
        .sidebar-link:hover, .sidebar-link.active {
            background: linear-gradient(90deg, #eef2ff 0%, transparent 100%);
            border-left-color: #4f46e5;
            color: #4f46e5;
        }
    </style>
</head>
<body class="bg-gray-50">

<!-- Navigation -->
<nav class="bg-white shadow-md sticky top-0 z-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center h-16">
            <div class="flex items-center space-x-3">
                <div class="h-10 w-10 bg-gradient-to-r from-indigo-600 to-purple-600 rounded-xl flex items-center justify-center">
                    <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                    </svg>
                </div>
                <span class="font-bold text-xl text-gray-800">Ezeria Library</span>
            </div>
            <div class="flex items-center space-x-4">
                <a href="/index.php" class="text-gray-600 hover:text-indigo-600 transition"><i class="fas fa-home"></i></a>
                <a href="/auth/logout.php" class="bg-red-500 text-white px-4 py-2 rounded-lg hover:bg-red-600 transition text-sm">
                    <i class="fas fa-sign-out-alt mr-2"></i> Logout
                </a>
            </div>
        </div>
    </div>
</nav>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="flex flex-col lg:flex-row gap-8">
        
        <!-- Sidebar -->
        <div class="lg:w-80 flex-shrink-0">
            <div class="bg-white rounded-2xl shadow-sm overflow-hidden sticky top-24">
                <div class="gradient-bg p-6 text-center">
                    <div class="w-24 h-24 bg-white rounded-full flex items-center justify-center mx-auto mb-3 shadow-lg">
                        <i class="fas fa-user text-4xl text-indigo-600"></i>
                    </div>
                    <h3 class="text-white font-bold text-xl"><?php echo htmlspecialchars($user['username']); ?></h3>
                    <p class="text-indigo-200 text-sm"><?php echo ucfirst($user['role']); ?> Member</p>
                    <span class="inline-block mt-2 bg-white/20 text-white text-xs px-3 py-1 rounded-full">
                        Member since <?php echo date('M Y', strtotime($user['created_at'])); ?>
                    </span>
                </div>
                <div class="p-4">
                    <a href="/user/dashboard.php" class="sidebar-link active flex items-center gap-3 px-4 py-3 text-gray-700 rounded-lg">
                        <i class="fas fa-tachometer-alt w-5 text-indigo-600"></i> Dashboard
                    </a>
                    <a href="/user/profile.php" class="sidebar-link flex items-center gap-3 px-4 py-3 text-gray-700 rounded-lg">
                        <i class="fas fa-user-circle w-5 text-indigo-600"></i> My Profile
                    </a>
                    <a href="/user/uploads.php" class="sidebar-link flex items-center gap-3 px-4 py-3 text-gray-700 rounded-lg">
                        <i class="fas fa-upload w-5 text-indigo-600"></i> My Uploads
                        <span class="ml-auto bg-gray-200 text-gray-600 text-xs px-2 py-1 rounded-full"><?php echo $stats['total_uploads']; ?></span>
                    </a>
                    <a href="/user/downloads.php" class="sidebar-link flex items-center gap-3 px-4 py-3 text-gray-700 rounded-lg">
                        <i class="fas fa-download w-5 text-indigo-600"></i> My Downloads
                        <span class="ml-auto bg-gray-200 text-gray-600 text-xs px-2 py-1 rounded-full"><?php echo $stats['my_downloads']; ?></span>
                    </a>
                    <a href="/user/orders.php" class="sidebar-link flex items-center gap-3 px-4 py-3 text-gray-700 rounded-lg">
                        <i class="fas fa-shopping-cart w-5 text-indigo-600"></i> Book Orders
                        <span class="ml-auto bg-gray-200 text-gray-600 text-xs px-2 py-1 rounded-full"><?php echo $stats['my_orders']; ?></span>
                    </a>
                    <a href="/user/settings.php" class="sidebar-link flex items-center gap-3 px-4 py-3 text-gray-700 rounded-lg">
                        <i class="fas fa-cog w-5 text-indigo-600"></i> Settings
                    </a>
                </div>
            </div>
        </div>
        
        <!-- Main Content -->
        <div class="flex-1">
            <!-- Welcome Banner -->
            <div class="gradient-bg rounded-2xl p-6 mb-6 text-white">
                <h1 class="text-2xl font-bold">Welcome back, <?php echo htmlspecialchars($user['username']); ?>!</h1>
                <p class="text-indigo-100 mt-1">Continue your learning journey with Ezeria Library</p>
            </div>
            
            <!-- Stats Cards -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5 mb-8">
                <div class="stat-card bg-white rounded-xl p-5 shadow-sm">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-gray-500 text-sm">Total Uploads</p>
                            <p class="text-2xl font-bold text-gray-800"><?php echo $stats['total_uploads']; ?></p>
                        </div>
                        <div class="w-12 h-12 bg-indigo-100 rounded-full flex items-center justify-center">
                            <i class="fas fa-upload text-indigo-600 text-xl"></i>
                        </div>
                    </div>
                </div>
                <div class="stat-card bg-white rounded-xl p-5 shadow-sm">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-gray-500 text-sm">Premium Uploads</p>
                            <p class="text-2xl font-bold text-gray-800"><?php echo $stats['premium_uploads']; ?></p>
                        </div>
                        <div class="w-12 h-12 bg-yellow-100 rounded-full flex items-center justify-center">
                            <i class="fas fa-crown text-yellow-600 text-xl"></i>
                        </div>
                    </div>
                </div>
                <div class="stat-card bg-white rounded-xl p-5 shadow-sm">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-gray-500 text-sm">My Downloads</p>
                            <p class="text-2xl font-bold text-gray-800"><?php echo $stats['my_downloads']; ?></p>
                        </div>
                        <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center">
                            <i class="fas fa-download text-green-600 text-xl"></i>
                        </div>
                    </div>
                </div>
                <div class="stat-card bg-white rounded-xl p-5 shadow-sm">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-gray-500 text-sm">Book Orders</p>
                            <p class="text-2xl font-bold text-gray-800"><?php echo $stats['my_orders']; ?></p>
                        </div>
                        <div class="w-12 h-12 bg-purple-100 rounded-full flex items-center justify-center">
                            <i class="fas fa-shopping-cart text-purple-600 text-xl"></i>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Quick Actions -->
            <div class="bg-white rounded-2xl shadow-sm p-6 mb-8">
                <h3 class="font-bold text-lg text-gray-800 mb-4 flex items-center gap-2">
                    <i class="fas fa-bolt text-indigo-600"></i> Quick Actions
                </h3>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                    <a href="/user/upload.php" class="bg-gradient-to-r from-indigo-600 to-purple-600 text-white text-center py-3 rounded-xl hover:from-indigo-700 hover:to-purple-700 transition font-medium">
                        <i class="fas fa-plus mr-2"></i> New Upload
                    </a>
                    <a href="/user/uploads.php" class="border-2 border-indigo-600 text-indigo-600 text-center py-3 rounded-xl hover:bg-indigo-600 hover:text-white transition font-medium">
                        <i class="fas fa-folder-open mr-2"></i> My Uploads
                    </a>
                    <a href="/user/downloads.php" class="border-2 border-indigo-600 text-indigo-600 text-center py-3 rounded-xl hover:bg-indigo-600 hover:text-white transition font-medium">
                        <i class="fas fa-download mr-2"></i> Downloads
                    </a>
                    <a href="/book-store.php" class="border-2 border-indigo-600 text-indigo-600 text-center py-3 rounded-xl hover:bg-indigo-600 hover:text-white transition font-medium">
                        <i class="fas fa-book mr-2"></i> Browse Books
                    </a>
                </div>
            </div>
            
            <!-- Recent Downloads -->
            <?php if (count($recentDownloads) > 0): ?>
            <div class="bg-white rounded-2xl shadow-sm p-6 mb-8">
                <h3 class="font-bold text-lg text-gray-800 mb-4 flex items-center gap-2">
                    <i class="fas fa-history text-indigo-600"></i> Recent Downloads
                </h3>
                <div class="space-y-3">
                    <?php foreach ($recentDownloads as $download): ?>
                    <div class="flex items-center justify-between py-2 border-b border-gray-100">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 bg-indigo-100 rounded-lg flex items-center justify-center">
                                <i class="fas fa-file-pdf text-indigo-600"></i>
                            </div>
                            <div>
                                <a href="/document.php?id=<?php echo $download['document_id']; ?>" class="font-medium text-gray-800 hover:text-indigo-600">
                                    <?php echo htmlspecialchars($download['doc_title']); ?>
                                </a>
                                <p class="text-xs text-gray-500">Downloaded on <?php echo date('M j, Y g:i A', strtotime($download['downloaded_at'])); ?></p>
                            </div>
                        </div>
                        <a href="/document.php?id=<?php echo $download['document_id']; ?>" class="text-indigo-600 hover:text-indigo-700 text-sm">
                            View <i class="fas fa-arrow-right ml-1"></i>
                        </a>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>
            
            <!-- Recent Orders -->
            <?php if (count($recentOrders) > 0): ?>
            <div class="bg-white rounded-2xl shadow-sm p-6">
                <h3 class="font-bold text-lg text-gray-800 mb-4 flex items-center gap-2">
                    <i class="fas fa-shopping-bag text-indigo-600"></i> Recent Orders
                </h3>
                <div class="space-y-3">
                    <?php foreach ($recentOrders as $order): ?>
                    <div class="flex items-center justify-between py-2 border-b border-gray-100">
                        <div>
                            <p class="font-medium text-gray-800">Order #<?php echo $order['order_number']; ?></p>
                            <p class="text-xs text-gray-500">Placed on <?php echo date('M j, Y', strtotime($order['created_at'])); ?></p>
                        </div>
                        <div class="text-right">
                            <p class="font-bold text-indigo-600">UGX <?php echo number_format($order['total_price'], 0); ?></p>
                            <span class="text-xs px-2 py-1 rounded-full <?php 
                                echo $order['payment_status'] == 'completed' ? 'bg-green-100 text-green-700' : 
                                    ($order['payment_status'] == 'pending' ? 'bg-yellow-100 text-yellow-700' : 'bg-red-100 text-red-700'); 
                            ?>">
                                <?php echo ucfirst($order['payment_status']); ?>
                            </span>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Footer -->
<footer class="bg-gray-900 text-white mt-12">
    <div class="max-w-7xl mx-auto px-4 py-6 text-center text-gray-400 text-sm">
        <p>&copy; <?php echo date('Y'); ?> Ezeria Library. All rights reserved.</p>
    </div>
</footer>

</body>
</html>
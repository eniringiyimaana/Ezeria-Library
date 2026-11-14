<?php
require_once '../config/database.php';
require_once '../includes/functions.php';
requireRole('admin');

$database = new Database();
$db = $database->getConnection();

// Get system statistics
$statsQuery = "SELECT 
    (SELECT COUNT(*) FROM users) as total_users,
    (SELECT COUNT(*) FROM users WHERE role = 'user') as regular_users,
    (SELECT COUNT(*) FROM users WHERE role = 'librarian') as librarians,
    (SELECT COUNT(*) FROM users WHERE role = 'admin') as admins,
    (SELECT COUNT(*) FROM documents) as total_documents,
    (SELECT COUNT(*) FROM documents WHERE type = 'free') as free_documents,
    (SELECT COUNT(*) FROM documents WHERE type = 'premium') as premium_documents,
    (SELECT COUNT(*) FROM documents WHERE type = 'book') as books,
    (SELECT COUNT(*) FROM documents WHERE status = 'pending') as pending_documents,
    (SELECT COUNT(*) FROM downloads) as total_downloads,
    (SELECT COUNT(*) FROM book_orders) as total_orders,
    (SELECT SUM(total_price) FROM book_orders WHERE payment_status = 'completed') as total_revenue";
$statsStmt = $db->prepare($statsQuery);
$statsStmt->execute();
$stats = $statsStmt->fetch(PDO::FETCH_ASSOC);

// Get recent users
$usersQuery = "SELECT * FROM users ORDER BY created_at DESC LIMIT 10";
$usersStmt = $db->prepare($usersQuery);
$usersStmt->execute();
$recentUsers = $usersStmt->fetchAll(PDO::FETCH_ASSOC);

// Get pending documents
$pendingQuery = "SELECT d.*, u.username as uploader 
                 FROM documents d 
                 LEFT JOIN users u ON d.uploaded_by = u.id 
                 WHERE d.status = 'pending' 
                 ORDER BY d.created_at DESC LIMIT 10";
$pendingStmt = $db->prepare($pendingQuery);
$pendingStmt->execute();
$pendingDocs = $pendingStmt->fetchAll(PDO::FETCH_ASSOC);

// Get recent orders
$ordersQuery = "SELECT bo.*, u.username, d.title as book_title 
                FROM book_orders bo 
                JOIN users u ON bo.user_id = u.id 
                JOIN documents d ON bo.document_id = d.id 
                ORDER BY bo.created_at DESC LIMIT 10";
$ordersStmt = $db->prepare($ordersQuery);
$ordersStmt->execute();
$recentOrders = $ordersStmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Ezeria Library</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { font-family: 'Inter', sans-serif; }
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
<nav class="bg-gradient-to-r from-indigo-800 to-purple-800 text-white shadow-lg sticky top-0 z-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center h-16">
            <div class="flex items-center space-x-3">
                <i class="fas fa-crown text-2xl"></i>
                <span class="font-bold text-xl">Ezeria Library Admin</span>
                <span class="bg-white/20 px-3 py-1 rounded-full text-xs">Administrator</span>
            </div>
            <div class="flex items-center space-x-4">
                <a href="/index.php" class="hover:text-indigo-200 transition"><i class="fas fa-home"></i></a>
                <a href="/auth/logout.php" class="bg-red-500/20 px-4 py-2 rounded-lg hover:bg-red-500/30 transition text-sm">
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
                <div class="gradient-bg p-6 text-center bg-gradient-to-r from-indigo-600 to-purple-600">
                    <div class="w-24 h-24 bg-white rounded-full flex items-center justify-center mx-auto mb-3 shadow-lg">
                        <i class="fas fa-user-shield text-4xl text-indigo-600"></i>
                    </div>
                    <h3 class="text-white font-bold text-xl">Admin</h3>
                    <p class="text-indigo-200 text-sm">System Administrator</p>
                </div>
                <div class="p-4">
                    <a href="/admin/dashboard.php" class="sidebar-link active flex items-center gap-3 px-4 py-3 text-gray-700 rounded-lg">
                        <i class="fas fa-tachometer-alt w-5 text-indigo-600"></i> Dashboard
                    </a>
                    <a href="/admin/users.php" class="sidebar-link flex items-center gap-3 px-4 py-3 text-gray-700 rounded-lg">
                        <i class="fas fa-users w-5 text-indigo-600"></i> Manage Users
                    </a>
                    <a href="/admin/documents.php" class="sidebar-link flex items-center gap-3 px-4 py-3 text-gray-700 rounded-lg">
                        <i class="fas fa-file-alt w-5 text-indigo-600"></i> Manage Documents
                    </a>
                    <a href="/admin/pending.php" class="sidebar-link flex items-center gap-3 px-4 py-3 text-gray-700 rounded-lg">
                        <i class="fas fa-clock w-5 text-indigo-600"></i> Pending Approvals
                        <?php if ($stats['pending_documents'] > 0): ?>
                        <span class="ml-auto bg-red-500 text-white text-xs px-2 py-1 rounded-full"><?php echo $stats['pending_documents']; ?></span>
                        <?php endif; ?>
                    </a>
                    <a href="/admin/orders.php" class="sidebar-link flex items-center gap-3 px-4 py-3 text-gray-700 rounded-lg">
                        <i class="fas fa-shopping-cart w-5 text-indigo-600"></i> Orders
                    </a>
                    <a href="/admin/messages.php" class="sidebar-link flex items-center gap-3 px-4 py-3 text-gray-700 rounded-lg">
                        <i class="fas fa-envelope w-5 text-indigo-600"></i> Messages
                    </a>
                    <a href="/admin/settings.php" class="sidebar-link flex items-center gap-3 px-4 py-3 text-gray-700 rounded-lg">
                        <i class="fas fa-cog w-5 text-indigo-600"></i> Settings
                    </a>
                </div>
            </div>
        </div>
        
        <!-- Main Content -->
        <div class="flex-1">
            <h1 class="text-2xl font-bold text-gray-800 mb-6">System Dashboard</h1>
            
            <!-- Stats Row 1 -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5 mb-6">
                <div class="bg-white rounded-xl p-5 shadow-sm border-l-4 border-indigo-500">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-gray-500 text-sm">Total Users</p>
                            <p class="text-2xl font-bold text-gray-800"><?php echo number_format($stats['total_users']); ?></p>
                        </div>
                        <i class="fas fa-users text-3xl text-indigo-400"></i>
                    </div>
                    <div class="mt-2 text-xs text-gray-400">
                        <span class="text-green-500"><?php echo $stats['regular_users']; ?></span> regular · 
                        <span class="text-blue-500"><?php echo $stats['librarians']; ?></span> librarians · 
                        <span class="text-purple-500"><?php echo $stats['admins']; ?></span> admins
                    </div>
                </div>
                <div class="bg-white rounded-xl p-5 shadow-sm border-l-4 border-green-500">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-gray-500 text-sm">Total Documents</p>
                            <p class="text-2xl font-bold text-gray-800"><?php echo number_format($stats['total_documents']); ?></p>
                        </div>
                        <i class="fas fa-file-alt text-3xl text-green-400"></i>
                    </div>
                    <div class="mt-2 text-xs text-gray-400">
                        <span class="text-green-500"><?php echo $stats['free_documents']; ?></span> free · 
                        <span class="text-yellow-500"><?php echo $stats['premium_documents']; ?></span> premium · 
                        <span class="text-blue-500"><?php echo $stats['books']; ?></span> books
                    </div>
                </div>
                <div class="bg-white rounded-xl p-5 shadow-sm border-l-4 border-yellow-500">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-gray-500 text-sm">Total Downloads</p>
                            <p class="text-2xl font-bold text-gray-800"><?php echo number_format($stats['total_downloads']); ?></p>
                        </div>
                        <i class="fas fa-download text-3xl text-yellow-400"></i>
                    </div>
                </div>
                <div class="bg-white rounded-xl p-5 shadow-sm border-l-4 border-purple-500">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-gray-500 text-sm">Total Revenue</p>
                            <p class="text-2xl font-bold text-gray-800">UGX <?php echo number_format($stats['total_revenue'] ?? 0, 0); ?></p>
                        </div>
                        <i class="fas fa-money-bill-wave text-3xl text-purple-400"></i>
                    </div>
                </div>
            </div>
            
            <!-- Pending Documents Alert -->
            <?php if ($stats['pending_documents'] > 0): ?>
            <div class="bg-yellow-50 border-l-4 border-yellow-400 rounded-xl p-4 mb-6">
                <div class="flex items-center">
                    <i class="fas fa-exclamation-triangle text-yellow-500 text-xl mr-3"></i>
                    <div>
                        <p class="font-medium text-yellow-800">Pending Approvals</p>
                        <p class="text-sm text-yellow-700">There are <?php echo $stats['pending_documents']; ?> documents waiting for your review.</p>
                    </div>
                    <a href="/admin/pending.php" class="ml-auto bg-yellow-500 text-white px-4 py-2 rounded-lg hover:bg-yellow-600 transition text-sm">
                        Review Now <i class="fas fa-arrow-right ml-1"></i>
                    </a>
                </div>
            </div>
            <?php endif; ?>
            
            <!-- Pending Documents Table -->
            <?php if (count($pendingDocs) > 0): ?>
            <div class="bg-white rounded-2xl shadow-sm p-6 mb-8">
                <h3 class="font-bold text-lg text-gray-800 mb-4 flex items-center gap-2">
                    <i class="fas fa-clock text-yellow-500"></i> Pending Documents
                </h3>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left">Title</th>
                                <th class="px-4 py-3 text-left">Uploader</th>
                                <th class="px-4 py-3 text-left">Type</th>
                                <th class="px-4 py-3 text-left">Date</th>
                                <th class="px-4 py-3 text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <?php foreach ($pendingDocs as $doc): ?>
                            <tr>
                                <td class="px-4 py-3 font-medium"><?php echo htmlspecialchars(substr($doc['title'], 0, 50)); ?>...</td>
                                <td class="px-4 py-3"><?php echo htmlspecialchars($doc['uploader']); ?></td>
                                <td class="px-4 py-3">
                                    <span class="px-2 py-1 rounded-full text-xs <?php 
                                        echo $doc['type'] == 'premium' ? 'bg-yellow-100 text-yellow-700' : 'bg-blue-100 text-blue-700'; 
                                    ?>">
                                        <?php echo ucfirst($doc['type']); ?>
                                    </span>
                                </td>
                                <td class="px-4 py-3"><?php echo date('M j, Y', strtotime($doc['created_at'])); ?></td>
                                <td class="px-4 py-3 text-center">
                                    <div class="flex justify-center gap-2">
                                        <button onclick="approveDocument(<?php echo $doc['id']; ?>)" class="bg-green-500 text-white px-3 py-1 rounded text-xs hover:bg-green-600">
                                            <i class="fas fa-check"></i> Approve
                                        </button>
                                        <button onclick="rejectDocument(<?php echo $doc['id']; ?>)" class="bg-red-500 text-white px-3 py-1 rounded text-xs hover:bg-red-600">
                                            <i class="fas fa-times"></i> Reject
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <?php endif; ?>
            
            <!-- Recent Users & Orders -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                <!-- Recent Users -->
                <div class="bg-white rounded-2xl shadow-sm p-6">
                    <h3 class="font-bold text-lg text-gray-800 mb-4 flex items-center gap-2">
                        <i class="fas fa-user-plus text-indigo-600"></i> Recent Users
                    </h3>
                    <div class="space-y-3">
                        <?php foreach ($recentUsers as $user): ?>
                        <div class="flex items-center justify-between py-2 border-b border-gray-100">
                            <div>
                                <p class="font-medium text-gray-800"><?php echo htmlspecialchars($user['username']); ?></p>
                                <p class="text-xs text-gray-500"><?php echo htmlspecialchars($user['email']); ?></p>
                            </div>
                            <div class="text-right">
                                <span class="text-xs px-2 py-1 rounded-full <?php 
                                    echo $user['role'] == 'admin' ? 'bg-purple-100 text-purple-700' : 
                                        ($user['role'] == 'librarian' ? 'bg-blue-100 text-blue-700' : 'bg-gray-100 text-gray-700'); 
                                ?>">
                                    <?php echo ucfirst($user['role']); ?>
                                </span>
                                <p class="text-xs text-gray-400 mt-1"><?php echo date('M j', strtotime($user['created_at'])); ?></p>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <a href="/admin/users.php" class="block text-center mt-4 text-indigo-600 hover:text-indigo-700 text-sm">
                        View All Users <i class="fas fa-arrow-right ml-1"></i>
                    </a>
                </div>
                
                <!-- Recent Orders -->
                <div class="bg-white rounded-2xl shadow-sm p-6">
                    <h3 class="font-bold text-lg text-gray-800 mb-4 flex items-center gap-2">
                        <i class="fas fa-shopping-cart text-green-600"></i> Recent Orders
                    </h3>
                    <div class="space-y-3">
                        <?php foreach ($recentOrders as $order): ?>
                        <div class="flex items-center justify-between py-2 border-b border-gray-100">
                            <div>
                                <p class="font-medium text-gray-800">Order #<?php echo $order['order_number']; ?></p>
                                <p class="text-xs text-gray-500"><?php echo htmlspecialchars($order['username']); ?> • <?php echo htmlspecialchars(substr($order['book_title'], 0, 30)); ?></p>
                            </div>
                            <div class="text-right">
                                <p class="font-bold text-indigo-600">UGX <?php echo number_format($order['total_price'], 0); ?></p>
                                <span class="text-xs px-2 py-1 rounded-full <?php 
                                    echo $order['payment_status'] == 'completed' ? 'bg-green-100 text-green-700' : 'bg-yellow-100 text-yellow-700'; 
                                ?>">
                                    <?php echo ucfirst($order['payment_status']); ?>
                                </span>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <a href="/admin/orders.php" class="block text-center mt-4 text-indigo-600 hover:text-indigo-700 text-sm">
                        View All Orders <i class="fas fa-arrow-right ml-1"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function approveDocument(docId) {
    if (confirm('Are you sure you want to approve this document?')) {
        window.location.href = '/admin/approve-document.php?id=' + docId;
    }
}

function rejectDocument(docId) {
    if (confirm('Are you sure you want to reject this document?')) {
        window.location.href = '/admin/reject-document.php?id=' + docId;
    }
}
</script>

</body>
</html>
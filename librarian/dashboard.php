<?php
require_once '../config/database.php';
require_once '../includes/functions.php';
requireRole('librarian');

$database = new Database();
$db = $database->getConnection();
$userId = $_SESSION['user_id'];

// Get librarian statistics
$statsQuery = "SELECT 
    (SELECT COUNT(*) FROM documents WHERE uploaded_by = :user_id) as my_uploads,
    (SELECT COUNT(*) FROM documents WHERE uploaded_by = :user_id AND type = 'premium') as premium_uploads,
    (SELECT COUNT(*) FROM documents WHERE uploaded_by = :user_id AND status = 'pending') as pending_uploads,
    (SELECT SUM(downloads_count) FROM documents WHERE uploaded_by = :user_id) as total_downloads,
    (SELECT COUNT(*) FROM messages WHERE status = 'unread') as unread_messages";
$statsStmt = $db->prepare($statsQuery);
$statsStmt->bindParam(':user_id', $userId);
$statsStmt->execute();
$stats = $statsStmt->fetch(PDO::FETCH_ASSOC);

// Get recent uploads
$uploadsQuery = "SELECT * FROM documents WHERE uploaded_by = :user_id ORDER BY created_at DESC LIMIT 10";
$uploadsStmt = $db->prepare($uploadsQuery);
$uploadsStmt->bindParam(':user_id', $userId);
$uploadsStmt->execute();
$recentUploads = $uploadsStmt->fetchAll(PDO::FETCH_ASSOC);

// Get recent messages
$messagesQuery = "SELECT * FROM messages ORDER BY created_at DESC LIMIT 10";
$messagesStmt = $db->prepare($messagesQuery);
$messagesStmt->execute();
$recentMessages = $messagesStmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Librarian Dashboard - Ezeria Library</title>
    <script src="https://cdn.tailwindcss.com"></script>
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
        .stat-card {
            transition: transform 0.3s ease;
        }
        .stat-card:hover {
            transform: translateY(-5px);
        }
    </style>
</head>
<body class="bg-gray-50">

<!-- Navigation -->
<nav class="bg-gradient-to-r from-blue-800 to-indigo-800 text-white shadow-lg sticky top-0 z-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center h-16">
            <div class="flex items-center space-x-3">
                <i class="fas fa-book-open text-2xl"></i>
                <span class="font-bold text-xl">Ezeria Library - Librarian Portal</span>
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
                <div class="bg-gradient-to-r from-blue-600 to-indigo-600 p-6 text-center">
                    <div class="w-24 h-24 bg-white rounded-full flex items-center justify-center mx-auto mb-3 shadow-lg">
                        <i class="fas fa-user-graduate text-4xl text-indigo-600"></i>
                    </div>
                    <h3 class="text-white font-bold text-xl"><?php echo htmlspecialchars($_SESSION['username']); ?></h3>
                    <p class="text-indigo-200 text-sm">Librarian</p>
                </div>
                <div class="p-4">
                    <a href="/librarian/dashboard.php" class="sidebar-link active flex items-center gap-3 px-4 py-3 text-gray-700 rounded-lg">
                        <i class="fas fa-tachometer-alt w-5 text-indigo-600"></i> Dashboard
                    </a>
                    <a href="/librarian/upload.php" class="sidebar-link flex items-center gap-3 px-4 py-3 text-gray-700 rounded-lg">
                        <i class="fas fa-plus-circle w-5 text-indigo-600"></i> New Upload
                    </a>
                    <a href="/librarian/uploads.php" class="sidebar-link flex items-center gap-3 px-4 py-3 text-gray-700 rounded-lg">
                        <i class="fas fa-folder-open w-5 text-indigo-600"></i> My Uploads
                    </a>
                    <a href="/librarian/messages.php" class="sidebar-link flex items-center gap-3 px-4 py-3 text-gray-700 rounded-lg">
                        <i class="fas fa-envelope w-5 text-indigo-600"></i> Messages
                        <?php if ($stats['unread_messages'] > 0): ?>
                        <span class="ml-auto bg-red-500 text-white text-xs px-2 py-1 rounded-full"><?php echo $stats['unread_messages']; ?></span>
                        <?php endif; ?>
                    </a>
                    <a href="/librarian/analytics.php" class="sidebar-link flex items-center gap-3 px-4 py-3 text-gray-700 rounded-lg">
                        <i class="fas fa-chart-line w-5 text-indigo-600"></i> Analytics
                    </a>
                    <a href="/librarian/settings.php" class="sidebar-link flex items-center gap-3 px-4 py-3 text-gray-700 rounded-lg">
                        <i class="fas fa-cog w-5 text-indigo-600"></i> Settings
                    </a>
                </div>
            </div>
        </div>
        
        <!-- Main Content -->
        <div class="flex-1">
            <h1 class="text-2xl font-bold text-gray-800 mb-6">Librarian Dashboard</h1>
            
            <!-- Stats Cards -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5 mb-8">
                <div class="stat-card bg-white rounded-xl p-5 shadow-sm border-l-4 border-blue-500">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-gray-500 text-sm">My Uploads</p>
                            <p class="text-2xl font-bold text-gray-800"><?php echo number_format($stats['my_uploads']); ?></p>
                        </div>
                        <i class="fas fa-upload text-3xl text-blue-400"></i>
                    </div>
                </div>
                <div class="stat-card bg-white rounded-xl p-5 shadow-sm border-l-4 border-yellow-500">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-gray-500 text-sm">Premium Uploads</p>
                            <p class="text-2xl font-bold text-gray-800"><?php echo number_format($stats['premium_uploads']); ?></p>
                        </div>
                        <i class="fas fa-crown text-3xl text-yellow-400"></i>
                    </div>
                </div>
                <div class="stat-card bg-white rounded-xl p-5 shadow-sm border-l-4 border-orange-500">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-gray-500 text-sm">Pending Approval</p>
                            <p class="text-2xl font-bold text-gray-800"><?php echo number_format($stats['pending_uploads']); ?></p>
                        </div>
                        <i class="fas fa-clock text-3xl text-orange-400"></i>
                    </div>
                </div>
                <div class="stat-card bg-white rounded-xl p-5 shadow-sm border-l-4 border-green-500">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-gray-500 text-sm">Total Downloads</p>
                            <p class="text-2xl font-bold text-gray-800"><?php echo number_format($stats['total_downloads']); ?></p>
                        </div>
                        <i class="fas fa-download text-3xl text-green-400"></i>
                    </div>
                </div>
            </div>
            
            <!-- Quick Upload -->
            <div class="bg-gradient-to-r from-blue-50 to-indigo-50 rounded-2xl p-6 mb-8">
                <div class="flex items-center justify-between flex-wrap gap-4">
                    <div>
                        <h3 class="font-bold text-lg text-gray-800">Share Knowledge with the World</h3>
                        <p class="text-gray-600 text-sm">Upload educational resources and help students learn</p>
                    </div>
                    <a href="/librarian/upload.php" class="bg-gradient-to-r from-blue-600 to-indigo-600 text-white px-6 py-3 rounded-xl hover:from-blue-700 hover:to-indigo-700 transition font-medium flex items-center gap-2">
                        <i class="fas fa-plus-circle"></i> Upload New Resource
                    </a>
                </div>
            </div>
            
            <!-- Recent Uploads -->
            <div class="bg-white rounded-2xl shadow-sm p-6 mb-8">
                <h3 class="font-bold text-lg text-gray-800 mb-4 flex items-center gap-2">
                    <i class="fas fa-history text-blue-600"></i> Recent Uploads
                </h3>
                <?php if (count($recentUploads) > 0): ?>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left">Title</th>
                                <th class="px-4 py-3 text-left">Type</th>
                                <th class="px-4 py-3 text-left">Status</th>
                                <th class="px-4 py-3 text-left">Downloads</th>
                                <th class="px-4 py-3 text-left">Date</th>
                                <th class="px-4 py-3 text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <?php foreach ($recentUploads as $upload): ?>
                            <tr>
                                <td class="px-4 py-3 font-medium"><?php echo htmlspecialchars(substr($upload['title'], 0, 40)); ?>...</td>
                                <td class="px-4 py-3">
                                    <span class="px-2 py-1 rounded-full text-xs <?php 
                                        echo $upload['type'] == 'premium' ? 'bg-yellow-100 text-yellow-700' : 
                                            ($upload['type'] == 'book' ? 'bg-purple-100 text-purple-700' : 'bg-blue-100 text-blue-700'); 
                                    ?>">
                                        <?php echo ucfirst($upload['type']); ?>
                                    </span>
                                </td>
                                <td class="px-4 py-3">
                                    <span class="px-2 py-1 rounded-full text-xs <?php 
                                        echo $upload['status'] == 'approved' ? 'bg-green-100 text-green-700' : 
                                            ($upload['status'] == 'pending' ? 'bg-yellow-100 text-yellow-700' : 'bg-red-100 text-red-700'); 
                                    ?>">
                                        <?php echo ucfirst($upload['status']); ?>
                                    </span>
                                </td>
                                <td class="px-4 py-3"><?php echo number_format($upload['downloads_count']); ?></td>
                                <td class="px-4 py-3"><?php echo date('M j, Y', strtotime($upload['created_at'])); ?></td>
                                <td class="px-4 py-3 text-center">
                                    <a href="/document.php?id=<?php echo $upload['id']; ?>" class="text-indigo-600 hover:text-indigo-700">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php else: ?>
                <p class="text-gray-500 text-center py-8">No uploads yet. Start by uploading your first resource!</p>
                <?php endif; ?>
            </div>
            
            <!-- Recent Messages -->
            <div class="bg-white rounded-2xl shadow-sm p-6">
                <h3 class="font-bold text-lg text-gray-800 mb-4 flex items-center gap-2">
                    <i class="fas fa-envelope text-blue-600"></i> Recent Inquiries
                    <?php if ($stats['unread_messages'] > 0): ?>
                    <span class="bg-red-500 text-white text-xs px-2 py-1 rounded-full"><?php echo $stats['unread_messages']; ?> unread</span>
                    <?php endif; ?>
                </h3>
                <?php if (count($recentMessages) > 0): ?>
                <div class="space-y-3">
                    <?php foreach (array_slice($recentMessages, 0, 5) as $message): ?>
                    <div class="flex items-center justify-between py-3 border-b border-gray-100">
                        <div class="flex-1">
                            <div class="flex items-center gap-2">
                                <p class="font-medium text-gray-800"><?php echo htmlspecialchars($message['name']); ?></p>
                                <?php if ($message['status'] == 'unread'): ?>
                                <span class="bg-red-500 text-white text-xs px-2 py-0.5 rounded-full">New</span>
                                <?php endif; ?>
                            </div>
                            <p class="text-sm text-gray-600"><?php echo htmlspecialchars(substr($message['message'], 0, 60)); ?>...</p>
                            <p class="text-xs text-gray-400 mt-1"><?php echo date('M j, Y g:i A', strtotime($message['created_at'])); ?></p>
                        </div>
                        <a href="/librarian/message.php?id=<?php echo $message['id']; ?>" class="bg-indigo-600 text-white px-3 py-1 rounded-lg text-sm hover:bg-indigo-700 transition">
                            Reply
                        </a>
                    </div>
                    <?php endforeach; ?>
                </div>
                <a href="/librarian/messages.php" class="block text-center mt-4 text-indigo-600 hover:text-indigo-700 text-sm">
                    View All Messages <i class="fas fa-arrow-right ml-1"></i>
                </a>
                <?php else: ?>
                <p class="text-gray-500 text-center py-8">No messages yet.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

</body>
</html>
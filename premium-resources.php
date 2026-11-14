<?php
require_once 'config/database.php';
require_once 'includes/functions.php';

$database = new Database();
$db = $database->getConnection();

// Get premium documents
$premiumQuery = "SELECT d.*, u.username as uploader, l.name as level_name, c.name as category_name 
                 FROM documents d 
                 LEFT JOIN users u ON d.uploaded_by = u.id 
                 LEFT JOIN levels l ON d.level_id = l.id 
                 LEFT JOIN categories c ON d.category_id = c.id 
                 WHERE d.status = 'approved' AND d.type = 'premium' 
                 ORDER BY d.created_at DESC";
$premiumStmt = $db->prepare($premiumQuery);
$premiumStmt->execute();
$premiumDocs = $premiumStmt->fetchAll(PDO::FETCH_ASSOC);

// Get categories for filtering
$categoriesQuery = "SELECT * FROM categories ORDER BY name";
$categoriesStmt = $db->prepare($categoriesQuery);
$categoriesStmt->execute();
$categories = $categoriesStmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Premium Resources - Ezeria Library</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { font-family: 'Inter', sans-serif; }
        .premium-card {
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        .premium-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 25px -12px rgba(0, 0, 0, 0.2);
        }
    </style>
</head>
<body class="bg-gray-50">

<!-- Navigation -->
<nav class="bg-gradient-to-r from-yellow-600 to-amber-600 text-white shadow-lg sticky top-0 z-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center h-16">
            <div class="flex items-center space-x-3">
                <i class="fas fa-crown text-2xl"></i>
                <span class="font-bold text-xl">Ezeria Library Premium</span>
            </div>
            <div class="hidden md:flex items-center space-x-6">
                <a href="/index.php" class="hover:text-yellow-200 transition">Library</a>
                <a href="/premium-resources.php" class="bg-white/20 px-4 py-2 rounded-full hover:bg-white/30 transition">Premium Resources</a>
                <a href="/book-store.php" class="hover:text-yellow-200 transition">Bookstore</a>
                <a href="/ask-librarian.php" class="hover:text-yellow-200 transition">Ask Librarian</a>
                <?php if (isLoggedIn()): ?>
                    <a href="/user/dashboard.php" class="hover:text-yellow-200 transition">
                        <i class="fas fa-user-circle mr-1"></i> Dashboard
                    </a>
                <?php else: ?>
                    <a href="/auth/login.php" class="hover:text-yellow-200 transition">Login / Sign Up</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</nav>

<!-- Hero Section -->
<section class="bg-gradient-to-r from-yellow-600 to-amber-600 py-16">
    <div class="max-w-7xl mx-auto px-4 text-center text-white">
        <i class="fas fa-crown text-5xl mb-4"></i>
        <h1 class="text-4xl md:text-5xl font-bold mb-3">Premium Resources</h1>
        <p class="text-xl text-yellow-100">High-quality, curriculum-aligned materials at affordable prices</p>
        <?php if (!isLoggedIn()): ?>
        <div class="mt-6">
            <a href="/auth/register.php" class="bg-white text-yellow-600 px-6 py-3 rounded-xl font-semibold hover:bg-gray-100 transition inline-flex items-center gap-2">
                <i class="fas fa-user-plus"></i> Join Premium Today
            </a>
        </div>
        <?php endif; ?>
    </div>
</section>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
    
    <!-- Stats Banner -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-10">
        <div class="bg-white rounded-2xl shadow-sm p-5 text-center">
            <i class="fas fa-file-alt text-3xl text-yellow-500 mb-2"></i>
            <p class="text-2xl font-bold text-gray-800"><?php echo number_format(count($premiumDocs)); ?>+</p>
            <p class="text-gray-600 text-sm">Premium Resources</p>
        </div>
        <div class="bg-white rounded-2xl shadow-sm p-5 text-center">
            <i class="fas fa-download text-3xl text-yellow-500 mb-2"></i>
            <p class="text-2xl font-bold text-gray-800">10,000+</p>
            <p class="text-gray-600 text-sm">Downloads</p>
        </div>
        <div class="bg-white rounded-2xl shadow-sm p-5 text-center">
            <i class="fas fa-smile text-3xl text-yellow-500 mb-2"></i>
            <p class="text-2xl font-bold text-gray-800">5,000+</p>
            <p class="text-gray-600 text-sm">Happy Students</p>
        </div>
    </div>
    
    <!-- Premium Resources Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <?php foreach ($premiumDocs as $doc): ?>
        <div class="premium-card bg-gradient-to-br from-yellow-50 to-amber-50 rounded-xl shadow-sm overflow-hidden border border-yellow-200">
            <div class="h-2 bg-gradient-to-r from-yellow-500 to-amber-500"></div>
            <div class="p-5">
                <div class="flex items-center justify-between mb-3">
                    <div class="flex items-center gap-2">
                        <div class="w-10 h-10 bg-yellow-100 rounded-lg flex items-center justify-center">
                            <i class="fas fa-crown text-yellow-600 text-lg"></i>
                        </div>
                        <span class="text-xs font-bold bg-yellow-200 text-yellow-800 px-2 py-0.5 rounded-full">PREMIUM</span>
                    </div>
                    <span class="text-xs font-bold bg-purple-100 text-purple-700 px-2 py-0.5 rounded-full">
                        <?php echo ucfirst($doc['type']); ?>
                    </span>
                </div>
                
                <a href="/document.php?id=<?php echo $doc['id']; ?>" class="block font-bold text-gray-800 hover:text-yellow-600 leading-tight mb-2 line-clamp-2">
                    <?php echo htmlspecialchars($doc['title']); ?>
                </a>
                
                <div class="space-y-1 text-sm text-gray-600 mb-3">
                    <p><i class="fas fa-book-open w-4 text-gray-400"></i> <?php echo htmlspecialchars($doc['category_name'] ?? 'General'); ?></p>
                    <p><i class="fas fa-user w-4 text-gray-400"></i> <?php echo htmlspecialchars($doc['uploader'] ?? 'Anonymous'); ?></p>
                    <p><i class="fas fa-graduation-cap w-4 text-gray-400"></i> <?php echo htmlspecialchars($doc['level_name'] ?? 'All Levels'); ?></p>
                    <?php if ($doc['pages'] > 0): ?>
                    <p><i class="fas fa-file-alt w-4 text-gray-400"></i> <?php echo $doc['pages']; ?> pages</p>
                    <?php endif; ?>
                </div>
                
                <div class="flex items-center justify-between mt-4">
                    <div>
                        <span class="text-2xl font-bold text-yellow-600">UGX <?php echo number_format($doc['price'], 0); ?></span>
                    </div>
                    <button onclick="purchasePremium(<?php echo $doc['id']; ?>)" 
                            class="bg-gradient-to-r from-yellow-500 to-amber-500 text-white px-5 py-2 rounded-lg hover:from-yellow-600 hover:to-amber-600 transition font-medium flex items-center gap-2">
                        <i class="fas fa-shopping-cart"></i> Buy Now
                    </button>
                </div>
                
                <div class="mt-3 pt-3 border-t border-yellow-200 flex items-center justify-between text-xs text-gray-500">
                    <span><i class="far fa-clock mr-1"></i> Instant Access</span>
                    <span><i class="fas fa-download mr-1"></i> <?php echo number_format($doc['downloads_count']); ?> sold</span>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    
    <?php if (count($premiumDocs) == 0): ?>
    <div class="bg-white rounded-2xl shadow-sm p-12 text-center">
        <i class="fas fa-crown text-6xl text-gray-300 mb-4"></i>
        <h3 class="text-xl font-semibold text-gray-800 mb-2">No premium resources yet</h3>
        <p class="text-gray-500">Check back soon for new premium content!</p>
    </div>
    <?php endif; ?>
</div>

<script>
function purchasePremium(docId) {
    <?php if (!isLoggedIn()): ?>
    if (confirm('Please login to purchase this premium resource. Login now?')) {
        window.location.href = '/auth/login.php';
    }
    <?php else: ?>
    window.location.href = '/checkout.php?doc_id=' + docId;
    <?php endif; ?>
}
</script>

<!-- Footer -->
<footer class="bg-gray-900 text-white mt-12">
    <div class="max-w-7xl mx-auto px-4 py-8 text-center text-gray-400 text-sm">
        <p>&copy; <?php echo date('Y'); ?> Ezeria Library. All rights reserved.</p>
    </div>
</footer>

</body>
</html>
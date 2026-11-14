<?php
require_once 'config/database.php';
require_once 'includes/functions.php';

if (!isset($_GET['id'])) {
    header('Location: /index.php');
    exit();
}

$docId = (int)$_GET['id'];
$database = new Database();
$db = $database->getConnection();

// Get document details
$query = "SELECT d.*, u.username as uploader, u.id as uploader_id, l.name as level_name, c.name as category_name 
          FROM documents d 
          LEFT JOIN users u ON d.uploaded_by = u.id 
          LEFT JOIN levels l ON d.level_id = l.id 
          LEFT JOIN categories c ON d.category_id = c.id 
          WHERE d.id = :id AND d.status = 'approved'";
$stmt = $db->prepare($query);
$stmt->bindParam(':id', $docId);
$stmt->execute();
$doc = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$doc) {
    header('Location: /index.php?error=document_not_found');
    exit();
}

// Update view count
$update = "UPDATE documents SET views_count = views_count + 1 WHERE id = :id";
$stmt = $db->prepare($update);
$stmt->bindParam(':id', $docId);
$stmt->execute();

// Get related documents
$relatedQuery = "SELECT id, title, price, type, downloads_count 
                 FROM documents 
                 WHERE (category_id = :category_id OR level_id = :level_id) 
                 AND id != :id AND status = 'approved' 
                 LIMIT 4";
$relatedStmt = $db->prepare($relatedQuery);
$relatedStmt->bindParam(':category_id', $doc['category_id']);
$relatedStmt->bindParam(':level_id', $doc['level_id']);
$relatedStmt->bindParam(':id', $docId);
$relatedStmt->execute();
$relatedDocs = $relatedStmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($doc['title']); ?> - Ezeria Library</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gray-50">

<!-- Navigation -->
<nav class="bg-gradient-to-r from-indigo-600 to-purple-600 text-white shadow-lg sticky top-0 z-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center h-16">
            <div class="flex items-center space-x-3">
                <i class="fas fa-book-open text-2xl"></i>
                <span class="font-bold text-xl">Ezeria Library</span>
            </div>
            <div class="hidden md:flex items-center space-x-6">
                <a href="/index.php" class="hover:text-indigo-200 transition">Library</a>
                <a href="/premium-resources.php" class="hover:text-indigo-200 transition">Premium Resources</a>
                <a href="/book-store.php" class="hover:text-indigo-200 transition">Bookstore</a>
                <a href="/ask-librarian.php" class="hover:text-indigo-200 transition">Ask Librarian</a>
                <?php if (isLoggedIn()): ?>
                    <a href="/user/dashboard.php" class="hover:text-indigo-200 transition">
                        <i class="fas fa-user-circle mr-1"></i> Dashboard
                    </a>
                <?php else: ?>
                    <a href="/auth/login.php" class="hover:text-indigo-200 transition">Login / Sign Up</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</nav>

<div class="max-w-6xl mx-auto px-4 py-8">
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        
        <!-- Main Content -->
        <div class="lg:col-span-2">
            <div class="bg-white rounded-2xl shadow-sm p-6">
                <!-- Document Preview -->
                <div class="mb-6">
                    <div class="flex items-center gap-2 mb-4">
                        <div class="w-12 h-12 bg-indigo-100 rounded-xl flex items-center justify-center">
                            <i class="fas fa-file-pdf text-indigo-600 text-2xl"></i>
                        </div>
                        <div>
                            <h1 class="text-2xl font-bold text-gray-800"><?php echo htmlspecialchars($doc['title']); ?></h1>
                            <p class="text-gray-500 text-sm">
                                by <?php echo htmlspecialchars($doc['uploader'] ?? 'Anonymous'); ?>
                                • <?php echo $doc['pages']; ?> pages
                                • <?php echo date('M j, Y', strtotime($doc['created_at'])); ?>
                            </p>
                        </div>
                    </div>
                    
                    <!-- Preview Image/Icon -->
                    <div class="bg-gray-100 rounded-xl p-8 text-center">
                        <i class="fas fa-file-pdf text-gray-400 text-6xl mb-3"></i>
                        <p class="text-gray-500">Document Preview Available After Purchase</p>
                        <?php if ($doc['type'] == 'free'): ?>
                        <p class="text-green-600 text-sm mt-2">✓ Free Download Available</p>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Document Info -->
                <div class="border-t border-gray-100 pt-6">
                    <h3 class="font-bold text-lg text-gray-800 mb-3">About This Document</h3>
                    <p class="text-gray-600 leading-relaxed">
                        <?php echo !empty($doc['description']) ? htmlspecialchars($doc['description']) : 'No description provided.'; ?>
                    </p>
                </div>
                
                <div class="grid grid-cols-2 gap-4 mt-6 pt-4 border-t border-gray-100">
                    <div>
                        <p class="text-xs text-gray-500">Category</p>
                        <p class="text-sm font-medium text-gray-700"><?php echo htmlspecialchars($doc['category_name'] ?? 'General'); ?></p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500">Education Level</p>
                        <p class="text-sm font-medium text-gray-700"><?php echo htmlspecialchars($doc['level_name'] ?? 'All Levels'); ?></p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500">Author</p>
                        <p class="text-sm font-medium text-gray-700"><?php echo htmlspecialchars($doc['author'] ?? $doc['uploader'] ?? 'Unknown'); ?></p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500">Publication Year</p>
                        <p class="text-sm font-medium text-gray-700"><?php echo $doc['year'] ?? date('Y'); ?></p>
                    </div>
                </div>
                
                <!-- Tags -->
                <div class="mt-6 pt-4 border-t border-gray-100">
                    <div class="flex flex-wrap gap-2">
                        <span class="text-xs bg-gray-100 text-gray-600 px-3 py-1 rounded-full">Educational Resource</span>
                        <span class="text-xs bg-gray-100 text-gray-600 px-3 py-1 rounded-full">PDF Document</span>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Sidebar - Purchase Card -->
        <div>
            <div class="bg-white rounded-2xl shadow-sm p-6 sticky top-24">
                <?php if ($doc['type'] == 'premium'): ?>
                <div class="text-center mb-4">
                    <div class="inline-flex items-center gap-1 bg-yellow-100 text-yellow-700 px-3 py-1 rounded-full text-sm">
                        <i class="fas fa-crown"></i> Premium Resource
                    </div>
                </div>
                <?php endif; ?>
                
                <div class="text-center mb-6">
                    <?php if ($doc['price'] > 0): ?>
                    <p class="text-3xl font-bold text-indigo-600">UGX <?php echo number_format($doc['price'], 0); ?></p>
                    <p class="text-xs text-gray-500 mt-1">One-time payment • Instant access</p>
                    <?php else: ?>
                    <p class="text-3xl font-bold text-green-600">FREE</p>
                    <p class="text-xs text-gray-500 mt-1">No payment required</p>
                    <?php endif; ?>
                </div>
                
                <button onclick="handlePurchase(<?php echo $doc['id']; ?>, '<?php echo $doc['type']; ?>', <?php echo $doc['price']; ?>)" 
                        class="w-full bg-gradient-to-r from-indigo-600 to-purple-600 text-white py-3 rounded-xl hover:from-indigo-700 hover:to-purple-700 transition font-semibold flex items-center justify-center gap-2">
                    <i class="fas <?php echo $doc['type'] == 'free' ? 'fa-download' : 'fa-shopping-cart'; ?>"></i>
                    <?php echo $doc['type'] == 'free' ? 'Download Now' : 'Purchase Now'; ?>
                </button>
                
                <div class="mt-6 space-y-3 text-sm text-gray-600">
                    <div class="flex items-center gap-3">
                        <i class="fas fa-check-circle text-green-500 w-5"></i>
                        <span>Instant download after purchase</span>
                    </div>
                    <div class="flex items-center gap-3">
                        <i class="fas fa-shield-alt text-green-500 w-5"></i>
                        <span>Secure payment processing</span>
                    </div>
                    <div class="flex items-center gap-3">
                        <i class="fas fa-headset text-green-500 w-5"></i>
                        <span>24/7 customer support</span>
                    </div>
                </div>
                
                <div class="mt-6 pt-6 border-t border-gray-100">
                    <div class="flex items-center justify-between text-sm">
                        <span class="text-gray-500"><i class="fas fa-download mr-1"></i> Downloads</span>
                        <span class="font-medium"><?php echo number_format($doc['downloads_count']); ?></span>
                    </div>
                    <div class="flex items-center justify-between text-sm mt-2">
                        <span class="text-gray-500"><i class="fas fa-eye mr-1"></i> Views</span>
                        <span class="font-medium"><?php echo number_format($doc['views_count']); ?></span>
                    </div>
                </div>
            </div>
            
            <!-- Share Section -->
            <div class="bg-white rounded-2xl shadow-sm p-6 mt-6">
                <h4 class="font-semibold text-gray-800 mb-3">Share This Resource</h4>
                <div class="flex gap-3">
                    <button onclick="shareDocument('facebook')" class="flex-1 bg-blue-600 text-white py-2 rounded-lg hover:bg-blue-700 transition">
                        <i class="fab fa-facebook-f"></i>
                    </button>
                    <button onclick="shareDocument('twitter')" class="flex-1 bg-blue-400 text-white py-2 rounded-lg hover:bg-blue-500 transition">
                        <i class="fab fa-twitter"></i>
                    </button>
                    <button onclick="shareDocument('whatsapp')" class="flex-1 bg-green-500 text-white py-2 rounded-lg hover:bg-green-600 transition">
                        <i class="fab fa-whatsapp"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Related Documents -->
    <?php if (count($relatedDocs) > 0): ?>
    <div class="mt-12">
        <h3 class="text-xl font-bold text-gray-800 mb-6">You May Also Like</h3>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
            <?php foreach ($relatedDocs as $related): ?>
            <div class="bg-white rounded-xl shadow-sm p-4 hover:shadow-md transition">
                <div class="flex items-center gap-2 mb-2">
                    <i class="fas fa-file-pdf text-indigo-600"></i>
                    <span class="text-xs font-bold bg-gray-100 text-gray-600 px-2 py-0.5 rounded">
                        <?php echo ucfirst($related['type']); ?>
                    </span>
                </div>
                <a href="/document.php?id=<?php echo $related['id']; ?>" class="font-medium text-gray-800 hover:text-indigo-600 line-clamp-2">
                    <?php echo htmlspecialchars($related['title']); ?>
                </a>
                <div class="mt-2 flex items-center justify-between">
                    <span class="text-sm font-bold text-indigo-600">
                        <?php echo $related['price'] > 0 ? 'UGX ' . number_format($related['price'], 0) : 'FREE'; ?>
                    </span>
                    <span class="text-xs text-gray-400">
                        <i class="fas fa-download mr-1"></i> <?php echo number_format($related['downloads_count']); ?>
                    </span>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>
</div>

<script>
function handlePurchase(docId, type, price) {
    <?php if (!isLoggedIn()): ?>
    if (confirm('Please login to ' + (type === 'free' ? 'download' : 'purchase') + ' this document. Login now?')) {
        window.location.href = '/auth/login.php?redirect=/document.php?id=' + docId;
    }
    <?php else: ?>
    if (type === 'free') {
        window.location.href = '/api/download.php?id=' + docId;
    } else {
        window.location.href = '/checkout.php?doc_id=' + docId;
    }
    <?php endif; ?>
}

function shareDocument(platform) {
    const url = encodeURIComponent(window.location.href);
    const title = encodeURIComponent(document.title);
    
    let shareUrl = '';
    switch(platform) {
        case 'facebook':
            shareUrl = `https://www.facebook.com/sharer/sharer.php?u=${url}`;
            break;
        case 'twitter':
            shareUrl = `https://twitter.com/intent/tweet?url=${url}&text=${title}`;
            break;
        case 'whatsapp':
            shareUrl = `https://wa.me/?text=${title}%20${url}`;
            break;
    }
    
    window.open(shareUrl, '_blank', 'width=600,height=400');
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
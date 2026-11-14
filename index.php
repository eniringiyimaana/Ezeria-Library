<?php
require_once 'config/database.php';
require_once 'includes/functions.php';

$database = new Database();
$db = $database->getConnection();

$pageTitle = "Home";

// Get recent documents
$recentQuery = "SELECT d.*, u.username as uploader_name, l.name as level_name, c.name as category_name 
                FROM documents d 
                LEFT JOIN users u ON d.uploaded_by = u.id 
                LEFT JOIN levels l ON d.level_id = l.id 
                LEFT JOIN categories c ON d.category_id = c.id 
                WHERE d.status = 'approved' 
                ORDER BY d.created_at DESC 
                LIMIT 12";
$recentStmt = $db->prepare($recentQuery);
$recentStmt->execute();
$recentDocs = $recentStmt->fetchAll(PDO::FETCH_ASSOC);

// Get most downloaded documents
$popularQuery = "SELECT d.*, u.username as uploader_name, l.name as level_name, c.name as category_name 
                 FROM documents d 
                 LEFT JOIN users u ON d.uploaded_by = u.id 
                 LEFT JOIN levels l ON d.level_id = l.id 
                 LEFT JOIN categories c ON d.category_id = c.id 
                 WHERE d.status = 'approved' 
                 ORDER BY d.downloads_count DESC 
                 LIMIT 12";
$popularStmt = $db->prepare($popularQuery);
$popularStmt->execute();
$popularDocs = $popularStmt->fetchAll(PDO::FETCH_ASSOC);

// Get premium documents
$premiumQuery = "SELECT d.*, u.username as uploader_name, l.name as level_name, c.name as category_name 
                 FROM documents d 
                 LEFT JOIN users u ON d.uploaded_by = u.id 
                 LEFT JOIN levels l ON d.level_id = l.id 
                 LEFT JOIN categories c ON d.category_id = c.id 
                 WHERE d.status = 'approved' AND d.type = 'premium' 
                 ORDER BY d.created_at DESC 
                 LIMIT 12";
$premiumStmt = $db->prepare($premiumQuery);
$premiumStmt->execute();
$premiumDocs = $premiumStmt->fetchAll(PDO::FETCH_ASSOC);

// Get books
$booksQuery = "SELECT d.*, u.username as uploader_name, l.name as level_name, c.name as category_name 
               FROM documents d 
               LEFT JOIN users u ON d.uploaded_by = u.id 
               LEFT JOIN levels l ON d.level_id = l.id 
               LEFT JOIN categories c ON d.category_id = c.id 
               WHERE d.status = 'approved' AND d.type = 'book' 
               ORDER BY d.created_at DESC 
               LIMIT 12";
$booksStmt = $db->prepare($booksQuery);
$booksStmt->execute();
$books = $booksStmt->fetchAll(PDO::FETCH_ASSOC);

// Get all levels for sidebar
$levelsQuery = "SELECT * FROM levels WHERE parent_id IS NULL ORDER BY sort_order";
$levelsStmt = $db->prepare($levelsQuery);
$levelsStmt->execute();
$levels = $levelsStmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($levels as &$level) {
    $subQuery = "SELECT * FROM levels WHERE parent_id = :parent_id ORDER BY name";
    $subStmt = $db->prepare($subQuery);
    $subStmt->bindParam(':parent_id', $level['id']);
    $subStmt->execute();
    $level['children'] = $subStmt->fetchAll(PDO::FETCH_ASSOC);
}

include 'includes/header.php';
?>

<!-- Hero Section -->
<section class="hero-gradient py-20 relative">
    <div class="relative z-10 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <h1 class="text-4xl md:text-5xl font-bold text-white mb-4">Discover Knowledge Without Limits</h1>
        <p class="text-xl text-indigo-100 mb-8">Access millions of educational resources, books, and research materials</p>
        
        <div class="max-w-2xl mx-auto">
            <form action="/ezeria-library/search.php" method="GET" class="flex shadow-2xl rounded-full overflow-hidden">
                <input type="text" name="q" placeholder="Search 1,000,000+ files in 7,800+ subjects..." 
                       class="flex-1 px-6 py-4 text-gray-700 focus:outline-none text-sm">
                <button type="submit" class="btn-primary text-white px-8 py-4 transition flex items-center gap-2 font-medium">
                    <i class="fas fa-search"></i> Search
                </button>
            </form>
        </div>
    </div>
</section>

<!-- Main Content -->
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="flex flex-col lg:flex-row gap-8">
        
        <!-- Sidebar -->
        <div class="lg:w-80 flex-shrink-0">
            <?php include 'includes/sidebar.php'; ?>
        </div>
        
        <!-- Main Content Area -->
        <div class="flex-1 space-y-10">
            
            <!-- Recent Documents -->
            <div>
                <div class="flex justify-between items-center mb-4">
                    <h2 class="text-2xl font-bold text-gray-800 flex items-center gap-2">
                        <i class="fas fa-clock text-indigo-600"></i> Recent Documents
                    </h2>
                    <a href="/ezeria-library/recent.php" class="text-indigo-600 hover:text-indigo-700 text-sm font-medium hover:underline">View all →</a>
                </div>
                <div class="relative">
                    <div class="scroll-container" id="recentScroll">
                        <div class="scroll-inner">
                            <?php foreach ($recentDocs as $doc): ?>
                            <div class="scroll-item">
                                <div class="bg-white rounded-xl shadow-md overflow-hidden hover:shadow-xl transition-all h-full">
                                    <div class="h-2 bg-gradient-to-r from-indigo-500 to-purple-500"></div>
                                    <div class="p-4">
                                        <div class="flex items-center justify-between mb-3">
                                            <div class="flex items-center gap-2">
                                                <div class="w-8 h-8 bg-indigo-100 rounded-lg flex items-center justify-center">
                                                    <i class="fas fa-file-pdf text-indigo-600 text-sm"></i>
                                                </div>
                                                <span class="text-xs font-bold text-gray-500 uppercase">PDF</span>
                                            </div>
                                            <span class="text-[10px] font-bold bg-indigo-100 text-indigo-700 px-2 py-1 rounded-full">
                                                <?php echo ucfirst($doc['type']); ?>
                                            </span>
                                        </div>
                                        <a href="/ezeria-library/document.php?id=<?php echo $doc['id']; ?>" 
                                           class="block font-bold text-sm text-gray-800 hover:text-indigo-600 leading-tight line-clamp-2 mb-2">
                                            <?php echo htmlspecialchars(substr($doc['title'], 0, 60)); ?>
                                        </a>
                                        <div class="space-y-1 text-xs text-gray-500">
                                            <p><i class="fas fa-book-open w-3"></i> <?php echo htmlspecialchars($doc['category_name'] ?? 'General'); ?></p>
                                            <p><i class="fas fa-user w-3"></i> <?php echo htmlspecialchars($doc['uploader_name'] ?? 'Anonymous'); ?></p>
                                            <p><i class="fas fa-calendar w-3"></i> <?php echo date('M j, Y', strtotime($doc['created_at'])); ?></p>
                                            <p><i class="fas fa-graduation-cap w-3"></i> <?php echo htmlspecialchars($doc['level_name'] ?? 'All Levels'); ?></p>
                                        </div>
                                        <div class="flex items-center justify-between text-xs text-gray-400 pt-3 mt-2 border-t border-gray-100">
                                            <span><i class="far fa-clock mr-1"></i> <?php echo getTimeAgo($doc['created_at']); ?></span>
                                            <span><i class="fas fa-download mr-1"></i> <?php echo number_format($doc['downloads_count']); ?></span>
                                            <span><i class="fas fa-file-alt mr-1"></i> <?php echo $doc['pages']; ?>p</span>
                                        </div>
                                        <button onclick="downloadDocument(<?php echo $doc['id']; ?>)" 
                                                class="mt-3 w-full btn-primary text-white text-xs font-semibold py-2 rounded-lg transition flex items-center justify-center gap-1">
                                            <i class="fas fa-download"></i> Download
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <button onclick="scrollLeft('recentScroll')" class="absolute left-0 top-1/2 transform -translate-y-1/2 bg-white rounded-full shadow-lg p-2 text-indigo-600 hover:bg-indigo-600 hover:text-white transition">
                        <i class="fas fa-chevron-left"></i>
                    </button>
                    <button onclick="scrollRight('recentScroll')" class="absolute right-0 top-1/2 transform -translate-y-1/2 bg-white rounded-full shadow-lg p-2 text-indigo-600 hover:bg-indigo-600 hover:text-white transition">
                        <i class="fas fa-chevron-right"></i>
                    </button>
                </div>
            </div>
            
            <!-- Most Downloaded -->
            <div>
                <div class="flex justify-between items-center mb-4">
                    <h2 class="text-2xl font-bold text-gray-800 flex items-center gap-2">
                        <i class="fas fa-chart-line text-indigo-600"></i> Most Downloaded
                    </h2>
                    <a href="/ezeria-library/popular.php" class="text-indigo-600 hover:text-indigo-700 text-sm font-medium hover:underline">View all →</a>
                </div>
                <div class="relative">
                    <div class="scroll-container" id="popularScroll">
                        <div class="scroll-inner">
                            <?php foreach ($popularDocs as $doc): ?>
                            <div class="scroll-item">
                                <div class="bg-white rounded-xl shadow-md overflow-hidden hover:shadow-xl transition-all h-full">
                                    <div class="h-2 bg-gradient-to-r from-indigo-500 to-purple-500"></div>
                                    <div class="p-4">
                                        <div class="flex items-center justify-between mb-3">
                                            <div class="flex items-center gap-2">
                                                <div class="w-8 h-8 bg-indigo-100 rounded-lg flex items-center justify-center">
                                                    <i class="fas fa-file-pdf text-indigo-600 text-sm"></i>
                                                </div>
                                                <span class="text-xs font-bold text-gray-500 uppercase">PDF</span>
                                            </div>
                                            <span class="text-[10px] font-bold bg-indigo-100 text-indigo-700 px-2 py-1 rounded-full">
                                                <?php echo ucfirst($doc['type']); ?>
                                            </span>
                                        </div>
                                        <a href="/ezeria-library/document.php?id=<?php echo $doc['id']; ?>" 
                                           class="block font-bold text-sm text-gray-800 hover:text-indigo-600 leading-tight line-clamp-2 mb-2">
                                            <?php echo htmlspecialchars(substr($doc['title'], 0, 60)); ?>
                                        </a>
                                        <div class="space-y-1 text-xs text-gray-500">
                                            <p><i class="fas fa-book-open w-3"></i> <?php echo htmlspecialchars($doc['category_name'] ?? 'General'); ?></p>
                                            <p><i class="fas fa-user w-3"></i> <?php echo htmlspecialchars($doc['uploader_name'] ?? 'Anonymous'); ?></p>
                                            <p><i class="fas fa-calendar w-3"></i> <?php echo date('M j, Y', strtotime($doc['created_at'])); ?></p>
                                            <p><i class="fas fa-graduation-cap w-3"></i> <?php echo htmlspecialchars($doc['level_name'] ?? 'All Levels'); ?></p>
                                        </div>
                                        <div class="flex items-center justify-between text-xs text-gray-400 pt-3 mt-2 border-t border-gray-100">
                                            <span><i class="fas fa-download mr-1"></i> <?php echo number_format($doc['downloads_count']); ?></span>
                                            <span><i class="fas fa-eye mr-1"></i> <?php echo number_format($doc['views_count']); ?></span>
                                            <span><i class="fas fa-file-alt mr-1"></i> <?php echo $doc['pages']; ?>p</span>
                                        </div>
                                        <button onclick="downloadDocument(<?php echo $doc['id']; ?>)" 
                                                class="mt-3 w-full btn-primary text-white text-xs font-semibold py-2 rounded-lg transition flex items-center justify-center gap-1">
                                            <i class="fas fa-download"></i> Download
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <button onclick="scrollLeft('popularScroll')" class="absolute left-0 top-1/2 transform -translate-y-1/2 bg-white rounded-full shadow-lg p-2 text-indigo-600 hover:bg-indigo-600 hover:text-white transition">
                        <i class="fas fa-chevron-left"></i>
                    </button>
                    <button onclick="scrollRight('popularScroll')" class="absolute right-0 top-1/2 transform -translate-y-1/2 bg-white rounded-full shadow-lg p-2 text-indigo-600 hover:bg-indigo-600 hover:text-white transition">
                        <i class="fas fa-chevron-right"></i>
                    </button>
                </div>
            </div>
            
            <!-- Premium Documents -->
            <div>
                <div class="flex justify-between items-center mb-4">
                    <h2 class="text-2xl font-bold text-gray-800 flex items-center gap-2">
                        <i class="fas fa-crown text-yellow-500"></i> Premium Documents
                    </h2>
                    <a href="/ezeria-library/premium-resources.php" class="text-indigo-600 hover:text-indigo-700 text-sm font-medium hover:underline">View all →</a>
                </div>
                <div class="relative">
                    <div class="scroll-container" id="premiumScroll">
                        <div class="scroll-inner">
                            <?php foreach ($premiumDocs as $doc): ?>
                            <div class="scroll-item">
                                <div class="bg-gradient-to-br from-yellow-50 to-amber-50 rounded-xl shadow-md overflow-hidden hover:shadow-xl transition-all h-full border border-yellow-200">
                                    <div class="h-2 bg-gradient-to-r from-yellow-500 to-amber-500"></div>
                                    <div class="p-4">
                                        <div class="flex items-center justify-between mb-3">
                                            <div class="flex items-center gap-2">
                                                <div class="w-8 h-8 bg-yellow-100 rounded-lg flex items-center justify-center">
                                                    <i class="fas fa-crown text-yellow-600 text-sm"></i>
                                                </div>
                                                <span class="text-xs font-bold text-yellow-700">PREMIUM</span>
                                            </div>
                                            <span class="text-[10px] font-bold bg-yellow-200 text-yellow-800 px-2 py-1 rounded-full">
                                                <?php echo ucfirst($doc['type']); ?>
                                            </span>
                                        </div>
                                        <a href="/ezeria-library/document.php?id=<?php echo $doc['id']; ?>" 
                                           class="block font-bold text-sm text-gray-800 hover:text-indigo-600 leading-tight line-clamp-2 mb-2">
                                            <?php echo htmlspecialchars(substr($doc['title'], 0, 60)); ?>
                                        </a>
                                        <div class="space-y-1 text-xs text-gray-500">
                                            <p><i class="fas fa-book-open w-3"></i> <?php echo htmlspecialchars($doc['category_name'] ?? 'General'); ?></p>
                                            <p><i class="fas fa-user w-3"></i> <?php echo htmlspecialchars($doc['uploader_name'] ?? 'Anonymous'); ?></p>
                                            <p><i class="fas fa-graduation-cap w-3"></i> <?php echo htmlspecialchars($doc['level_name'] ?? 'All Levels'); ?></p>
                                        </div>
                                        <div class="flex items-center justify-between text-xs text-gray-400 pt-3 mt-2 border-t border-yellow-200">
                                            <span><i class="far fa-clock mr-1"></i> <?php echo getTimeAgo($doc['created_at']); ?></span>
                                            <span><i class="fas fa-download mr-1"></i> <?php echo number_format($doc['downloads_count']); ?></span>
                                            <span><i class="fas fa-file-alt mr-1"></i> <?php echo $doc['pages']; ?>p</span>
                                        </div>
                                        <div class="mt-3 flex gap-2">
                                            <button onclick="shareDocument(<?php echo $doc['id']; ?>)" class="flex-1 bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs font-semibold py-2 rounded-lg transition"><i class="fas fa-share-alt"></i> Share</button>
                                            <button onclick="purchaseDocument(<?php echo $doc['id']; ?>)" class="flex-1 bg-gradient-to-r from-yellow-500 to-amber-500 text-white text-xs font-semibold py-2 rounded-lg hover:from-yellow-600 hover:to-amber-600 transition flex items-center justify-center gap-1"><i class="fas fa-shopping-cart"></i> Buy UGX <?php echo number_format($doc['price'], 0); ?></button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <button onclick="scrollLeft('premiumScroll')" class="absolute left-0 top-1/2 transform -translate-y-1/2 bg-white rounded-full shadow-lg p-2 text-yellow-600 hover:bg-yellow-600 hover:text-white transition"><i class="fas fa-chevron-left"></i></button>
                    <button onclick="scrollRight('premiumScroll')" class="absolute right-0 top-1/2 transform -translate-y-1/2 bg-white rounded-full shadow-lg p-2 text-yellow-600 hover:bg-yellow-600 hover:text-white transition"><i class="fas fa-chevron-right"></i></button>
                </div>
            </div>
            
            <!-- Book Store -->
            <div>
                <div class="flex justify-between items-center mb-4">
                    <h2 class="text-2xl font-bold text-gray-800 flex items-center gap-2">
                        <i class="fas fa-book text-indigo-600"></i> Book Store
                    </h2>
                    <a href="/ezeria-library/book-store.php" class="text-indigo-600 hover:text-indigo-700 text-sm font-medium hover:underline">View all →</a>
                </div>
                <div class="relative">
                    <div class="scroll-container" id="bookScroll">
                        <div class="scroll-inner">
                            <?php foreach ($books as $book): ?>
                            <div class="scroll-item">
                                <div class="bg-white rounded-xl shadow-md overflow-hidden hover:shadow-xl transition-all h-full">
                                    <div class="h-2 bg-gradient-to-r from-indigo-500 to-purple-500"></div>
                                    <div class="p-4">
                                        <div class="flex items-center gap-2 mb-3">
                                            <div class="w-10 h-10 bg-indigo-100 rounded-lg flex items-center justify-center"><i class="fas fa-book-open text-indigo-600 text-lg"></i></div>
                                            <span class="text-xs font-bold text-gray-500 uppercase">BOOK</span>
                                        </div>
                                        <a href="/ezeria-library/document.php?id=<?php echo $book['id']; ?>" class="block font-bold text-sm text-gray-800 hover:text-indigo-600 leading-tight line-clamp-2 mb-2"><?php echo htmlspecialchars(substr($book['title'], 0, 60)); ?></a>
                                        <p class="text-xs text-gray-600 mb-1"><i class="fas fa-user mr-1 text-gray-400"></i> <?php echo htmlspecialchars($book['uploader_name'] ?? 'Ezeria Publishing'); ?></p>
                                        <p class="text-xs font-bold text-indigo-600 mb-2"><i class="fas fa-tag mr-1"></i> <?php echo htmlspecialchars($book['category_name'] ?? 'General'); ?></p>
                                        <div class="flex items-center justify-between mt-3 pt-2 border-t border-gray-100">
                                            <span class="text-lg font-bold text-indigo-600">UGX <?php echo number_format($book['price'], 0); ?></span>
                                            <button onclick="buyBook(<?php echo $book['id']; ?>)" class="btn-primary text-white text-xs font-semibold px-4 py-2 rounded-lg transition flex items-center gap-1"><i class="fas fa-shopping-cart"></i> Buy Now</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <button onclick="scrollLeft('bookScroll')" class="absolute left-0 top-1/2 transform -translate-y-1/2 bg-white rounded-full shadow-lg p-2 text-indigo-600 hover:bg-indigo-600 hover:text-white transition"><i class="fas fa-chevron-left"></i></button>
                    <button onclick="scrollRight('bookScroll')" class="absolute right-0 top-1/2 transform -translate-y-1/2 bg-white rounded-full shadow-lg p-2 text-indigo-600 hover:bg-indigo-600 hover:text-white transition"><i class="fas fa-chevron-right"></i></button>
                </div>
            </div>
            
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
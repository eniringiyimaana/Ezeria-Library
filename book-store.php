<?php
require_once 'config/database.php';
require_once 'includes/functions.php';

$database = new Database();
$db = $database->getConnection();

// Get books
$booksQuery = "SELECT d.*, u.username as uploader, l.name as level_name, c.name as category_name 
               FROM documents d 
               LEFT JOIN users u ON d.uploaded_by = u.id 
               LEFT JOIN levels l ON d.level_id = l.id 
               LEFT JOIN categories c ON d.category_id = c.id 
               WHERE d.status = 'approved' AND d.type = 'book' 
               ORDER BY d.created_at DESC";
$booksStmt = $db->prepare($booksQuery);
$booksStmt->execute();
$books = $booksStmt->fetchAll(PDO::FETCH_ASSOC);

// Get categories for filtering
$categoriesQuery = "SELECT * FROM categories ORDER BY name";
$categoriesStmt = $db->prepare($categoriesQuery);
$categoriesStmt->execute();
$categories = $categoriesStmt->fetchAll(PDO::FETCH_ASSOC);

// Get levels for filtering
$levelsQuery = "SELECT * FROM levels WHERE parent_id IS NULL ORDER BY sort_order";
$levelsStmt = $db->prepare($levelsQuery);
$levelsStmt->execute();
$levels = $levelsStmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book Store - Ezeria Library</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { font-family: 'Inter', sans-serif; }
        .book-card {
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        .book-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 25px -12px rgba(0, 0, 0, 0.2);
        }
        .filter-sidebar {
            position: sticky;
            top: 80px;
        }
    </style>
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
                <a href="/book-store.php" class="bg-white/20 px-4 py-2 rounded-full hover:bg-white/30 transition">Bookstore</a>
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

<!-- Hero Section -->
<section class="bg-gradient-to-r from-indigo-600 to-purple-600 py-12">
    <div class="max-w-7xl mx-auto px-4 text-center text-white">
        <h1 class="text-3xl md:text-4xl font-bold mb-3">Book Store</h1>
        <p class="text-indigo-100">Discover educational books for all levels and subjects</p>
    </div>
</section>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="flex flex-col lg:flex-row gap-8">
        
        <!-- Filters Sidebar -->
        <div class="lg:w-72 flex-shrink-0">
            <div class="filter-sidebar bg-white rounded-2xl shadow-sm p-5">
                <h3 class="font-bold text-gray-800 mb-4 flex items-center gap-2">
                    <i class="fas fa-filter text-indigo-600"></i> Filters
                </h3>
                
                <div class="mb-5">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Search Books</label>
                    <input type="text" id="searchInput" placeholder="Search by title, author..." 
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-indigo-500 focus:border-indigo-500">
                </div>
                
                <div class="mb-5">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Category</label>
                    <select id="categoryFilter" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="all">All Categories</option>
                        <?php foreach ($categories as $category): ?>
                        <option value="<?php echo $category['id']; ?>"><?php echo htmlspecialchars($category['name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="mb-5">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Education Level</label>
                    <select id="levelFilter" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="all">All Levels</option>
                        <?php foreach ($levels as $level): ?>
                        <option value="<?php echo $level['id']; ?>"><?php echo htmlspecialchars($level['name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="mb-5">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Price Range</label>
                    <div class="flex gap-2">
                        <input type="number" id="minPrice" placeholder="Min" class="w-1/2 border border-gray-300 rounded-lg px-3 py-2 text-sm">
                        <input type="number" id="maxPrice" placeholder="Max" class="w-1/2 border border-gray-300 rounded-lg px-3 py-2 text-sm">
                    </div>
                </div>
                
                <button onclick="applyFilters()" class="w-full bg-gradient-to-r from-indigo-600 to-purple-600 text-white py-2 rounded-lg hover:from-indigo-700 hover:to-purple-700 transition">
                    <i class="fas fa-search mr-2"></i> Apply Filters
                </button>
                
                <button onclick="resetFilters()" class="w-full mt-2 text-indigo-600 text-sm hover:underline">
                    Reset All Filters
                </button>
            </div>
        </div>
        
        <!-- Books Grid -->
        <div class="flex-1">
            <div class="flex justify-between items-center mb-6">
                <p id="resultsCount" class="text-gray-600">Loading books...</p>
                <select id="sortBy" class="border border-gray-300 rounded-lg px-3 py-1.5 text-sm focus:ring-indigo-500 focus:border-indigo-500">
                    <option value="newest">Newest First</option>
                    <option value="price_low">Price: Low to High</option>
                    <option value="price_high">Price: High to Low</option>
                    <option value="popular">Most Popular</option>
                </select>
            </div>
            
            <div id="booksGrid" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <?php foreach ($books as $book): ?>
                <div class="book-card bg-white rounded-xl shadow-sm overflow-hidden" data-category="<?php echo $book['category_id']; ?>" data-level="<?php echo $book['level_id']; ?>" data-price="<?php echo $book['price']; ?>" data-title="<?php echo strtolower($book['title']); ?>" data-author="<?php echo strtolower($book['uploader']); ?>">
                    <div class="h-2 bg-gradient-to-r from-indigo-500 to-purple-500"></div>
                    <div class="p-5">
                        <div class="flex items-start justify-between mb-3">
                            <div class="flex items-center gap-2">
                                <div class="w-10 h-10 bg-indigo-100 rounded-lg flex items-center justify-center">
                                    <i class="fas fa-book-open text-indigo-600 text-lg"></i>
                                </div>
                                <div>
                                    <span class="text-xs font-bold bg-blue-100 text-blue-700 px-2 py-0.5 rounded-full">BOOK</span>
                                    <?php if ($book['price'] > 0): ?>
                                    <span class="text-xs font-bold bg-green-100 text-green-700 px-2 py-0.5 rounded-full ml-1">INSTANT ACCESS</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        
                        <a href="/document.php?id=<?php echo $book['id']; ?>" class="block font-bold text-gray-800 hover:text-indigo-600 leading-tight mb-2 line-clamp-2">
                            <?php echo htmlspecialchars($book['title']); ?>
                        </a>
                        
                        <p class="text-sm text-gray-600 mb-1">
                            <i class="fas fa-user mr-1 text-gray-400"></i> <?php echo htmlspecialchars($book['uploader'] ?? 'Ezeria Publishing'); ?>
                        </p>
                        
                        <div class="flex flex-wrap gap-1 mb-3">
                            <?php if ($book['category_name']): ?>
                            <span class="text-xs bg-gray-100 text-gray-600 px-2 py-0.5 rounded"><?php echo htmlspecialchars($book['category_name']); ?></span>
                            <?php endif; ?>
                            <?php if ($book['level_name']): ?>
                            <span class="text-xs bg-gray-100 text-gray-600 px-2 py-0.5 rounded"><?php echo htmlspecialchars($book['level_name']); ?></span>
                            <?php endif; ?>
                        </div>
                        
                        <div class="flex items-center justify-between mt-4">
                            <div>
                                <?php if ($book['price'] > 0): ?>
                                <span class="text-2xl font-bold text-indigo-600">UGX <?php echo number_format($book['price'], 0); ?></span>
                                <?php else: ?>
                                <span class="text-2xl font-bold text-green-600">FREE</span>
                                <?php endif; ?>
                            </div>
                            <button onclick="buyBook(<?php echo $book['id']; ?>, <?php echo $book['price']; ?>)" 
                                    class="bg-gradient-to-r from-indigo-600 to-purple-600 text-white px-4 py-2 rounded-lg hover:from-indigo-700 hover:to-purple-700 transition text-sm font-medium flex items-center gap-1">
                                <i class="fas fa-shopping-cart"></i> Buy Now
                            </button>
                        </div>
                        
                        <div class="mt-3 pt-3 border-t border-gray-100 flex items-center justify-between text-xs text-gray-400">
                            <span><i class="far fa-calendar-alt mr-1"></i> <?php echo date('M j, Y', strtotime($book['created_at'])); ?></span>
                            <span><i class="fas fa-download mr-1"></i> <?php echo number_format($book['downloads_count']); ?> downloads</span>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            
            <?php if (count($books) == 0): ?>
            <div class="bg-white rounded-2xl shadow-sm p-12 text-center">
                <i class="fas fa-book-open text-6xl text-gray-300 mb-4"></i>
                <h3 class="text-xl font-semibold text-gray-800 mb-2">No books available yet</h3>
                <p class="text-gray-500">Check back soon for new arrivals!</p>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
function applyFilters() {
    const searchTerm = document.getElementById('searchInput').value.toLowerCase();
    const category = document.getElementById('categoryFilter').value;
    const level = document.getElementById('levelFilter').value;
    const minPrice = document.getElementById('minPrice').value;
    const maxPrice = document.getElementById('maxPrice').value;
    const sortBy = document.getElementById('sortBy').value;
    
    const books = document.querySelectorAll('#booksGrid .book-card');
    let visibleCount = 0;
    let booksArray = [];
    
    books.forEach(book => {
        let show = true;
        
        // Search filter
        if (searchTerm) {
            const title = book.dataset.title || '';
            const author = book.dataset.author || '';
            if (!title.includes(searchTerm) && !author.includes(searchTerm)) {
                show = false;
            }
        }
        
        // Category filter
        if (show && category !== 'all' && book.dataset.category !== category) {
            show = false;
        }
        
        // Level filter
        if (show && level !== 'all' && book.dataset.level !== level) {
            show = false;
        }
        
        // Price filter
        const price = parseFloat(book.dataset.price) || 0;
        if (show && minPrice && price < parseFloat(minPrice)) show = false;
        if (show && maxPrice && price > parseFloat(maxPrice)) show = false;
        
        if (show) {
            book.style.display = 'block';
            visibleCount++;
            booksArray.push(book);
        } else {
            book.style.display = 'none';
        }
    });
    
    // Sort
    if (sortBy === 'price_low') {
        booksArray.sort((a, b) => parseFloat(a.dataset.price) - parseFloat(b.dataset.price));
    } else if (sortBy === 'price_high') {
        booksArray.sort((a, b) => parseFloat(b.dataset.price) - parseFloat(a.dataset.price));
    } else if (sortBy === 'newest') {
        // Keep original order
    }
    
    // Reorder DOM
    const grid = document.getElementById('booksGrid');
    booksArray.forEach(book => grid.appendChild(book));
    
    document.getElementById('resultsCount').innerHTML = `Showing ${visibleCount} book${visibleCount !== 1 ? 's' : ''}`;
}

function resetFilters() {
    document.getElementById('searchInput').value = '';
    document.getElementById('categoryFilter').value = 'all';
    document.getElementById('levelFilter').value = 'all';
    document.getElementById('minPrice').value = '';
    document.getElementById('maxPrice').value = '';
    document.getElementById('sortBy').value = 'newest';
    applyFilters();
}

function buyBook(bookId, price) {
    <?php if (!isLoggedIn()): ?>
    if (confirm('Please login to purchase this book. Login now?')) {
        window.location.href = '/auth/login.php';
    }
    <?php else: ?>
    window.location.href = '/checkout.php?book_id=' + bookId;
    <?php endif; ?>
}

// Event listeners
document.getElementById('searchInput').addEventListener('keyup', applyFilters);
document.getElementById('categoryFilter').addEventListener('change', applyFilters);
document.getElementById('levelFilter').addEventListener('change', applyFilters);
document.getElementById('sortBy').addEventListener('change', applyFilters);

// Initial count
setTimeout(applyFilters, 100);
</script>

<!-- Footer -->
<footer class="bg-gray-900 text-white mt-12">
    <div class="max-w-7xl mx-auto px-4 py-8 text-center text-gray-400 text-sm">
        <p>&copy; <?php echo date('Y'); ?> Ezeria Library. All rights reserved.</p>
    </div>
</footer>

</body>
</html>
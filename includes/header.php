<?php
// No session_start() here - it's already in functions.php
require_once __DIR__ . '/functions.php';

$current_page = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? $pageTitle . ' - ' : ''; ?>Ezeria Library</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { font-family: 'Inter', sans-serif; }
        .gradient-nav { background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%); }
        .hero-gradient { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }
        .btn-primary { background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%); transition: all 0.3s ease; }
        .btn-primary:hover { background: linear-gradient(135deg, #4338ca 0%, #6d28d9 100%); transform: translateY(-2px); }
        .nav-link { transition: all 0.3s ease; }
        .nav-link:hover, .nav-link.active { background: rgba(255, 255, 255, 0.2); border-radius: 9999px; }
        .scroll-container { overflow-x: auto; scroll-behavior: smooth; -webkit-overflow-scrolling: touch; }
        .scroll-container::-webkit-scrollbar { height: 6px; }
        .scroll-container::-webkit-scrollbar-track { background: #e2e8f0; border-radius: 10px; }
        .scroll-container::-webkit-scrollbar-thumb { background: #4f46e5; border-radius: 10px; }
        .scroll-inner { display: flex; gap: 20px; padding: 10px 5px; }
        .scroll-item { flex: 0 0 280px; }
        .line-clamp-2 { display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }
    </style>
</head>
<body class="bg-gray-50">

<nav class="gradient-nav text-white shadow-lg sticky top-0 z-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center h-16">
            <div class="flex items-center space-x-3">
                <div class="h-10 w-10 bg-white/20 rounded-xl flex items-center justify-center">
                    <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                    </svg>
                </div>
                <span class="font-bold text-xl tracking-tight">Ezeria Library</span>
            </div>
            
            <div class="hidden md:flex items-center space-x-2">
                <a href="/ezeria-library/index.php" class="nav-link px-4 py-2 rounded-full transition <?php echo $current_page == 'index.php' ? 'bg-white/20' : ''; ?>">
                    <i class="fas fa-home mr-2"></i> Library
                </a>
                <a href="/ezeria-library/premium-resources.php" class="nav-link px-4 py-2 rounded-full transition <?php echo $current_page == 'premium-resources.php' ? 'bg-white/20' : ''; ?>">
                    <i class="fas fa-crown mr-2"></i> Premium
                </a>
                <a href="/ezeria-library/book-store.php" class="nav-link px-4 py-2 rounded-full transition <?php echo $current_page == 'book-store.php' ? 'bg-white/20' : ''; ?>">
                    <i class="fas fa-book mr-2"></i> Bookstore
                </a>
                <a href="/ezeria-library/ask-librarian.php" class="nav-link px-4 py-2 rounded-full transition <?php echo $current_page == 'ask-librarian.php' ? 'bg-white/20' : ''; ?>">
                    <i class="fas fa-question-circle mr-2"></i> Ask Librarian
                </a>
                
                <?php if (isLoggedIn()): ?>
                    <div class="relative group ml-2">
                        <button class="flex items-center space-x-2 bg-white/20 px-4 py-2 rounded-full hover:bg-white/30 transition">
                            <div class="h-8 w-8 bg-white/30 rounded-full flex items-center justify-center">
                                <i class="fas fa-user text-sm"></i>
                            </div>
                            <span><?php echo htmlspecialchars($_SESSION['username']); ?></span>
                            <i class="fas fa-chevron-down text-xs"></i>
                        </button>
                        <div class="absolute right-0 mt-2 w-56 bg-white rounded-lg shadow-xl py-2 hidden group-hover:block transition-all z-50">
                            <div class="px-4 py-3 border-b border-gray-100">
                                <p class="text-xs text-gray-500">Signed in as</p>
                                <p class="text-sm font-semibold text-gray-800 truncate"><?php echo htmlspecialchars($_SESSION['username']); ?></p>
                            </div>
                            <a href="/ezeria-library/user/dashboard.php" class="block px-4 py-2 text-gray-700 hover:bg-indigo-50">
                                <i class="fas fa-tachometer-alt mr-3 w-5 text-indigo-600"></i> Dashboard
                            </a>
                            <a href="/ezeria-library/user/profile.php" class="block px-4 py-2 text-gray-700 hover:bg-indigo-50">
                                <i class="fas fa-user-circle mr-3 w-5 text-indigo-600"></i> Profile
                            </a>
                            <hr class="my-1">
                            <a href="/ezeria-library/auth/logout.php" class="block px-4 py-2 text-red-600 hover:bg-red-50">
                                <i class="fas fa-sign-out-alt mr-3 w-5"></i> Logout
                            </a>
                        </div>
                    </div>
                <?php else: ?>
                    <a href="/ezeria-library/auth/login.php" class="bg-white/20 px-5 py-2 rounded-full hover:bg-white/30 transition ml-2">
                        <i class="fas fa-sign-in-alt mr-2"></i> Login / Sign Up
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</nav>
<?php
$currentYear = date('Y');
?>
<footer class="bg-gray-900 text-white mt-16">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
            <div>
                <div class="flex items-center gap-2 mb-4">
                    <div class="h-10 w-10 bg-indigo-500 rounded-xl flex items-center justify-center">
                        <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                        </svg>
                    </div>
                    <span class="font-bold text-xl">Ezeria Library</span>
                </div>
                <p class="text-gray-400 text-sm">Empowering education through digital resources since 2024.</p>
            </div>
            <div>
                <h4 class="font-bold text-lg mb-4">Quick Links</h4>
                <ul class="space-y-2 text-sm text-gray-400">
                    <li><a href="/ezeria-library/index.php" class="hover:text-indigo-400 transition">Library</a></li>
                    <li><a href="/ezeria-library/premium-resources.php" class="hover:text-indigo-400 transition">Premium Resources</a></li>
                    <li><a href="/ezeria-library/book-store.php" class="hover:text-indigo-400 transition">Book Store</a></li>
                    <li><a href="/ezeria-library/ask-librarian.php" class="hover:text-indigo-400 transition">Ask Librarian</a></li>
                </ul>
            </div>
            <div>
                <h4 class="font-bold text-lg mb-4">Resources</h4>
                <ul class="space-y-2 text-sm text-gray-400">
                    <li><a href="#" class="hover:text-indigo-400 transition">Textbooks</a></li>
                    <li><a href="#" class="hover:text-indigo-400 transition">Past Papers</a></li>
                    <li><a href="#" class="hover:text-indigo-400 transition">Research Papers</a></li>
                    <li><a href="#" class="hover:text-indigo-400 transition">Video Lectures</a></li>
                </ul>
            </div>
            <div>
                <h4 class="font-bold text-lg mb-4">Connect With Us</h4>
                <div class="flex space-x-3 mb-4">
                    <a href="#" class="w-10 h-10 bg-gray-800 rounded-full flex items-center justify-center hover:bg-indigo-600 transition"><i class="fab fa-facebook-f"></i></a>
                    <a href="#" class="w-10 h-10 bg-gray-800 rounded-full flex items-center justify-center hover:bg-indigo-600 transition"><i class="fab fa-twitter"></i></a>
                    <a href="#" class="w-10 h-10 bg-gray-800 rounded-full flex items-center justify-center hover:bg-indigo-600 transition"><i class="fab fa-linkedin-in"></i></a>
                    <a href="#" class="w-10 h-10 bg-gray-800 rounded-full flex items-center justify-center hover:bg-indigo-600 transition"><i class="fab fa-youtube"></i></a>
                </div>
                <p class="text-gray-400 text-sm">
                    <i class="fas fa-envelope mr-2"></i> info@ezerialibrary.com<br>
                    <i class="fas fa-phone mr-2"></i> +256 700 000 000
                </p>
            </div>
        </div>
        <div class="border-t border-gray-800 mt-8 pt-8 text-center text-gray-400 text-sm">
            <p>&copy; <?php echo $currentYear; ?> Ezeria Library. All rights reserved. Built with <i class="fas fa-heart text-red-500"></i> for education.</p>
        </div>
    </div>
</footer>

<script>
function scrollLeft(containerId) { const container = document.getElementById(containerId); if (container) container.scrollBy({ left: -300, behavior: 'smooth' }); }
function scrollRight(containerId) { const container = document.getElementById(containerId); if (container) container.scrollBy({ left: 300, behavior: 'smooth' }); }
function downloadDocument(docId) { <?php if (isLoggedIn()): ?>window.location.href = '/ezeria-library/api/download.php?id=' + docId;<?php else: ?>if (confirm('Please login to download this document. Login now?')) window.location.href = '/ezeria-library/auth/login.php';<?php endif; ?> }
function purchaseDocument(docId) { <?php if (isLoggedIn()): ?>window.location.href = '/ezeria-library/checkout.php?doc_id=' + docId;<?php else: ?>if (confirm('Please login to purchase this premium document. Login now?')) window.location.href = '/ezeria-library/auth/login.php';<?php endif; ?> }
function buyBook(bookId) { <?php if (isLoggedIn()): ?>window.location.href = '/ezeria-library/checkout.php?book_id=' + bookId;<?php else: ?>if (confirm('Please login to purchase this book. Login now?')) window.location.href = '/ezeria-library/auth/login.php';<?php endif; ?> }
function shareDocument(docId) { navigator.clipboard.writeText(window.location.origin + '/ezeria-library/document.php?id=' + docId); alert('Link copied to clipboard!'); }
</script>
</body>
</html>
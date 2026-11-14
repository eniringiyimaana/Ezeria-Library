<?php
require_once 'config/database.php';
require_once 'includes/functions.php';

$database = new Database();
$db = $database->getConnection();

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = sanitize($_POST['name']);
    $email = sanitize($_POST['email']);
    $phone = sanitize($_POST['phone']);
    $subject = sanitize($_POST['subject']);
    $messageText = sanitize($_POST['message']);
    
    $query = "INSERT INTO messages (name, email, phone, subject, message) 
              VALUES (:name, :email, :phone, :subject, :message)";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':name', $name);
    $stmt->bindParam(':email', $email);
    $stmt->bindParam(':phone', $phone);
    $stmt->bindParam(':subject', $subject);
    $stmt->bindParam(':message', $messageText);
    
    if ($stmt->execute()) {
        $message = "Your message has been sent! A librarian will respond within 24 hours.";
    } else {
        $error = "Failed to send message. Please try again.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ask Librarian - Ezeria Library</title>
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
                <div class="h-10 w-10 bg-white/20 rounded-xl flex items-center justify-center">
                    <i class="fas fa-book-open text-white"></i>
                </div>
                <span class="font-bold text-xl">Ezeria Library</span>
            </div>
            <div class="hidden md:flex items-center space-x-6">
                <a href="/index.php" class="hover:text-indigo-200 transition">Library</a>
                <a href="/premium-resources.php" class="hover:text-indigo-200 transition">Premium Resources</a>
                <a href="/book-store.php" class="hover:text-indigo-200 transition">Bookstore</a>
                <a href="/ask-librarian.php" class="bg-white/20 px-4 py-2 rounded-full hover:bg-white/30 transition">Ask Librarian</a>
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
<section class="bg-gradient-to-r from-indigo-600 to-purple-600 py-20">
    <div class="max-w-4xl mx-auto px-4 text-center text-white">
        <h1 class="text-4xl md:text-5xl font-bold mb-4">Ask a Librarian</h1>
        <p class="text-xl text-indigo-100">Get expert help with research, citations, and finding resources</p>
    </div>
</section>

<div class="max-w-6xl mx-auto px-4 py-12">
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        
        <!-- Contact Info Cards -->
        <div class="lg:col-span-1 space-y-6">
            <div class="bg-white rounded-2xl shadow-sm p-6">
                <div class="w-12 h-12 bg-indigo-100 rounded-full flex items-center justify-center mb-4">
                    <i class="fas fa-envelope text-indigo-600 text-xl"></i>
                </div>
                <h3 class="font-bold text-lg text-gray-800 mb-2">Email Us</h3>
                <p class="text-gray-600">Send us an email anytime</p>
                <a href="mailto:info@ezerialibrary.com" class="text-indigo-600 hover:underline mt-2 inline-block">info@ezerialibrary.com</a>
            </div>
            
            <div class="bg-white rounded-2xl shadow-sm p-6">
                <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center mb-4">
                    <i class="fab fa-whatsapp text-green-600 text-xl"></i>
                </div>
                <h3 class="font-bold text-lg text-gray-800 mb-2">WhatsApp</h3>
                <p class="text-gray-600">Chat with our team on WhatsApp</p>
                <a href="https://wa.me/256700000000" class="text-green-600 hover:underline mt-2 inline-block">+256 700 000 000</a>
            </div>
            
            <div class="bg-white rounded-2xl shadow-sm p-6">
                <div class="w-12 h-12 bg-purple-100 rounded-full flex items-center justify-center mb-4">
                    <i class="fas fa-phone-alt text-purple-600 text-xl"></i>
                </div>
                <h3 class="font-bold text-lg text-gray-800 mb-2">Call Us</h3>
                <p class="text-gray-600">Mon-Fri, 8am - 6pm</p>
                <a href="tel:+256700000000" class="text-purple-600 hover:underline mt-2 inline-block">+256 700 000 000</a>
            </div>
        </div>
        
        <!-- Contact Form -->
        <div class="lg:col-span-2">
            <div class="bg-white rounded-2xl shadow-sm p-8">
                <h2 class="text-2xl font-bold text-gray-800 mb-2">Send us a Message</h2>
                <p class="text-gray-600 mb-6">Our librarians will respond within 24 hours</p>
                
                <?php if ($message): ?>
                <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6 rounded">
                    <i class="fas fa-check-circle mr-2"></i> <?php echo $message; ?>
                </div>
                <?php endif; ?>
                
                <?php if ($error): ?>
                <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded">
                    <i class="fas fa-exclamation-circle mr-2"></i> <?php echo $error; ?>
                </div>
                <?php endif; ?>
                
                <form method="POST">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5 mb-5">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Full Name *</label>
                            <input type="text" name="name" required class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-indigo-500 focus:border-indigo-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Email *</label>
                            <input type="email" name="email" required class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-indigo-500 focus:border-indigo-500">
                        </div>
                    </div>
                    
                    <div class="mb-5">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Phone Number</label>
                        <input type="tel" name="phone" class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-indigo-500 focus:border-indigo-500">
                    </div>
                    
                    <div class="mb-5">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Subject</label>
                        <input type="text" name="subject" class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-indigo-500 focus:border-indigo-500" placeholder="e.g., Research Help, Citation Question">
                    </div>
                    
                    <div class="mb-6">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Message *</label>
                        <textarea name="message" rows="5" required class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-indigo-500 focus:border-indigo-500" placeholder="Please describe your question or request..."></textarea>
                    </div>
                    
                    <button type="submit" class="w-full bg-gradient-to-r from-indigo-600 to-purple-600 text-white py-3 rounded-xl hover:from-indigo-700 hover:to-purple-700 transition font-medium">
                        <i class="fas fa-paper-plane mr-2"></i> Send Message
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Footer -->
<footer class="bg-gray-900 text-white mt-12">
    <div class="max-w-7xl mx-auto px-4 py-8 text-center text-gray-400 text-sm">
        <p>&copy; <?php echo date('Y'); ?> Ezeria Library. All rights reserved.</p>
    </div>
</footer>

</body>
</html>
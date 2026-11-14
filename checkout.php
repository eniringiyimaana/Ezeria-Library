<?php
require_once 'config/database.php';
require_once 'includes/functions.php';
requireLogin();

$database = new Database();
$db = $database->getConnection();
$userId = $_SESSION['user_id'];

$itemId = isset($_GET['doc_id']) ? (int)$_GET['doc_id'] : (isset($_GET['book_id']) ? (int)$_GET['book_id'] : 0);
$itemType = isset($_GET['doc_id']) ? 'document' : (isset($_GET['book_id']) ? 'book' : '');

if (!$itemId || !$itemType) {
    header('Location: /index.php');
    exit();
}

// Get item details
$query = "SELECT * FROM documents WHERE id = :id AND status = 'approved'";
$stmt = $db->prepare($query);
$stmt->bindParam(':id', $itemId);
$stmt->execute();
$item = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$item) {
    header('Location: /index.php?error=item_not_found');
    exit();
}

// Get user profile
$profileQuery = "SELECT * FROM user_profiles WHERE user_id = :user_id";
$profileStmt = $db->prepare($profileQuery);
$profileStmt->bindParam(':user_id', $userId);
$profileStmt->execute();
$profile = $profileStmt->fetch(PDO::FETCH_ASSOC);

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $phone = sanitize($_POST['phone']);
    $address = sanitize($_POST['address']);
    $paymentMethod = sanitize($_POST['payment_method']);
    
    // Generate order number
    $orderNumber = 'EZ-' . strtoupper(uniqid());
    
    $query = "INSERT INTO book_orders (order_number, user_id, document_id, quantity, unit_price, total_price, payment_method, payment_status, shipping_address, phone) 
              VALUES (:order_number, :user_id, :document_id, 1, :unit_price, :total_price, :payment_method, 'pending', :address, :phone)";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':order_number', $orderNumber);
    $stmt->bindParam(':user_id', $userId);
    $stmt->bindParam(':document_id', $itemId);
    $stmt->bindParam(':unit_price', $item['price']);
    $stmt->bindParam(':total_price', $item['price']);
    $stmt->bindParam(':payment_method', $paymentMethod);
    $stmt->bindParam(':address', $address);
    $stmt->bindParam(':phone', $phone);
    
    if ($stmt->execute()) {
        $message = "Order placed successfully! You will receive a confirmation email shortly.";
        // Redirect to success page or dashboard
        header('Location: /user/orders.php?success=1');
        exit();
    } else {
        $error = "Failed to process order. Please try again.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - Ezeria Library</title>
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
                <i class="fas fa-shopping-cart text-2xl"></i>
                <span class="font-bold text-xl">Checkout</span>
            </div>
            <a href="/user/dashboard.php" class="hover:text-indigo-200 transition">
                <i class="fas fa-arrow-left mr-2"></i> Back to Dashboard
            </a>
        </div>
    </div>
</nav>

<div class="max-w-6xl mx-auto px-4 py-8">
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        
        <!-- Checkout Form -->
        <div class="lg:col-span-2">
            <div class="bg-white rounded-2xl shadow-sm p-6">
                <h2 class="text-xl font-bold text-gray-800 mb-4">Billing Information</h2>
                
                <?php if ($error): ?>
                <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-3 mb-4 rounded">
                    <i class="fas fa-exclamation-circle mr-2"></i> <?php echo $error; ?>
                </div>
                <?php endif; ?>
                
                <form method="POST">
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Phone Number *</label>
                        <input type="tel" name="phone" required value="<?php echo htmlspecialchars($profile['phone'] ?? ''); ?>" 
                               class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-indigo-500 focus:border-indigo-500">
                    </div>
                    
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Delivery Address</label>
                        <textarea name="address" rows="3" class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-indigo-500 focus:border-indigo-500"><?php echo htmlspecialchars($profile['address'] ?? ''); ?></textarea>
                    </div>
                    
                    <div class="mb-6">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Payment Method</label>
                        <div class="space-y-2">
                            <label class="flex items-center p-3 border rounded-lg cursor-pointer hover:bg-gray-50">
                                <input type="radio" name="payment_method" value="mobile_money" checked class="mr-3">
                                <i class="fab fa-paypal text-blue-600 text-xl mr-3"></i>
                                <div>
                                    <p class="font-medium">Mobile Money</p>
                                    <p class="text-xs text-gray-500">MTN Mobile Money / Airtel Money</p>
                                </div>
                            </label>
                            <label class="flex items-center p-3 border rounded-lg cursor-pointer hover:bg-gray-50">
                                <input type="radio" name="payment_method" value="credit_card" class="mr-3">
                                <i class="fab fa-cc-visa text-indigo-600 text-xl mr-3"></i>
                                <div>
                                    <p class="font-medium">Credit / Debit Card</p>
                                    <p class="text-xs text-gray-500">Visa, Mastercard, American Express</p>
                                </div>
                            </label>
                        </div>
                    </div>
                    
                    <button type="submit" class="w-full bg-gradient-to-r from-indigo-600 to-purple-600 text-white py-3 rounded-xl hover:from-indigo-700 hover:to-purple-700 transition font-semibold">
                        <i class="fas fa-lock mr-2"></i> Complete Purchase
                    </button>
                </form>
            </div>
        </div>
        
        <!-- Order Summary -->
        <div>
            <div class="bg-white rounded-2xl shadow-sm p-6 sticky top-24">
                <h3 class="font-bold text-lg text-gray-800 mb-4">Order Summary</h3>
                
                <div class="flex gap-3 mb-4 pb-4 border-b border-gray-100">
                    <div class="w-12 h-12 bg-indigo-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-file-pdf text-indigo-600 text-xl"></i>
                    </div>
                    <div class="flex-1">
                        <p class="font-medium text-gray-800 line-clamp-2"><?php echo htmlspecialchars($item['title']); ?></p>
                        <p class="text-xs text-gray-500">Instant digital download</p>
                    </div>
                    <p class="font-bold text-indigo-600">UGX <?php echo number_format($item['price'], 0); ?></p>
                </div>
                
                <div class="space-y-2 mb-4">
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-600">Subtotal</span>
                        <span>UGX <?php echo number_format($item['price'], 0); ?></span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-600">Tax</span>
                        <span>UGX 0</span>
                    </div>
                </div>
                
                <div class="pt-4 border-t border-gray-200">
                    <div class="flex justify-between font-bold text-lg">
                        <span>Total</span>
                        <span class="text-indigo-600">UGX <?php echo number_format($item['price'], 0); ?></span>
                    </div>
                </div>
                
                <div class="mt-6 p-3 bg-green-50 rounded-lg text-center">
                    <i class="fas fa-shield-alt text-green-600 mr-2"></i>
                    <span class="text-xs text-green-700">Secure payment guaranteed</span>
                </div>
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
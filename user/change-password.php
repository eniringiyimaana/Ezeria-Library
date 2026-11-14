<?php
require_once '../config/database.php';
require_once '../includes/functions.php';
requireLogin();

$database = new Database();
$db = $database->getConnection();
$userId = $_SESSION['user_id'];
$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $currentPassword = $_POST['current_password'];
    $newPassword = $_POST['new_password'];
    $confirmPassword = $_POST['confirm_password'];
    
    // Get current password hash
    $query = "SELECT password_hash FROM users WHERE id = :id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':id', $userId);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (password_verify($currentPassword, $user['password_hash'])) {
        if ($newPassword === $confirmPassword) {
            if (strlen($newPassword) >= 6) {
                $newHash = password_hash($newPassword, PASSWORD_DEFAULT);
                $update = "UPDATE users SET password_hash = :hash WHERE id = :id";
                $stmt = $db->prepare($update);
                $stmt->bindParam(':hash', $newHash);
                $stmt->bindParam(':id', $userId);
                
                if ($stmt->execute()) {
                    $message = "Password changed successfully!";
                } else {
                    $error = "Failed to update password.";
                }
            } else {
                $error = "New password must be at least 6 characters.";
            }
        } else {
            $error = "New passwords do not match.";
        }
    } else {
        $error = "Current password is incorrect.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Change Password - Ezeria Library</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gray-50">

<div class="max-w-md mx-auto px-4 py-16">
    <div class="bg-white rounded-2xl shadow-sm p-8">
        <div class="text-center mb-6">
            <div class="w-16 h-16 bg-indigo-100 rounded-full flex items-center justify-center mx-auto mb-4">
                <i class="fas fa-lock text-indigo-600 text-2xl"></i>
            </div>
            <h1 class="text-2xl font-bold text-gray-800">Change Password</h1>
            <p class="text-gray-600 text-sm mt-1">Keep your account secure</p>
        </div>
        
        <?php if ($message): ?>
        <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-3 mb-4 rounded text-sm">
            <i class="fas fa-check-circle mr-2"></i> <?php echo $message; ?>
        </div>
        <?php endif; ?>
        
        <?php if ($error): ?>
        <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-3 mb-4 rounded text-sm">
            <i class="fas fa-exclamation-circle mr-2"></i> <?php echo $error; ?>
        </div>
        <?php endif; ?>
        
        <form method="POST">
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Current Password</label>
                <input type="password" name="current_password" required class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-indigo-500 focus:border-indigo-500">
            </div>
            
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">New Password</label>
                <input type="password" name="new_password" required class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-indigo-500 focus:border-indigo-500">
                <p class="text-xs text-gray-500 mt-1">Minimum 6 characters</p>
            </div>
            
            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 mb-1">Confirm New Password</label>
                <input type="password" name="confirm_password" required class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-indigo-500 focus:border-indigo-500">
            </div>
            
            <button type="submit" class="w-full bg-gradient-to-r from-indigo-600 to-purple-600 text-white py-2 rounded-lg hover:from-indigo-700 hover:to-purple-700 transition font-medium">
                Update Password
            </button>
        </form>
        
        <div class="text-center mt-4">
            <a href="/user/dashboard.php" class="text-indigo-600 hover:underline text-sm">
                <i class="fas fa-arrow-left mr-1"></i> Back to Dashboard
            </a>
        </div>
    </div>
</div>

</body>
</html>
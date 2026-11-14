<?php
require_once '../config/database.php';
require_once '../includes/functions.php';

$database = new Database();
$db = $database->getConnection();

if (!isset($_GET['id'])) {
    header('Location: /index.php');
    exit();
}

$docId = (int)$_GET['id'];

// Get document info
$query = "SELECT * FROM documents WHERE id = :id AND status = 'approved'";
$stmt = $db->prepare($query);
$stmt->bindParam(':id', $docId);
$stmt->execute();
$doc = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$doc) {
    header('Location: /index.php?error=document_not_found');
    exit();
}

// Check if user is logged in for premium documents
if ($doc['type'] == 'premium' && !isLoggedIn()) {
    header('Location: /auth/login.php?redirect=/document.php?id=' . $docId);
    exit();
}

// Record download
$insert = "INSERT INTO downloads (document_id, user_id, ip_address) 
           VALUES (:doc_id, :user_id, :ip)";
$user_id = isLoggedIn() ? $_SESSION['user_id'] : null;
$ip = $_SERVER['REMOTE_ADDR'];

$stmt = $db->prepare($insert);
$stmt->bindParam(':doc_id', $docId);
$stmt->bindParam(':user_id', $user_id);
$stmt->bindParam(':ip', $ip);
$stmt->execute();

// Update download count
$update = "UPDATE documents SET downloads_count = downloads_count + 1 WHERE id = :id";
$stmt = $db->prepare($update);
$stmt->bindParam(':id', $docId);
$stmt->execute();

// Serve the file
$filePath = '../assets/uploads/' . $doc['file_path'];
if (file_exists($filePath)) {
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename="' . basename($doc['title']) . '.pdf"');
    header('Content-Length: ' . filesize($filePath));
    readfile($filePath);
    exit();
} else {
    header('Location: /index.php?error=file_not_found');
    exit();
}
?>
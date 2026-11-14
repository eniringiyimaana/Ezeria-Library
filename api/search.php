<?php
require_once '../config/database.php';

header('Content-Type: application/json');

$database = new Database();
$db = $database->getConnection();

$search = isset($_GET['q']) ? '%' . $_GET['q'] . '%' : '';
$type = isset($_GET['type']) ? $_GET['type'] : '';
$level = isset($_GET['level']) ? (int)$_GET['level'] : 0;

$query = "SELECT d.*, u.username as uploader, l.name as level_name, c.name as category_name 
          FROM documents d 
          LEFT JOIN users u ON d.uploaded_by = u.id 
          LEFT JOIN levels l ON d.level_id = l.id 
          LEFT JOIN categories c ON d.category_id = c.id 
          WHERE d.status = 'approved'";

if (!empty($search)) {
    $query .= " AND (d.title LIKE :search OR d.description LIKE :search OR d.author LIKE :search)";
}

if (!empty($type)) {
    $query .= " AND d.type = :type";
}

if ($level > 0) {
    $query .= " AND d.level_id = :level";
}

$query .= " ORDER BY d.created_at DESC LIMIT 50";

$stmt = $db->prepare($query);

if (!empty($search)) {
    $stmt->bindParam(':search', $search);
}
if (!empty($type)) {
    $stmt->bindParam(':type', $type);
}
if ($level > 0) {
    $stmt->bindParam(':level', $level);
}

$stmt->execute();
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode([
    'success' => true,
    'total' => count($results),
    'results' => $results
]);
?>
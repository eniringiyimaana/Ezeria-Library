<?php
require_once '../config/database.php';
require_once '../includes/functions.php';
requireRole('librarian');

$database = new Database();
$db = $database->getConnection();

// Get categories and levels for dropdowns
$categoriesQuery = "SELECT * FROM categories ORDER BY name";
$categoriesStmt = $db->prepare($categoriesQuery);
$categoriesStmt->execute();
$categories = $categoriesStmt->fetchAll(PDO::FETCH_ASSOC);

$levelsQuery = "SELECT * FROM levels ORDER BY parent_id, sort_order";
$levelsStmt = $db->prepare($levelsQuery);
$levelsStmt->execute();
$levels = $levelsStmt->fetchAll(PDO::FETCH_ASSOC);

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = sanitize($_POST['title']);
    $description = sanitize($_POST['description']);
    $author = sanitize($_POST['author']);
    $year = (int)$_POST['year'];
    $pages = (int)$_POST['pages'];
    $category_id = (int)$_POST['category_id'];
    $level_id = (int)$_POST['level_id'];
    $type = sanitize($_POST['type']);
    $price = $type == 'premium' ? (float)$_POST['price'] : 0;
    
    // Handle file upload
    if (isset($_FILES['document_file']) && $_FILES['document_file']['error'] == 0) {
        $allowed = ['pdf', 'doc', 'docx', 'ppt', 'pptx', 'mp4', 'mp3'];
        $filename = $_FILES['document_file']['name'];
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        
        if (in_array($ext, $allowed)) {
            $newFilename = uniqid() . '.' . $ext;
            $uploadPath = '../assets/uploads/' . $newFilename;
            
            if (move_uploaded_file($_FILES['document_file']['tmp_name'], $uploadPath)) {
                $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $title)));
                $slug = $slug . '-' . uniqid();
                
                $query = "INSERT INTO documents (title, slug, description, file_path, file_type, file_size, pages, author, year, category_id, level_id, type, price, uploaded_by, status) 
                          VALUES (:title, :slug, :description, :file_path, :file_type, :file_size, :pages, :author, :year, :category_id, :level_id, :type, :price, :uploaded_by, 'pending')";
                
                $stmt = $db->prepare($query);
                $stmt->bindParam(':title', $title);
                $stmt->bindParam(':slug', $slug);
                $stmt->bindParam(':description', $description);
                $stmt->bindParam(':file_path', $newFilename);
                $stmt->bindParam(':file_type', $ext);
                $stmt->bindParam(':file_size', $_FILES['document_file']['size']);
                $stmt->bindParam(':pages', $pages);
                $stmt->bindParam(':author', $author);
                $stmt->bindParam(':year', $year);
                $stmt->bindParam(':category_id', $category_id);
                $stmt->bindParam(':level_id', $level_id);
                $stmt->bindParam(':type', $type);
                $stmt->bindParam(':price', $price);
                $stmt->bindParam(':uploaded_by', $_SESSION['user_id']);
                
                if ($stmt->execute()) {
                    $message = "Document uploaded successfully! It will be reviewed by an admin.";
                } else {
                    $error = "Failed to save document information.";
                }
            } else {
                $error = "Failed to upload file.";
            }
        } else {
            $error = "File type not allowed. Allowed: " . implode(', ', $allowed);
        }
    } else {
        $error = "Please select a file to upload.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload Document - Ezeria Library</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { font-family: 'Inter', sans-serif; }
        .drop-zone {
            border: 2px dashed #cbd5e1;
            transition: all 0.3s ease;
        }
        .drop-zone.drag-over {
            border-color: #4f46e5;
            background-color: #eef2ff;
        }
    </style>
</head>
<body class="bg-gray-50">

<!-- Navigation -->
<nav class="bg-gradient-to-r from-blue-800 to-indigo-800 text-white shadow-lg sticky top-0 z-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center h-16">
            <div class="flex items-center space-x-3">
                <i class="fas fa-upload text-2xl"></i>
                <span class="font-bold text-xl">Upload New Document</span>
            </div>
            <div class="flex items-center space-x-4">
                <a href="/librarian/dashboard.php" class="hover:text-indigo-200 transition">
                    <i class="fas fa-arrow-left mr-2"></i> Back to Dashboard
                </a>
            </div>
        </div>
    </div>
</nav>

<div class="max-w-4xl mx-auto px-4 py-8">
    <div class="bg-white rounded-2xl shadow-sm p-8">
        <h1 class="text-2xl font-bold text-gray-800 mb-2">Upload a New Resource</h1>
        <p class="text-gray-600 mb-6">Share your educational materials with the Ezeria community</p>
        
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
        
        <form method="POST" enctype="multipart/form-data" id="uploadForm">
            <!-- File Upload -->
            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 mb-2">Document File *</label>
                <div id="dropZone" class="drop-zone rounded-xl p-8 text-center cursor-pointer">
                    <i class="fas fa-cloud-upload-alt text-5xl text-gray-400 mb-3"></i>
                    <p class="text-gray-600">Drag & drop your file here or click to browse</p>
                    <p class="text-xs text-gray-400 mt-2">PDF, DOC, DOCX, PPT, MP4, MP3 (Max 50MB)</p>
                    <input type="file" name="document_file" id="fileInput" class="hidden" accept=".pdf,.doc,.docx,.ppt,.pptx,.mp4,.mp3">
                </div>
                <div id="fileInfo" class="hidden mt-3 p-3 bg-gray-50 rounded-lg">
                    <i class="fas fa-file-pdf text-red-500 mr-2"></i>
                    <span id="fileName"></span>
                    <span id="fileSize" class="text-gray-500 text-sm ml-2"></span>
                </div>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Title -->
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Document Title *</label>
                    <input type="text" name="title" required class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-indigo-500 focus:border-indigo-500">
                </div>
                
                <!-- Description -->
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Description</label>
                    <textarea name="description" rows="4" class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-indigo-500 focus:border-indigo-500"></textarea>
                </div>
                
                <!-- Author -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Author</label>
                    <input type="text" name="author" class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-indigo-500 focus:border-indigo-500">
                </div>
                
                <!-- Year -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Publication Year</label>
                    <select name="year" class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="2026">2026</option>
                        <option value="2025">2025</option>
                        <option value="2024">2024</option>
                        <option value="2023">2023</option>
                        <option value="2022">2022</option>
                        <option value="2021">2021</option>
                        <option value="2020">2020</option>
                    </select>
                </div>
                
                <!-- Pages -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Number of Pages</label>
                    <input type="number" name="pages" value="0" class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-indigo-500 focus:border-indigo-500">
                </div>
                
                <!-- Category -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Category</label>
                    <select name="category_id" class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="">Select Category</option>
                        <?php foreach ($categories as $category): ?>
                        <option value="<?php echo $category['id']; ?>"><?php echo htmlspecialchars($category['name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <!-- Level -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Educational Level</label>
                    <select name="level_id" class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="">Select Level</option>
                        <?php foreach ($levels as $level): ?>
                        <option value="<?php echo $level['id']; ?>"><?php echo htmlspecialchars($level['name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <!-- Document Type -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Document Type</label>
                    <select name="type" id="docType" class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="free">Free</option>
                        <option value="premium">Premium (Paid)</option>
                        <option value="book">Book Store</option>
                    </select>
                </div>
                
                <!-- Price (shown only for premium) -->
                <div id="priceField" class="hidden">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Price (UGX)</label>
                    <input type="number" name="price" value="0" class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-indigo-500 focus:border-indigo-500">
                </div>
            </div>
            
            <div class="mt-8">
                <button type="submit" class="w-full bg-gradient-to-r from-blue-600 to-indigo-600 text-white py-3 rounded-xl hover:from-blue-700 hover:to-indigo-700 transition font-medium">
                    <i class="fas fa-cloud-upload-alt mr-2"></i> Upload Document
                </button>
            </div>
        </form>
    </div>
</div>

<script>
// Drag and drop functionality
const dropZone = document.getElementById('dropZone');
const fileInput = document.getElementById('fileInput');
const fileInfo = document.getElementById('fileInfo');
const fileName = document.getElementById('fileName');
const fileSize = document.getElementById('fileSize');

dropZone.addEventListener('click', () => fileInput.click());
dropZone.addEventListener('dragover', (e) => {
    e.preventDefault();
    dropZone.classList.add('drag-over');
});
dropZone.addEventListener('dragleave', () => {
    dropZone.classList.remove('drag-over');
});
dropZone.addEventListener('drop', (e) => {
    e.preventDefault();
    dropZone.classList.remove('drag-over');
    fileInput.files = e.dataTransfer.files;
    updateFileInfo();
});

fileInput.addEventListener('change', updateFileInfo);

function updateFileInfo() {
    if (fileInput.files.length > 0) {
        const file = fileInput.files[0];
        fileName.textContent = file.name;
        fileSize.textContent = '(' + (file.size / 1024 / 1024).toFixed(2) + ' MB)';
        fileInfo.classList.remove('hidden');
        dropZone.classList.add('border-green-500');
    }
}

// Show/hide price field based on document type
const docType = document.getElementById('docType');
const priceField = document.getElementById('priceField');

docType.addEventListener('change', function() {
    if (this.value === 'premium') {
        priceField.classList.remove('hidden');
    } else {
        priceField.classList.add('hidden');
    }
});
</script>

</body>
</html>
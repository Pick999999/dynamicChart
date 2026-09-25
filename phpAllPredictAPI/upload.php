<?php
// upload.php - Handle image uploads for CKEditor
header('Content-Type: application/json');

// Configuration
$uploadDir = 'uploads/'; // Directory to store uploaded images
$allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
$maxFileSize = 5 * 1024 * 1024; // 5MB

// Create upload directory if it doesn't exist
if (!file_exists($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

// Check if file was uploaded
if (!isset($_FILES['upload'])) {
    echo json_encode([
        'uploaded' => 0,
        'error' => ['message' => 'No file uploaded']
    ]);
    exit;
}

$file = $_FILES['upload'];

// Validate file
if ($file['error'] !== UPLOAD_ERR_OK) {
    echo json_encode([
        'uploaded' => 0,
        'error' => ['message' => 'Upload error: ' . $file['error']]
    ]);
    exit;
}

// Check file type
if (!in_array($file['type'], $allowedTypes)) {
    echo json_encode([
        'uploaded' => 0,
        'error' => ['message' => 'Invalid file type. Only JPEG, PNG, GIF, and WebP are allowed.']
    ]);
    exit;
}

// Check file size
if ($file['size'] > $maxFileSize) {
    echo json_encode([
        'uploaded' => 0,
        'error' => ['message' => 'File size exceeds 5MB limit.']
    ]);
    exit;
}

// Generate unique filename
$extension = pathinfo($file['name'], PATHINFO_EXTENSION);
$filename = uniqid() . '_' . time() . '.' . $extension;
$filepath = $uploadDir . $filename;

// Move uploaded file
if (move_uploaded_file($file['tmp_name'], $filepath)) {
    // Get full URL
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'];
    $baseUrl = $protocol . '://' . $host . dirname($_SERVER['PHP_SELF']);
    $fileUrl = $baseUrl . '/' . $filepath;
    
    // Return success response in CKEditor format
    echo json_encode([
        'uploaded' => 1,
        'fileName' => $filename,
        'url' => $fileUrl
    ]);
} else {
    echo json_encode([
        'uploaded' => 0,
        'error' => ['message' => 'Failed to save file.']
    ]);
}
?>
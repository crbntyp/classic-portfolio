<?php
require_once __DIR__ . '/config.php';
requireAuth();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(['success' => false, 'message' => 'POST required'], 405);
}

if (!isset($_FILES['image'])) {
    jsonResponse(['success' => false, 'message' => 'No file uploaded'], 400);
}

$file = $_FILES['image'];
$maxSize = 10 * 1024 * 1024; // 10MB

if ($file['error'] !== UPLOAD_ERR_OK) {
    jsonResponse(['success' => false, 'message' => 'Upload error'], 400);
}

if ($file['size'] > $maxSize) {
    jsonResponse(['success' => false, 'message' => 'File too large (max 10MB)'], 400);
}

// Validate image
$imageInfo = getimagesize($file['tmp_name']);
if (!$imageInfo) {
    jsonResponse(['success' => false, 'message' => 'Invalid image file'], 400);
}

$allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
if (!in_array($imageInfo['mime'], $allowedTypes)) {
    jsonResponse(['success' => false, 'message' => 'Unsupported image type'], 400);
}

$ext = match($imageInfo['mime']) {
    'image/jpeg' => 'jpg',
    'image/png' => 'png',
    'image/gif' => 'gif',
    'image/webp' => 'webp',
    default => 'png'
};

$filename = 'artwork-' . uniqid() . '.' . $ext;
$uploadDir = dirname(__DIR__) . '/uploads/';

if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

$destination = $uploadDir . $filename;

if (!move_uploaded_file($file['tmp_name'], $destination)) {
    jsonResponse(['success' => false, 'message' => 'Failed to save file'], 500);
}

jsonResponse(['success' => true, 'filename' => $filename]);

<?php
session_start();
require_once 'includes/connection.php';
require_once 'includes/file-upload.php';

// Set JSON header
header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'You must be logged in']);
    exit;
}

// Check if request is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

// Get POST data
$category = $_POST['category'] ?? '';
$projectName = $_POST['projectName'] ?? '';
$projectUrl = $_POST['projectUrl'] ?? '';

// Default URL to javascript:void(0) if empty
if (empty($projectUrl)) {
    $projectUrl = 'javascript:void(0)';
}

// Validate input
if (empty($category) || empty($projectName)) {
    echo json_encode(['success' => false, 'message' => 'Category and Project Name are required']);
    exit;
}

// Determine if this is a blob entry
$blobEntry = 0;
if (in_array($category, ['recent-artwork', 'artwork-portfolio', 'vibes'])) {
    $blobEntry = 1;
}

// Handle Classic Portfolio category (requires image)
if ($category === 'classic-portfolio') {
    // Validate and upload image
    $uploader = new FileUploadHandler();

    try {
        $filename = $uploader->uploadImage($_FILES['projectImage'] ?? null, 'portfolio/uploads');
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        exit;
    }

    // Insert into projects table with blobEntry = 0
    $stmt = $mysqli->prepare("INSERT INTO projects (projectHeading, projectTeaser, url, projectCTA, blobEntry) VALUES (?, ?, ?, ?, ?)");
    $cta = 'View Project';
    $stmt->bind_param("ssssi", $projectName, $filename, $projectUrl, $cta, $blobEntry);

    if ($stmt->execute()) {
        echo json_encode([
            'success' => true,
            'message' => 'Classic Portfolio project added successfully',
            'projectId' => $stmt->insert_id
        ]);
    } else {
        // Delete uploaded file if database insert fails
        unlink($uploadPath);
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $mysqli->error]);
    }

    $stmt->close();
}
// Handle blob entry categories (Recent Artwork, Artwork Portfolio, Vibes)
elseif ($blobEntry === 1) {
    // Insert into projects table with blobEntry = 1
    $stmt = $mysqli->prepare("INSERT INTO projects (projectHeading, url, blobEntry) VALUES (?, ?, ?)");
    $stmt->bind_param("ssi", $projectName, $projectUrl, $blobEntry);

    if ($stmt->execute()) {
        echo json_encode([
            'success' => true,
            'message' => 'Link added successfully to ' . ucwords(str_replace('-', ' ', $category)),
            'projectId' => $stmt->insert_id
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $mysqli->error]);
    }

    $stmt->close();
}
else {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid category'
    ]);
}

$mysqli->close();
?>

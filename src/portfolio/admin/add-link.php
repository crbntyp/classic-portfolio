<?php
// Check authentication (includes ApiResponse helper)
require_once 'auth-check.php';
require_once '../includes/connection.php';
require_once '../includes/file-upload.php';

// Validate request method
ApiResponse::requireMethod('POST');

// Validate required parameters
ApiResponse::requireParams(['category', 'projectName']);

// Get POST data (already validated)
$category = $_POST['category'];
$projectName = $_POST['projectName'];
$projectUrl = $_POST['projectUrl'] ?? '';
$projectUrlTwo = $_POST['projectUrlTwo'] ?? '';
$projectDescription = $_POST['projectDescription'] ?? '';
$projectCTA = $_POST['projectCTA'] ?? '';

// Default URL to javascript:void(0) if empty
if (empty($projectUrl)) {
    $projectUrl = 'javascript:void(0)';
}

// Handle empty url_two
if (empty($projectUrlTwo)) {
    $projectUrlTwo = null;
}

// Default CTA if not provided
if (empty($projectCTA)) {
    $projectCTA = 'View Project';
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
        $filename = $uploader->uploadImage($_FILES['projectImage'] ?? null, '../uploads');
    } catch (Exception $e) {
        ApiResponse::error($e->getMessage());
    }

    // Insert into projects table with blobEntry = 0
    $stmt = $mysqli->prepare("INSERT INTO projects (projectHeading, projectDescription, projectTeaser, url, url_two, projectCTA, blobEntry) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssssi", $projectName, $projectDescription, $filename, $projectUrl, $projectUrlTwo, $projectCTA, $blobEntry);

    if ($stmt->execute()) {
        $projectId = $stmt->insert_id;
        $stmt->close();
        $mysqli->close();
        ApiResponse::success([
            'projectId' => $projectId
        ], 'Classic Portfolio project added successfully');
    } else {
        // Delete uploaded file if database insert fails
        unlink($uploadPath);
        $error = $mysqli->error;
        $stmt->close();
        $mysqli->close();
        ApiResponse::serverError('Database error: ' . $error);
    }
}
// Handle blob entry categories (Recent Artwork, Artwork Portfolio, Vibes)
elseif ($blobEntry === 1) {
    // Insert into projects table with blobEntry = 1
    $stmt = $mysqli->prepare("INSERT INTO projects (projectHeading, projectDescription, url, url_two, projectCTA, blobEntry) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssssi", $projectName, $projectDescription, $projectUrl, $projectUrlTwo, $projectCTA, $blobEntry);

    if ($stmt->execute()) {
        $projectId = $stmt->insert_id;
        $categoryName = ucwords(str_replace('-', ' ', $category));
        $stmt->close();
        $mysqli->close();
        ApiResponse::success([
            'projectId' => $projectId
        ], 'Link added successfully to ' . $categoryName);
    } else {
        $error = $mysqli->error;
        $stmt->close();
        $mysqli->close();
        ApiResponse::serverError('Database error: ' . $error);
    }
}
else {
    $mysqli->close();
    ApiResponse::error('Invalid category');
}

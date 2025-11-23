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

// Validate category
$validCategories = ['classic-portfolio', 'recent-artwork', 'artwork-portfolio', 'vibes'];
if (!in_array($category, $validCategories)) {
    ApiResponse::error('Invalid category');
}

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

// Handle optional image upload
$filename = null;
if (isset($_FILES['projectImage']) && $_FILES['projectImage']['error'] !== UPLOAD_ERR_NO_FILE) {
    $uploader = new FileUploadHandler();
    try {
        $filename = $uploader->uploadImage($_FILES['projectImage'], '../uploads');
    } catch (Exception $e) {
        ApiResponse::error($e->getMessage());
    }
}

// Get max sort_order for new project
$maxOrderResult = $mysqli->query("SELECT MAX(sort_order) as max_order FROM projects");
$maxOrder = $maxOrderResult->fetch_assoc()['max_order'] ?? 0;
$newSortOrder = $maxOrder + 1;

// Insert into projects table with category
$stmt = $mysqli->prepare("INSERT INTO projects (projectHeading, projectDescription, projectTeaser, url, url_two, projectCTA, category, sort_order) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
$stmt->bind_param("sssssssi", $projectName, $projectDescription, $filename, $projectUrl, $projectUrlTwo, $projectCTA, $category, $newSortOrder);

if ($stmt->execute()) {
    $projectId = $stmt->insert_id;
    $categoryName = ucwords(str_replace('-', ' ', $category));
    $stmt->close();
    $mysqli->close();
    ApiResponse::success([
        'projectId' => $projectId
    ], $categoryName . ' project added successfully');
} else {
    // Delete uploaded file if database insert fails
    if ($filename) {
        @unlink('../uploads/' . $filename);
    }
    $error = $mysqli->error;
    $stmt->close();
    $mysqli->close();
    ApiResponse::serverError('Database error: ' . $error);
}

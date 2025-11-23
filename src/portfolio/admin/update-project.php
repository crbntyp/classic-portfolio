<?php
// Check authentication (includes ApiResponse helper)
require_once 'auth-check.php';
require_once '../includes/connection.php';
require_once '../includes/file-upload.php';

// Validate request method
ApiResponse::requireMethod('POST');

// Validate required parameters
ApiResponse::requireParams(['projectID', 'projectName', 'category']);

// Get POST data (already validated)
$projectID = $_POST['projectID'];
$projectName = $_POST['projectName'];
$projectDescription = $_POST['projectDescription'] ?? '';
$projectUrl = $_POST['projectUrl'] ?? '';
$projectUrlTwo = $_POST['projectUrlTwo'] ?? '';
$projectCTA = $_POST['projectCTA'] ?? '';
$category = $_POST['category'];

$projectID = intval($projectID);

// Validate category
$validCategories = ['classic-portfolio', 'recent-artwork', 'artwork-portfolio', 'vibes'];
if (!in_array($category, $validCategories)) {
    ApiResponse::error('Invalid category');
}

// Handle empty values
if (empty($projectUrl)) {
    $projectUrl = null;
}
if (empty($projectUrlTwo)) {
    $projectUrlTwo = null;
}
if (empty($projectCTA)) {
    $projectCTA = 'View Project';
}

// Check if new image is uploaded
$newImage = null;
$uploader = new FileUploadHandler();

if ($uploader->hasUpload($_FILES['projectImage'] ?? null)) {
    try {
        $newImage = $uploader->uploadImage($_FILES['projectImage'], '../uploads');
    } catch (Exception $e) {
        ApiResponse::error($e->getMessage());
    }

    // Delete old image if exists
    $oldImageQuery = $mysqli->prepare("SELECT projectTeaser FROM projects WHERE projectID = ?");
    $oldImageQuery->bind_param("i", $projectID);
    $oldImageQuery->execute();
    $oldImageQuery->bind_result($oldTeaser);
    if ($oldImageQuery->fetch()) {
        if (!empty($oldTeaser)) {
            $oldImagePath = $uploadDir . $oldTeaser;
            if (file_exists($oldImagePath)) {
                unlink($oldImagePath);
            }
        }
    }
    $oldImageQuery->close();
}

// Update project
if ($newImage) {
    // Update with new image
    $stmt = $mysqli->prepare("UPDATE projects SET projectHeading = ?, projectDescription = ?, projectTeaser = ?, url = ?, url_two = ?, projectCTA = ?, category = ? WHERE projectID = ?");
    $stmt->bind_param("sssssssi", $projectName, $projectDescription, $newImage, $projectUrl, $projectUrlTwo, $projectCTA, $category, $projectID);
} else {
    // Update without changing image
    $stmt = $mysqli->prepare("UPDATE projects SET projectHeading = ?, projectDescription = ?, url = ?, url_two = ?, projectCTA = ?, category = ? WHERE projectID = ?");
    $stmt->bind_param("ssssssi", $projectName, $projectDescription, $projectUrl, $projectUrlTwo, $projectCTA, $category, $projectID);
}

if ($stmt->execute()) {
    $stmt->close();
    $mysqli->close();
    ApiResponse::success([], 'Project updated successfully');
} else {
    // If update fails and we uploaded a new image, delete it
    if ($newImage && isset($uploadPath) && file_exists($uploadPath)) {
        unlink($uploadPath);
    }
    $error = $mysqli->error;
    $stmt->close();
    $mysqli->close();
    ApiResponse::serverError('Database error: ' . $error);
}

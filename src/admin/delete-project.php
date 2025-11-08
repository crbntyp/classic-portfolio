<?php
error_reporting(0);
ini_set('display_errors', 0);

// Check authentication (includes session initialization and API response helper)
require_once 'auth-check.php';

// Validate request method
ApiResponse::requireMethod('POST');

// Get project ID
$projectID = isset($_POST['projectID']) ? intval($_POST['projectID']) : 0;

if ($projectID <= 0) {
    ApiResponse::error('Invalid project ID');
}

// Use shared database connection
require_once '../includes/connection.php';

if (!isset($mysqli) || $mysqli->connect_error) {
    ApiResponse::serverError('Database connection failed');
}

// Delete the project
$stmt = $mysqli->prepare("DELETE FROM projects WHERE projectID = ?");
if (!$stmt) {
    $mysqli->close();
    ApiResponse::serverError('Failed to prepare database query');
}

$stmt->bind_param("i", $projectID);
$success = $stmt->execute();
$stmt->close();
$mysqli->close();

// Return response
if ($success) {
    ApiResponse::success([], 'Project deleted successfully');
} else {
    ApiResponse::error('Delete failed');
}

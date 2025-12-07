<?php
error_reporting(0);
ini_set('display_errors', 0);

// Check authentication (includes session initialization and API response helper)
require_once 'auth-check.php';
require_once '../includes/connection.php';

// Validate request method
ApiResponse::requireMethod('POST');

// Get shrug ID
$id = isset($_POST['id']) ? intval($_POST['id']) : 0;

if ($id <= 0) {
    ApiResponse::error('Invalid shrug ID');
}

// Check database connection
if (!isset($mysqli) || $mysqli->connect_error) {
    ApiResponse::serverError('Database connection failed');
}

// Delete the shrug entry
$stmt = $mysqli->prepare("DELETE FROM shrug_entries WHERE id = ?");
if (!$stmt) {
    $mysqli->close();
    ApiResponse::serverError('Failed to prepare database query');
}

$stmt->bind_param("i", $id);
$success = $stmt->execute();
$stmt->close();
$mysqli->close();

// Return response
if ($success) {
    ApiResponse::success([], 'Shrug entry deleted successfully');
} else {
    ApiResponse::error('Delete failed');
}

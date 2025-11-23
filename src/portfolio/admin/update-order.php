<?php
error_reporting(0);
ini_set('display_errors', 0);

// Check authentication (includes session initialization and API response helper)
require_once 'auth-check.php';

// Validate request method
ApiResponse::requireMethod('POST');

// Get the order data from JSON body
$input = json_decode(file_get_contents('php://input'), true);
$order = isset($input['order']) ? $input['order'] : [];

if (empty($order) || !is_array($order)) {
    ApiResponse::error('Invalid order data');
}

// Use shared database connection
require_once '../includes/connection.php';

if (!isset($mysqli) || $mysqli->connect_error) {
    ApiResponse::serverError('Database connection failed');
}

// Update sort_order for each project
$stmt = $mysqli->prepare("UPDATE projects SET sort_order = ? WHERE projectID = ?");
if (!$stmt) {
    $mysqli->close();
    ApiResponse::serverError('Failed to prepare database query');
}

$success = true;
foreach ($order as $position => $projectID) {
    $projectID = intval($projectID);
    $sortOrder = $position + 1;
    $stmt->bind_param("ii", $sortOrder, $projectID);
    if (!$stmt->execute()) {
        $success = false;
        break;
    }
}

$stmt->close();
$mysqli->close();

// Return response
if ($success) {
    ApiResponse::success([], 'Order updated successfully');
} else {
    ApiResponse::error('Failed to update order');
}

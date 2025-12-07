<?php
error_reporting(0);
ini_set('display_errors', 0);

// Check authentication
require_once 'auth-check.php';
require_once '../includes/connection.php';

// Validate request method
ApiResponse::requireMethod('POST');

// Check database connection
if (!isset($mysqli) || !$mysqli) {
    ApiResponse::serverError('Database connection failed');
}

// Get content from POST
$content = isset($_POST['content']) ? $_POST['content'] : '';

// Create settings table if it doesn't exist
$mysqli->query("CREATE TABLE IF NOT EXISTS site_settings (
    setting_key VARCHAR(255) PRIMARY KEY,
    setting_value LONGTEXT,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
)");

// Insert or update the bookmarks content
$stmt = $mysqli->prepare("INSERT INTO site_settings (setting_key, setting_value) VALUES ('bookmarks_html', ?)
    ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)");

if (!$stmt) {
    $mysqli->close();
    ApiResponse::serverError('Prepare failed: ' . $mysqli->error);
}

$stmt->bind_param("s", $content);
$success = $stmt->execute();
$stmt->close();
$mysqli->close();

if ($success) {
    ApiResponse::success([], 'Bookmarks saved successfully');
} else {
    ApiResponse::error('Failed to save bookmarks');
}

<?php
error_reporting(0);
ini_set('display_errors', 0);

// Check authentication
require_once 'auth-check.php';
require_once '../includes/connection.php';

// Validate request method
ApiResponse::requireMethod('GET');

// Check database connection
if (!isset($mysqli) || !$mysqli) {
    ApiResponse::serverError('Database connection failed');
}

// Create settings table if it doesn't exist
$mysqli->query("CREATE TABLE IF NOT EXISTS site_settings (
    setting_key VARCHAR(255) PRIMARY KEY,
    setting_value LONGTEXT,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
)");

// Get bookmarks content
$result = $mysqli->query("SELECT setting_value FROM site_settings WHERE setting_key = 'bookmarks_html'");

$content = '';
if ($result && $row = $result->fetch_assoc()) {
    $content = $row['setting_value'];
}

// If empty, return default bookmarks
if (empty($content)) {
    $content = '<p>[https://www.anthropic.com] Anthropic</p><p>[https://designvault.io/] Design Vault</p>';
}

$mysqli->close();
ApiResponse::success(['content' => $content]);

<?php
/**
 * Admin Authentication Check
 * Include this at the top of all admin pages
 */

// Load required helpers
require_once __DIR__ . '/../includes/session-manager.php';
require_once __DIR__ . '/../includes/api-response.php';

// Initialize secure session
initializeSecureSession();

// Check if user is logged in
if (!isset($_SESSION['user_id']) || !isset($_SESSION['username'])) {
    // Check if this is an AJAX request
    $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
              strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';

    // Check if this is a fetch/JSON request
    $acceptHeader = isset($_SERVER['HTTP_ACCEPT']) ? $_SERVER['HTTP_ACCEPT'] : '';
    $isJson = strpos($acceptHeader, 'application/json') !== false;

    if ($isAjax || $isJson || isset($_GET['id']) || isset($_POST['projectID'])) {
        // AJAX/API request - return JSON error
        ApiResponse::unauthorized('Not authenticated');
    } else {
        // Regular page request - redirect to home
        header('Location: /');
        exit;
    }
}

// User is authenticated
$current_user_id = $_SESSION['user_id'];
$current_username = $_SESSION['username'];

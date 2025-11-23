<?php
// Enable error reporting for debugging
ini_set('display_errors', 0);
error_reporting(E_ALL);

// Check authentication (includes ApiResponse helper)
require_once 'auth-check.php';
require_once '../includes/connection.php';

// Validate request method
ApiResponse::requireMethod('GET');

// Validate required parameters
ApiResponse::requireParams(['id'], 'GET');

// Check database connection
if (!isset($mysqli) || !$mysqli) {
    ApiResponse::serverError('Database connection failed');
}

$projectID = intval($_GET['id']);

try {
    // Fetch project data - using bind_result for compatibility
    $stmt = $mysqli->prepare("SELECT projectID, projectHeading, projectDescription, projectTeaser, url, url_two, projectCTA, category FROM projects WHERE projectID = ?");

    if (!$stmt) {
        $mysqli->close();
        ApiResponse::serverError('Prepare failed: ' . $mysqli->error);
    }

    $stmt->bind_param("i", $projectID);
    $stmt->execute();

    // Use bind_result instead of get_result for server compatibility
    $stmt->bind_result($id, $heading, $description, $teaser, $url, $url_two, $cta, $category);

    if ($stmt->fetch()) {
        $project = [
            'projectID' => $id,
            'projectHeading' => $heading,
            'projectDescription' => $description,
            'projectTeaser' => $teaser,
            'url' => $url,
            'url_two' => $url_two,
            'projectCTA' => $cta,
            'category' => $category ?? 'classic-portfolio'
        ];

        $stmt->close();
        $mysqli->close();

        ApiResponse::success(['project' => $project]);
    } else {
        $stmt->close();
        $mysqli->close();
        ApiResponse::notFound('Project not found');
    }
} catch (Exception $e) {
    ApiResponse::serverError('Error: ' . $e->getMessage());
}

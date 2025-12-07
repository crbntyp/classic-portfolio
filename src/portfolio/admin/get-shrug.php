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

$id = intval($_GET['id']);

try {
    // Fetch shrug entry data
    $stmt = $mysqli->prepare("SELECT id, title, slug, content, tags, published, sort_order, created_at FROM shrug_entries WHERE id = ?");

    if (!$stmt) {
        $mysqli->close();
        ApiResponse::serverError('Prepare failed: ' . $mysqli->error);
    }

    $stmt->bind_param("i", $id);
    $stmt->execute();

    // Use bind_result for server compatibility
    $stmt->bind_result($entryId, $title, $slug, $content, $tags, $published, $sortOrder, $createdAt);

    if ($stmt->fetch()) {
        $entry = [
            'id' => $entryId,
            'title' => $title,
            'slug' => $slug,
            'content' => $content,
            'tags' => $tags,
            'published' => $published,
            'sort_order' => $sortOrder,
            'created_at' => $createdAt
        ];

        $stmt->close();
        $mysqli->close();

        ApiResponse::success(['entry' => $entry]);
    } else {
        $stmt->close();
        $mysqli->close();
        ApiResponse::notFound('Shrug entry not found');
    }
} catch (Exception $e) {
    ApiResponse::serverError('Error: ' . $e->getMessage());
}

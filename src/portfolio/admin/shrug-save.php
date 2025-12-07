<?php
error_reporting(0);
ini_set('display_errors', 0);

// Check authentication (includes session initialization and API response helper)
require_once 'auth-check.php';
require_once '../includes/connection.php';

// Validate request method
ApiResponse::requireMethod('POST');

// Check database connection
if (!isset($mysqli) || !$mysqli) {
    ApiResponse::serverError('Database connection failed');
}

// Get form data
$id = isset($_POST['id']) ? intval($_POST['id']) : 0;
$title = isset($_POST['title']) ? trim($_POST['title']) : '';
$slug = isset($_POST['slug']) ? trim($_POST['slug']) : '';
$content = isset($_POST['content']) ? $_POST['content'] : '';
$tags = isset($_POST['tags']) ? trim($_POST['tags']) : '';
$published = isset($_POST['published']) ? 1 : 0;

// Validate required fields
if (empty($title)) {
    ApiResponse::error('Title is required');
}

if (empty($slug)) {
    // Auto-generate slug from title if not provided
    $slug = strtolower(preg_replace('/[^a-zA-Z0-9]+/', '-', $title));
    $slug = trim($slug, '-');
}

try {
    if ($id > 0) {
        // Update existing entry
        $stmt = $mysqli->prepare("UPDATE shrug_entries SET title = ?, slug = ?, content = ?, tags = ?, published = ? WHERE id = ?");

        if (!$stmt) {
            $mysqli->close();
            ApiResponse::serverError('Prepare failed: ' . $mysqli->error);
        }

        $stmt->bind_param("ssssis", $title, $slug, $content, $tags, $published, $id);
        $success = $stmt->execute();
        $stmt->close();

        if ($success) {
            $mysqli->close();
            ApiResponse::success(['id' => $id], 'Shrug entry updated successfully');
        } else {
            $mysqli->close();
            ApiResponse::error('Failed to update entry');
        }
    } else {
        // Insert new entry
        // Get max sort_order to add new entry at the end
        $maxOrderResult = $mysqli->query("SELECT MAX(sort_order) as max_order FROM shrug_entries");
        $maxOrder = 0;
        if ($maxOrderResult && $row = $maxOrderResult->fetch_assoc()) {
            $maxOrder = intval($row['max_order']) + 1;
        }

        $stmt = $mysqli->prepare("INSERT INTO shrug_entries (title, slug, content, tags, published, sort_order, created_at) VALUES (?, ?, ?, ?, ?, ?, NOW())");

        if (!$stmt) {
            $mysqli->close();
            ApiResponse::serverError('Prepare failed: ' . $mysqli->error);
        }

        $stmt->bind_param("ssssis", $title, $slug, $content, $tags, $published, $maxOrder);
        $success = $stmt->execute();
        $newId = $mysqli->insert_id;
        $stmt->close();
        $mysqli->close();

        if ($success) {
            ApiResponse::success(['id' => $newId], 'Shrug entry created successfully');
        } else {
            ApiResponse::error('Failed to create entry');
        }
    }
} catch (Exception $e) {
    ApiResponse::serverError('Error: ' . $e->getMessage());
}

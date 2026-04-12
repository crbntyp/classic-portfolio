<?php
require_once __DIR__ . '/config.php';

$method = $_SERVER['REQUEST_METHOD'];

// GET - public, returns all artworks
if ($method === 'GET') {
    // Auth check endpoint for dashboard
    if (isset($_GET['auth_check'])) {
        requireAuth();
        jsonResponse(['success' => true]);
    }

    $result = $mysqli->query('SELECT id, title, category, image, url, description, sort_order FROM artworks ORDER BY sort_order ASC');
    $artworks = [];
    while ($row = $result->fetch_assoc()) {
        $artworks[] = $row;
    }
    jsonResponse($artworks);
}

// All other methods require auth
requireAuth();

// POST - create
if ($method === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    $title = trim($input['title'] ?? '');
    $category = $input['category'] ?? 'artwork';
    $image = $input['image'] ?? '';
    $url = trim($input['url'] ?? '') ?: null;
    $description = trim($input['description'] ?? '') ?: null;

    if (!$title || !$image) {
        jsonResponse(['success' => false, 'message' => 'Title and image required'], 400);
    }

    if (!in_array($category, ['artwork', 'project'])) {
        $category = 'artwork';
    }

    // Get next sort order
    $maxOrder = $mysqli->query('SELECT COALESCE(MAX(sort_order), -1) + 1 as next_order FROM artworks');
    $sortOrder = $maxOrder->fetch_assoc()['next_order'];

    $stmt = $mysqli->prepare('INSERT INTO artworks (title, category, image, url, description, sort_order) VALUES (?, ?, ?, ?, ?, ?)');
    $stmt->bind_param('sssssi', $title, $category, $image, $url, $description, $sortOrder);
    $stmt->execute();
    $id = $stmt->insert_id;
    $stmt->close();

    jsonResponse(['success' => true, 'id' => $id], 201);
}

// PUT - update
if ($method === 'PUT') {
    $id = intval($_GET['id'] ?? 0);
    if (!$id) jsonResponse(['success' => false, 'message' => 'ID required'], 400);

    $input = json_decode(file_get_contents('php://input'), true);
    $title = trim($input['title'] ?? '');
    $category = $input['category'] ?? 'artwork';
    $image = $input['image'] ?? '';
    $url = trim($input['url'] ?? '') ?: null;
    $description = trim($input['description'] ?? '') ?: null;

    if (!$title) {
        jsonResponse(['success' => false, 'message' => 'Title required'], 400);
    }

    if (!in_array($category, ['artwork', 'project'])) {
        $category = 'artwork';
    }

    $stmt = $mysqli->prepare('UPDATE artworks SET title = ?, category = ?, image = ?, url = ?, description = ? WHERE id = ?');
    $stmt->bind_param('sssssi', $title, $category, $image, $url, $description, $id);
    $stmt->execute();
    $stmt->close();

    jsonResponse(['success' => true]);
}

// DELETE
if ($method === 'DELETE') {
    $id = intval($_GET['id'] ?? 0);
    if (!$id) jsonResponse(['success' => false, 'message' => 'ID required'], 400);

    // Get image filename to delete
    $stmt = $mysqli->prepare('SELECT image FROM artworks WHERE id = ?');
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $artwork = $result->fetch_assoc();
    $stmt->close();

    if ($artwork) {
        $imagePath = dirname(__DIR__) . '/uploads/' . $artwork['image'];
        if (file_exists($imagePath)) {
            unlink($imagePath);
        }

        $stmt = $mysqli->prepare('DELETE FROM artworks WHERE id = ?');
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $stmt->close();
    }

    jsonResponse(['success' => true]);
}

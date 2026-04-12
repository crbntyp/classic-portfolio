<?php
require_once __DIR__ . '/config.php';
requireAuth();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(['success' => false, 'message' => 'POST required'], 405);
}

$input = json_decode(file_get_contents('php://input'), true);
$order = $input['order'] ?? [];

if (empty($order)) {
    jsonResponse(['success' => false, 'message' => 'Order data required'], 400);
}

$stmt = $mysqli->prepare('UPDATE artworks SET sort_order = ? WHERE id = ?');

foreach ($order as $item) {
    $id = intval($item['id']);
    $sortOrder = intval($item['sort_order']);
    $stmt->bind_param('ii', $sortOrder, $id);
    $stmt->execute();
}

$stmt->close();

jsonResponse(['success' => true]);

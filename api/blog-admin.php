<?php
/*
 * Writing, from the other side. Everything here needs a session.
 *
 *   GET  blog-admin.php            every post, drafts included
 *   GET  blog-admin.php?id=<id>    one post, with its body
 *   POST blog-admin.php?do=save    create or update
 *   POST blog-admin.php?do=delete  remove one
 *
 * The table is shrug_entries, which is what it has always been called. The
 * section is called The Undo Stack on screen; renaming a live table with real
 * rows in it to match a label would be work with no upside.
 */

require_once __DIR__ . '/config.php';

requireAuth();

/** A slug is derived from the title unless one is given, and must be unique. */
function makeSlug(mysqli $db, string $wanted, string $fallback, ?int $ignoreId): string
{
    $base = strtolower(trim(preg_replace('/[^a-z0-9]+/i', '-', $wanted !== '' ? $wanted : $fallback), '-'));
    $base = mb_substr($base, 0, 200) ?: 'post';

    $slug = $base;
    $suffix = 2;
    while (true) {
        $sql = 'SELECT id FROM shrug_entries WHERE slug = ?' . ($ignoreId ? ' AND id <> ?' : '');
        $stmt = $db->prepare($sql);
        if ($ignoreId) {
            $stmt->bind_param('si', $slug, $ignoreId);
        } else {
            $stmt->bind_param('s', $slug);
        }
        $stmt->execute();
        if (!$stmt->get_result()->fetch_row()) {
            return $slug;
        }
        // Taken — walk until it is not. Slugs are addresses; two posts cannot
        // share one, and silently overwriting the other post is not an option.
        $slug = $base . '-' . $suffix++;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $id = isset($_GET['id']) ? (int) $_GET['id'] : null;

    if ($id) {
        $stmt = $mysqli->prepare(
            'SELECT id, title, slug, content, tags, published, sort_order, created_at
             FROM shrug_entries WHERE id = ?'
        );
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        if (!$row) {
            jsonResponse(['error' => 'No post with that id.'], 404);
        }
        $row['published'] = (bool) $row['published'];
        jsonResponse($row);
    }

    $rows = $mysqli->query(
        'SELECT id, title, slug, tags, published, sort_order, created_at,
                CHAR_LENGTH(content) AS chars
         FROM shrug_entries ORDER BY created_at DESC, id DESC'
    );
    $posts = [];
    while ($row = $rows->fetch_assoc()) {
        $row['published'] = (bool) $row['published'];
        $row['words'] = (int) round($row['chars'] / 5.6); // rough, and only for a read time
        $posts[] = $row;
    }
    jsonResponse($posts);
}

$action = $_GET['do'] ?? null;

if ($action === 'save') {
    $id = (int) ($_POST['id'] ?? 0) ?: null;
    $title = trim((string) ($_POST['title'] ?? ''));
    $content = (string) ($_POST['content'] ?? '');
    $tags = trim((string) ($_POST['tags'] ?? ''));
    $published = ($_POST['published'] ?? '0') === '1' ? 1 : 0;

    if ($title === '') {
        jsonResponse(['success' => false, 'message' => 'A post needs a title.'], 400);
    }

    $slug = makeSlug($mysqli, trim((string) ($_POST['slug'] ?? '')), $title, $id);

    if ($id) {
        $stmt = $mysqli->prepare(
            'UPDATE shrug_entries SET title = ?, slug = ?, content = ?, tags = ?, published = ?
             WHERE id = ?'
        );
        $stmt->bind_param('ssssii', $title, $slug, $content, $tags, $published, $id);
        $stmt->execute();
    } else {
        $stmt = $mysqli->prepare(
            'INSERT INTO shrug_entries (title, slug, content, tags, published, sort_order)
             VALUES (?, ?, ?, ?, ?, 0)'
        );
        $stmt->bind_param('ssssi', $title, $slug, $content, $tags, $published);
        $stmt->execute();
        $id = $mysqli->insert_id;
    }

    jsonResponse(['success' => true, 'id' => $id, 'slug' => $slug]);
}

if ($action === 'delete') {
    $id = (int) ($_POST['id'] ?? 0);
    if ($id < 1) {
        jsonResponse(['success' => false, 'message' => 'Which post?'], 400);
    }
    $stmt = $mysqli->prepare('DELETE FROM shrug_entries WHERE id = ?');
    $stmt->bind_param('i', $id);
    $stmt->execute();
    jsonResponse(['success' => true, 'deleted' => $stmt->affected_rows]);
}

jsonResponse(['success' => false, 'message' => 'Unknown action.'], 400);

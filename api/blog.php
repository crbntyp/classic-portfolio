<?php
/*
 * The writing.
 *
 *   GET blog.php              every published post, newest first
 *   GET blog.php?slug=<slug>  one post, with its body
 *
 * Unlike the work, this lives in this site's own database rather than on
 * Behance — it is written here, so it belongs here. The table is still called
 * shrug_entries: renaming a live table with real rows in it buys nothing, and
 * what a section is called on screen is not the schema's business.
 */

require_once __DIR__ . '/config.php';

/** Comma-separated in one column; a list everywhere else. */
function tagList(?string $tags): array
{
    if (!$tags) {
        return [];
    }
    return array_values(array_filter(array_map('trim', explode(',', $tags))));
}

/** First couple of sentences, stripped of markup, for a listing. */
function excerpt(string $html, int $limit = 190): string
{
    $text = trim(preg_replace('/\s+/', ' ', strip_tags($html)));
    if (mb_strlen($text) <= $limit) {
        return $text;
    }
    $cut = mb_substr($text, 0, $limit);
    $lastSpace = mb_strrpos($cut, ' ');
    return rtrim($lastSpace ? mb_substr($cut, 0, $lastSpace) : $cut, " ,.;:") . '…';
}

$slug = $_GET['slug'] ?? null;

if ($slug !== null) {
    $stmt = $mysqli->prepare(
        'SELECT title, slug, content, tags, created_at
         FROM shrug_entries WHERE slug = ? AND published = 1 LIMIT 1'
    );
    $stmt->bind_param('s', $slug);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    if (!$row) {
        jsonResponse(['error' => 'No post with that slug.'], 404);
    }
    jsonResponse([
        'title' => $row['title'],
        'slug' => $row['slug'],
        // First-party HTML, written through this site's own admin.
        'content' => $row['content'],
        'tags' => tagList($row['tags']),
        'published' => $row['created_at'],
    ]);
}

$result = $mysqli->query(
    'SELECT title, slug, content, tags, created_at
     FROM shrug_entries WHERE published = 1 ORDER BY created_at DESC, id DESC'
);

$posts = [];
while ($row = $result->fetch_assoc()) {
    $posts[] = [
        'title' => $row['title'],
        'slug' => $row['slug'],
        'excerpt' => excerpt($row['content']),
        'tags' => tagList($row['tags']),
        'published' => $row['created_at'],
        // Enough to say "a four minute read" without shipping the whole body.
        'words' => str_word_count(strip_tags($row['content'])),
    ];
}

jsonResponse($posts);

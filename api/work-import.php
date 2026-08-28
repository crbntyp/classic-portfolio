<?php
/*
 * Brings the client work into work_items — the 38 rows that have been sitting
 * in `projects` since the old site, none of which is on Behance.
 *
 *   php work-import.php
 *
 * Safe to run twice: it upserts on (source, source_ref) and, like a Behance
 * pull, leaves the admin-owned columns alone. The original table is not
 * touched, so this is reversible by deleting the imported rows.
 */

if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit("This is a one-off import, not an endpoint.\n");
}

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/work-schema.php';

ensureWorkTable($mysqli);

$rows = $mysqli->query(
    'SELECT projectID, projectHeading, projectTeaser, projectDescription, url, url_two, sort_order, display_order
     FROM projects ORDER BY sort_order, projectID'
);

$count = 0;
while ($row = $rows->fetch_assoc()) {
    // "javascript:void(0)" is how the old site marked a project with no link.
    $url = trim((string) ($row['url'] ?? ''));
    if ($url === '' || str_starts_with($url, 'javascript:')) {
        $url = null;
    }

    $subtitle = trim((string) ($row['projectTeaser'] ?? ''));
    if ($subtitle === '') {
        $subtitle = mb_substr(trim(strip_tags((string) ($row['projectDescription'] ?? ''))), 0, 400) ?: null;
    }

    upsertSyncedItem($mysqli, [
        'source' => 'client',
        'source_ref' => (string) $row['projectID'],
        'title' => $row['projectHeading'],
        'subtitle' => $subtitle,
        'url' => $url,
        // Client work has no cover here: the images in uploads/ were never
        // linked to these rows by any column. A banner set in the admin is
        // what gives them a face.
        'cover_url' => null,
        'accent' => null,
        'published_at' => null,
        'payload' => json_encode(['description' => $row['projectDescription'] ?? null, 'url_two' => $row['url_two'] ?? null]),
        'kind' => 'client',
        'sort_order' => (int) ($row['sort_order'] ?: $row['display_order'] ?: 0),
        'synced_at' => time(),
    ]);
    $count++;
}

echo "client projects imported: {$count}\n";

<?php
/*
 * The work, as crbntyp.com reads it.
 *
 *   GET behance.php                        every project
 *   GET behance.php?project=<id>           one project, with its case study
 *
 * This never contacts Behance. `behance-sync.php` does that on a schedule and
 * leaves the result in the cache — because Behance rate limits, refuses PHP's
 * curl outright, and a visitor should never wait on someone else's site to
 * render this one. If the cache is empty it says so plainly rather than
 * pretending there is no work.
 */

require_once __DIR__ . '/config.php';

const BEHANCE_USER = 'jonnypyper';

function readCache(mysqli $db, string $key): ?array
{
    $stmt = $db->prepare('SELECT body, fetched_at FROM behance_cache WHERE cache_key = ?');
    if (!$stmt) {
        return null;
    }
    $stmt->bind_param('s', $key);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    return $row ?: null;
}

$id = $_GET['project'] ?? null;
$key = $id === null ? 'list:' . BEHANCE_USER : 'project:' . preg_replace('/\D/', '', (string) $id);

$row = readCache($mysqli, $key);
if (!$row) {
    jsonResponse([
        'error' => $id === null
            ? 'The work has not been synced yet.'
            : 'That project has not been synced yet.',
    ], 503);
}

header('X-Behance-Synced: ' . $row['fetched_at']);
// Age matters to nobody but a debugger; the body already carries the timestamp.
echo $row['body'];

<?php
/*
 * The work, as the site reads it: Behance and client projects in one list,
 * ordered and filtered the way the admin left them.
 *
 * Only visible rows come out. Ordering is the admin's sort_order first, then
 * newest published, then title — so an item nobody has ordered still lands
 * somewhere sensible rather than at random. The categories come too, because
 * the site groups the work by them and titles each group with their label.
 */

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/work-schema.php';

ensureWorkTable($mysqli);
ensureCategories($mysqli);

$rows = $mysqli->query(
    'SELECT source, source_ref, title, subtitle, url, cover_url, accent,
            published_at, banner_url, intro, features, live_url, kind, sort_order, payload, tags
     FROM work_items
     WHERE visible = 1
     ORDER BY sort_order ASC, published_at DESC, title ASC'
);

$items = [];
while ($row = $rows->fetch_assoc()) {
    $payload = $row['payload'] ? json_decode($row['payload'], true) : [];

    // A banner set in the admin wins over whatever the source supplied — that
    // is the point of it, and it is the only image client work has.
    $banner = $row['banner_url'] ?: null;

    $items[] = [
        'source' => $row['source'],
        // Behance items are addressed by their project id, which is what a
        // project page needs to fetch its case study.
        'id' => $row['source_ref'],
        'name' => $row['title'],
        'subtitle' => $row['subtitle'],
        // Written here, about the work done — it leads a project page ahead of
        // anything Behance had to say.
        'intro' => $row['intro'],
        // One per line as written, a list by the time it gets here. Empty for
        // everything that is not an app, which is why the panel can be absent.
        'features' => splitLines($row['features']),
        'slug' => $payload['slug'] ?? null,
        'url' => $row['url'],
        'cover' => $banner ?: ($payload['cover'] ?? $row['cover_url']),
        'coverLarge' => $banner ?: ($payload['coverLarge'] ?? $row['cover_url']),
        'hasOwnBanner' => (bool) $banner,
        'accent' => $row['accent'],
        'liveUrl' => $row['live_url'],
        'kind' => $row['kind'],
        // Epoch, the way the front end already reads Behance dates.
        'published' => $row['published_at'] ? strtotime($row['published_at']) : null,
        'views' => $payload['views'] ?? 0,
        // Renamed where the admin renamed them, and dropped where it hid them.
        // Behance drives these. Title cased on the way out, and nothing here
        // overrides them — the tag on the project is the tag on the site.
        'tags' => array_map('titleTag', splitTags($row['tags'])),
    ];
}

/*
 * The categories travel with the work, in the order the admin put them: the
 * site groups by them and titles the groups with their labels, so adding
 * "Engineering" in the admin puts an Engineering section on the page without
 * anyone editing the front end.
 */
$categories = [];
$rows = $mysqli->query('SELECT slug, label FROM work_categories ORDER BY sort_order, id');
while ($row = $rows->fetch_assoc()) {
    $categories[] = $row;
}

jsonResponse(['categories' => $categories, 'items' => $items]);

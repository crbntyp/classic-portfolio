<?php
/*
 * The bits of the site that are neither work nor writing: who this is, and
 * where else to find him.
 *
 *   GET site.php    about copy, the project history, and the social links
 *
 * It reads site_settings, the key/value table that has been there all along.
 * Copy belongs in the database rather than in the page so it can be edited
 * without a deploy — the same reasoning as everything else here.
 */

require_once __DIR__ . '/config.php';

/** One setting, or null. */
function setting(mysqli $db, string $key): ?string
{
    $stmt = $db->prepare('SELECT setting_value FROM site_settings WHERE setting_key = ?');
    $stmt->bind_param('s', $key);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_row();
    return $row ? $row[0] : null;
}

/*
 * The heading and standfirst of each page. They were written into the markup,
 * which made them the only copy on the site that needed a deploy to change —
 * every other word here has been editable for a while.
 */
function pageHead(mysqli $db, string $page): array
{
    return [
        'title' => setting($db, "head_{$page}_title") ?? '',
        'lede' => setting($db, "head_{$page}_lede") ?? '',
    ];
}

$about = setting($mysqli, 'about_html');
$history = setting($mysqli, 'work_history');
$socials = setting($mysqli, 'social_links');

jsonResponse([
    'heads' => [
        'work' => pageHead($mysqli, 'work'),
        'writing' => pageHead($mysqli, 'writing'),
        'about' => pageHead($mysqli, 'about'),
    ],
    'about' => $about,
    // A list of "year\tname" lines — plain enough to edit by hand, structured
    // enough to render as a table.
    'history' => array_values(array_filter(array_map(
        static function (string $line): ?array {
            $parts = explode("\t", trim($line), 2);
            return count($parts) === 2 ? ['year' => trim($parts[0]), 'name' => trim($parts[1])] : null;
        },
        explode("\n", (string) $history)
    ))),
    'socials' => $socials ? (json_decode($socials, true) ?: []) : [],
]);

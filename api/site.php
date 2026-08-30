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

/*
 * The track behind the nav's now-playing control.
 *
 * now_playing_src is either a filename or '*' for random. Random is resolved
 * here rather than in the page, so the browser is never handed a directory
 * listing and the choice cannot be steered from the client.
 *
 * Credits come from now_playing_library, a filename → {title, artist} map
 * written by the admin. They have to travel per file: with random playback a
 * single stored title would caption whichever track happened to come up,
 * which is worse than no caption. The old flat settings are the fallback so
 * a library that has not been saved yet still names its one track.
 */
function nowPlaying(mysqli $db): array
{
    $src = setting($db, 'now_playing_src') ?: null;
    $library = json_decode((string) setting($db, 'now_playing_library'), true);
    if (!is_array($library)) $library = [];

    if ($src === '*') {
        // Only files that are actually there — a library entry whose file has
        // been removed must never be picked.
        $onDisk = array_map('basename', glob(__DIR__ . '/../../audio/*.{mp3,m4a,mp4}', GLOB_BRACE) ?: []);
        $src = $onDisk ? $onDisk[random_int(0, count($onDisk) - 1)] : null;
    }

    if ($src === null) {
        return ['src' => null, 'title' => null, 'artist' => null];
    }

    $entry = $library[$src] ?? [];
    return [
        'src' => $src,
        'title' => ($entry['title'] ?? '') ?: (setting($db, 'now_playing_title') ?: null),
        'artist' => ($entry['artist'] ?? '') ?: (setting($db, 'now_playing_artist') ?: null),
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
    /*
     * The track behind the nav's now-playing control. A filename rather than
     * a path — the page prefixes /audio/ — so a stored value can never point
     * somewhere else on the server.
     */
    'now_playing' => nowPlaying($mysqli),
]);

<?php
/*
 * The About page, from the other side. Needs a session.
 *
 *   GET  site-admin.php           page headings, the about copy, history, socials
 *   POST site-admin.php?do=save   write them back
 *
 * All three live in site_settings, the key/value table that predates this.
 * Nothing here is synced from anywhere — it is the one part of the site that
 * is purely yours to write.
 */

require_once __DIR__ . '/config.php';

requireAuth();

function readSetting(mysqli $db, string $key): ?string
{
    $stmt = $db->prepare('SELECT setting_value FROM site_settings WHERE setting_key = ?');
    $stmt->bind_param('s', $key);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_row();
    return $row ? $row[0] : null;
}

function writeSetting(mysqli $db, string $key, string $value): void
{
    $stmt = $db->prepare(
        'INSERT INTO site_settings (setting_key, setting_value) VALUES (?, ?)
         ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)'
    );
    $stmt->bind_param('ss', $key, $value);
    $stmt->execute();
}

/* The three pages whose heading and standfirst are editable. */
const HEAD_PAGES = ['work', 'writing', 'about'];

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $heads = [];
    foreach (HEAD_PAGES as $page) {
        $heads[$page] = [
            'title' => readSetting($mysqli, "head_{$page}_title") ?? '',
            'lede' => readSetting($mysqli, "head_{$page}_lede") ?? '',
        ];
    }

    jsonResponse([
        'heads' => $heads,
        'about' => readSetting($mysqli, 'about_html') ?? '',
        // Tab separated, one per line: plain enough to paste into and edit.
        'history' => readSetting($mysqli, 'work_history') ?? '',
        'socials' => readSetting($mysqli, 'social_links') ?? '[]',
    ]);
}

if (($_GET['do'] ?? null) !== 'save') {
    jsonResponse(['success' => false, 'message' => 'Unknown action.'], 400);
}

/*
 * Plain text, not markup: these are a heading and a sentence under it, and the
 * page escapes them on the way out. Anything wanting emphasis belongs in the
 * body copy below, which does take HTML.
 */
foreach (HEAD_PAGES as $page) {
    foreach (['title', 'lede'] as $part) {
        $field = "head_{$page}_{$part}";
        if (array_key_exists($field, $_POST)) {
            writeSetting($mysqli, $field, trim((string) $_POST[$field]));
        }
    }
}

if (array_key_exists('about', $_POST)) {
    writeSetting($mysqli, 'about_html', (string) $_POST['about']);
}

/*
 * The now-playing track. Stored as a bare filename and validated as one:
 * this is behind a session, but a setting that the public page turns into a
 * URL should never be able to carry a path out of the audio folder.
 */
if (array_key_exists('now_playing_src', $_POST)) {
    $src = trim((string) $_POST['now_playing_src']);
    if ($src !== '' && !preg_match('/^[A-Za-z0-9._-]+\.mp3$/', $src)) {
        jsonResponse(['success' => false, 'message' => 'That is not an audio file name.'], 400);
    }
    writeSetting($mysqli, 'now_playing_src', $src);
}

if (array_key_exists('now_playing_artist', $_POST)) {
    writeSetting($mysqli, 'now_playing_artist', trim((string) $_POST['now_playing_artist']));
}

if (array_key_exists('history', $_POST)) {
    writeSetting($mysqli, 'work_history', trim((string) $_POST['history']));
}

if (array_key_exists('socials', $_POST)) {
    // Stored as JSON, so a broken list would take the About page down with it.
    $socials = json_decode((string) $_POST['socials'], true);
    if (!is_array($socials)) {
        jsonResponse(['success' => false, 'message' => 'The links are not valid JSON.'], 400);
    }

    $clean = [];
    foreach ($socials as $link) {
        $name = trim((string) ($link['name'] ?? ''));
        $url = trim((string) ($link['url'] ?? ''));
        if ($name === '' || !preg_match('#^https?://#i', $url)) {
            continue;
        }
        $clean[] = ['name' => $name, 'url' => $url];
    }
    writeSetting($mysqli, 'social_links', json_encode(array_values($clean)));
}

jsonResponse(['success' => true]);

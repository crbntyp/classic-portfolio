<?php
/*
 * The uploads folder, as something you can manage rather than something that
 * only fills up. Everything here needs a session.
 *
 *   GET  media.php               every file in uploads/, newest first
 *   POST media.php?do=upload     add one or more
 *   POST media.php?do=delete     remove one, if nothing points at it
 *
 * Until now a file could only arrive attached to a work item, which made the
 * folder a place banners happened to land rather than a library you could put
 * a picture in and use in a post.
 *
 * "In use" is asked of the two places a URL can be written: a work item's
 * banner, and the body of anything in the writing. A file nothing references is
 * the only kind that can be deleted — an image sitting in a published post is
 * not rubbish just because nobody remembers uploading it.
 */

require_once __DIR__ . '/config.php';

requireAuth();

const MEDIA_DIR = __DIR__ . '/../uploads';
const MEDIA_URL = '/portfolio/uploads';
const MEDIA_MAX_BYTES = 8 * 1024 * 1024;

/** What the browser sent, whatever it called itself. */
const MEDIA_TYPES = [
    'image/jpeg' => 'jpg',
    'image/png' => 'png',
    'image/webp' => 'webp',
    'image/avif' => 'avif',
    'image/gif' => 'gif',
    'image/svg+xml' => 'svg',
];

/** Every upload URL that something currently points at. */
function mediaInUse(mysqli $db): array
{
    $used = [];

    $rows = $db->query("SELECT banner_url FROM work_items WHERE banner_url IS NOT NULL AND banner_url <> ''");
    while ($row = $rows->fetch_row()) {
        $used[basename($row[0])] = true;
    }

    // Anything written into a post body. Matched on the filename rather than
    // the whole URL, so a copy pasted with the domain on the front still counts.
    $rows = $db->query('SELECT content FROM shrug_entries');
    while ($row = $rows->fetch_row()) {
        if (preg_match_all('#/uploads/([A-Za-z0-9._-]+)#', (string) $row[0], $found)) {
            foreach ($found[1] as $name) {
                $used[$name] = true;
            }
        }
    }

    // And the page copy, which can carry markup too.
    $rows = $db->query('SELECT setting_value FROM site_settings');
    while ($row = $rows->fetch_row()) {
        if (preg_match_all('#/uploads/([A-Za-z0-9._-]+)#', (string) $row[0], $found)) {
            foreach ($found[1] as $name) {
                $used[$name] = true;
            }
        }
    }

    return $used;
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $used = mediaInUse($mysqli);
    $files = [];

    foreach (glob(MEDIA_DIR . '/*') ?: [] as $path) {
        if (!is_file($path)) {
            continue;
        }
        $name = basename($path);
        if ($name[0] === '.') {
            continue;
        }
        $size = filesize($path) ?: 0;
        $dimensions = @getimagesize($path);

        $files[] = [
            'file' => $name,
            'url' => MEDIA_URL . '/' . rawurlencode($name),
            'bytes' => $size,
            'size' => $size > 1048576
                ? round($size / 1048576, 1) . ' MB'
                : max(1, (int) round($size / 1024)) . ' KB',
            'width' => $dimensions[0] ?? null,
            'height' => $dimensions[1] ?? null,
            'added' => filemtime($path) ?: 0,
            'used' => isset($used[$name]),
        ];
    }

    // Newest first: the one you just added is the one you want.
    usort($files, static fn($a, $b) => $b['added'] <=> $a['added']);

    jsonResponse([
        'files' => $files,
        'limit' => ini_get('upload_max_filesize') ?: '8M',
    ]);
}

$action = $_GET['do'] ?? null;

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(['success' => false, 'message' => 'POST required.'], 405);
}

if ($action === 'upload') {
    $sent = $_FILES['files'] ?? null;
    if (!$sent || !isset($sent['name'])) {
        jsonResponse(['success' => false, 'message' => 'No files arrived.'], 400);
    }

    // One file and several arrive in different shapes; normalise to a list.
    $names = (array) $sent['name'];
    $count = count($names);

    $reasons = [
        UPLOAD_ERR_INI_SIZE => 'bigger than the server accepts',
        UPLOAD_ERR_FORM_SIZE => 'bigger than the form allows',
        UPLOAD_ERR_PARTIAL => 'interrupted on the way up',
        UPLOAD_ERR_NO_FILE => 'no file arrived',
        UPLOAD_ERR_NO_TMP_DIR => 'the server has nowhere to put it',
        UPLOAD_ERR_CANT_WRITE => 'the server could not write it',
        UPLOAD_ERR_EXTENSION => 'a PHP extension refused it',
    ];

    if (!is_dir(MEDIA_DIR) && !@mkdir(MEDIA_DIR, 0775, true)) {
        jsonResponse(['success' => false, 'message' => 'The uploads directory is missing.'], 500);
    }

    $added = [];
    $failed = [];

    for ($i = 0; $i < $count; $i++) {
        $name = is_array($sent['name']) ? $sent['name'][$i] : $sent['name'];
        $tmp = is_array($sent['tmp_name']) ? $sent['tmp_name'][$i] : $sent['tmp_name'];
        $error = is_array($sent['error']) ? $sent['error'][$i] : $sent['error'];
        $size = is_array($sent['size']) ? $sent['size'][$i] : $sent['size'];

        if ($error !== UPLOAD_ERR_OK) {
            $failed[] = $name . ' — ' . ($reasons[$error] ?? "failed (code {$error})");
            continue;
        }
        if ($size > MEDIA_MAX_BYTES) {
            $failed[] = $name . ' — over ' . round(MEDIA_MAX_BYTES / 1048576) . 'MB';
            continue;
        }

        // Trust the file, not its name: an extension is whatever someone typed.
        $mime = (new finfo(FILEINFO_MIME_TYPE))->file($tmp);
        if (!isset(MEDIA_TYPES[$mime])) {
            $failed[] = $name . ' — not an image';
            continue;
        }

        /*
         * The uploader's own name, cleaned, so the folder stays readable —
         * "belfast-metro-3.png" says more than a hash. A counter goes on the
         * end rather than overwriting, because two files with the same name are
         * two files.
         */
        $stem = pathinfo($name, PATHINFO_FILENAME);
        $stem = strtolower(preg_replace('/[^A-Za-z0-9]+/', '-', $stem));
        $stem = trim($stem, '-') ?: 'image';
        $stem = substr($stem, 0, 60);
        $extension = MEDIA_TYPES[$mime];

        $file = $stem . '.' . $extension;
        $n = 2;
        while (file_exists(MEDIA_DIR . '/' . $file)) {
            $file = $stem . '-' . $n++ . '.' . $extension;
        }

        if (!move_uploaded_file($tmp, MEDIA_DIR . '/' . $file)) {
            $failed[] = $name . ' — could not be saved';
            continue;
        }
        @chmod(MEDIA_DIR . '/' . $file, 0644);

        $added[] = ['file' => $file, 'url' => MEDIA_URL . '/' . rawurlencode($file)];
    }

    jsonResponse([
        'success' => (bool) $added,
        'added' => $added,
        'failed' => $failed,
        'message' => $added
            ? count($added) . ' added' . ($failed ? ', ' . count($failed) . ' refused' : '')
            : (implode('; ', $failed) ?: 'Nothing was added.'),
    ]);
}

if ($action === 'delete') {
    $name = basename((string) ($_POST['file'] ?? ''));
    if ($name === '' || $name[0] === '.') {
        jsonResponse(['success' => false, 'message' => 'Which file?'], 400);
    }

    if (isset(mediaInUse($mysqli)[$name])) {
        jsonResponse([
            'success' => false,
            'message' => 'Something still uses that — a banner or a post. Change it there first.',
        ], 409);
    }

    // basename above, and then checked to land inside uploads: the name came
    // from a request and is about to be handed to unlink.
    $path = MEDIA_DIR . '/' . $name;
    $real = realpath($path);
    if (!$real || !str_starts_with($real, realpath(MEDIA_DIR) . '/') || !is_file($real)) {
        jsonResponse(['success' => false, 'message' => 'No such file.'], 404);
    }

    jsonResponse(['success' => unlink($real), 'message' => "Deleted {$name}."]);
}

jsonResponse(['success' => false, 'message' => 'Unknown action.'], 400);

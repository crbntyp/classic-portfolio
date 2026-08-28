<?php
/*
 * Managing the work. Everything here needs a session.
 *
 *   GET  work-admin.php                 every item, hidden ones included
 *   POST work-admin.php?do=update       one item: visible, kind, sort, intro,
 *                                       features, live url
 *   POST work-admin.php?do=banner       upload a banner image for one item
 *   POST work-admin.php?do=clear-banner drop it again
 *   GET  work-admin.php?do=uploads      what is already in uploads/
 *   POST work-admin.php?do=banner-existing assign one of those to an item
 *   POST work-admin.php?do=add-project  adopt a project by its gallery URL, or
 *                                       pull an existing one again on demand
 *   POST work-admin.php?do=delete       remove one for good
 *
 * Nothing here writes to Behance — it cannot, and would not want to. What it
 * manages is this site's opinion of what Behance sent: what to show, in what
 * order, under what banner.
 */

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/work-schema.php';

requireAuth();
ensureWorkTable($mysqli);
ensureCategories($mysqli);

const BANNER_DIR = __DIR__ . '/../uploads';
const BANNER_URL_BASE = '/portfolio/uploads';
/*
 * Whatever PHP will actually take, so the app never advertises a limit the
 * server will not honour. PHP rejects an oversized file before this code runs,
 * which is why the upload error above has to be reported properly.
 */
const MAX_BANNER_BYTES = 8 * 1024 * 1024;

$action = $_GET['do'] ?? null;

if ($action === null && $_SERVER['REQUEST_METHOD'] === 'GET') {
    $rows = $mysqli->query(
        'SELECT id, source, source_ref, title, subtitle, url, cover_url, banner_url,
                intro, features, live_url, kind, visible, sort_order, published_at, synced_at, gone_at
         FROM work_items ORDER BY sort_order ASC, published_at DESC, title ASC'
    );
    $items = [];
    while ($row = $rows->fetch_assoc()) {
        $row['visible'] = (bool) $row['visible'];
        $row['sort_order'] = (int) $row['sort_order'];
        $row['image'] = $row['banner_url'] ?: $row['cover_url'];
        $items[] = $row;
    }

    $categories = [];
    $catRows = $mysqli->query('SELECT slug, label FROM work_categories ORDER BY sort_order, id');
    while ($row = $catRows->fetch_assoc()) {
        $categories[] = $row;
    }

    jsonResponse(['items' => $items, 'categories' => $categories]);
}

// Everything writes except the uploads listing, which is a read like the one above.
if ($_SERVER['REQUEST_METHOD'] !== 'POST' && $action !== 'uploads') {
    jsonResponse(['success' => false, 'message' => 'POST required.'], 405);
}

/** Every write addresses one row by its id. */
function itemId(mysqli $db): int
{
    $id = (int) ($_POST['id'] ?? 0);
    if ($id < 1) {
        jsonResponse(['success' => false, 'message' => 'Which item?'], 400);
    }
    return $id;
}

if ($action === 'update') {
    $id = itemId($mysqli);

    // Only the columns the admin owns. A sync overwrites the rest, so letting
    // them be edited here would just be writing into the next pull's path.
    $sets = [];
    $types = '';
    $values = [];

    if (array_key_exists('visible', $_POST)) {
        $sets[] = 'visible = ?';
        $types .= 'i';
        $values[] = $_POST['visible'] === '1' || $_POST['visible'] === 'true' ? 1 : 0;
    }
    if (array_key_exists('kind', $_POST)) {
        $allowed = categorySlugs($mysqli);
        $kind = in_array($_POST['kind'], $allowed, true) ? $_POST['kind'] : ($allowed[0] ?? 'design');
        $sets[] = 'kind = ?';
        $types .= 's';
        $values[] = $kind;
    }
    if (array_key_exists('sort_order', $_POST)) {
        $sets[] = 'sort_order = ?';
        $types .= 'i';
        $values[] = (int) $_POST['sort_order'];
    }
    if (array_key_exists('intro', $_POST)) {
        $intro = trim((string) $_POST['intro']);
        $sets[] = 'intro = ?';
        $types .= 's';
        $values[] = $intro === '' ? null : $intro;
    }
    if (array_key_exists('features', $_POST)) {
        // Kept as typed, newlines and all — the line breaks *are* the data.
        // Only the outer whitespace goes, so a trailing return does not become
        // an empty feature on the page.
        $features = trim((string) $_POST['features']);
        $sets[] = 'features = ?';
        $types .= 's';
        $values[] = $features === '' ? null : $features;
    }
    if (array_key_exists('live_url', $_POST)) {
        $live = trim((string) $_POST['live_url']);
        $sets[] = 'live_url = ?';
        $types .= 's';
        $values[] = $live === '' ? null : $live;
    }

    if (!$sets) {
        jsonResponse(['success' => false, 'message' => 'Nothing to change.'], 400);
    }

    $types .= 'i';
    $values[] = $id;
    $stmt = $mysqli->prepare('UPDATE work_items SET ' . implode(', ', $sets) . ' WHERE id = ?');
    $stmt->bind_param($types, ...$values);
    $stmt->execute();

    jsonResponse(['success' => true]);
}

if ($action === 'banner') {
    $id = itemId($mysqli);
    $file = $_FILES['banner'] ?? null;

    /*
     * Every upload failure used to come back as "no file arrived", which is
     * only true for one of them. PHP knows exactly what went wrong, and a file
     * being too big is by far the most likely — so it says which.
     */
    $limit = ini_get('upload_max_filesize') ?: '8M';
    $reasons = [
        UPLOAD_ERR_INI_SIZE => "That image is bigger than the {$limit} this server accepts.",
        UPLOAD_ERR_FORM_SIZE => 'That image is bigger than the form allows.',
        UPLOAD_ERR_PARTIAL => 'The upload was interrupted — try again.',
        UPLOAD_ERR_NO_FILE => 'No file arrived.',
        UPLOAD_ERR_NO_TMP_DIR => 'The server has nowhere to put it — a temp directory is missing.',
        UPLOAD_ERR_CANT_WRITE => 'The server could not write it to disk.',
        UPLOAD_ERR_EXTENSION => 'A PHP extension refused the upload.',
    ];

    $error = $file['error'] ?? UPLOAD_ERR_NO_FILE;
    if (!$file || $error !== UPLOAD_ERR_OK) {
        jsonResponse([
            'success' => false,
            'message' => $reasons[$error] ?? "The upload failed (code {$error}).",
        ], 400);
    }

    if ($file['size'] > MAX_BANNER_BYTES) {
        jsonResponse([
            'success' => false,
            'message' => 'That image is over ' . round(MAX_BANNER_BYTES / 1048576) . 'MB.',
        ], 400);
    }

    // Trust the file, not its name: an extension is whatever the uploader typed.
    $mime = (new finfo(FILEINFO_MIME_TYPE))->file($file['tmp_name']);
    $extensions = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp',
        'image/avif' => 'avif',
    ];
    if (!isset($extensions[$mime])) {
        jsonResponse(['success' => false, 'message' => 'Images only (jpg, png, webp, avif).'], 400);
    }

    if (!is_dir(BANNER_DIR) && !@mkdir(BANNER_DIR, 0775, true)) {
        jsonResponse(['success' => false, 'message' => 'The uploads directory is missing.'], 500);
    }

    $name = 'banner-' . $id . '-' . bin2hex(random_bytes(6)) . '.' . $extensions[$mime];
    if (!move_uploaded_file($file['tmp_name'], BANNER_DIR . '/' . $name)) {
        jsonResponse(['success' => false, 'message' => 'The file could not be saved.'], 500);
    }
    @chmod(BANNER_DIR . '/' . $name, 0644);

    // Replacing a banner leaves the old file behind on purpose: nothing else
    // deletes from uploads/, and an orphaned image is cheaper than deleting
    // one something else turns out to be using.
    $url = BANNER_URL_BASE . '/' . $name;
    $stmt = $mysqli->prepare('UPDATE work_items SET banner_url = ? WHERE id = ?');
    $stmt->bind_param('si', $url, $id);
    $stmt->execute();

    jsonResponse(['success' => true, 'banner_url' => $url]);
}

if ($action === 'uploads') {
    /*
     * What is already sitting in uploads/. Most of it is orphaned — files left
     * behind by artwork rows that were deleted — and the client work has no
     * imagery of its own, so being able to assign one of these beats uploading
     * a copy of a file that is already on the server.
     */
    $used = [];
    $rows = $mysqli->query('SELECT banner_url FROM work_items WHERE banner_url IS NOT NULL');
    while ($row = $rows->fetch_row()) {
        $used[basename($row[0])] = true;
    }

    $files = [];
    foreach (glob(BANNER_DIR . '/*.{jpg,jpeg,png,webp,avif}', GLOB_BRACE) ?: [] as $path) {
        $name = basename($path);
        $files[] = [
            'file' => $name,
            'url' => BANNER_URL_BASE . '/' . $name,
            'bytes' => filesize($path),
            'used' => isset($used[$name]),
        ];
    }

    // Newest first: the ones you added most recently are the ones you are
    // most likely to be looking for.
    usort($files, static fn($a, $b) => strcmp($b['file'], $a['file']));

    jsonResponse($files);
}

if ($action === 'banner-existing') {
    $id = itemId($mysqli);

    // basename, then an existence check inside the directory itself — a name
    // arriving over POST is not to be trusted with a path.
    $file = basename((string) ($_POST['file'] ?? ''));
    if ($file === '' || !is_file(BANNER_DIR . '/' . $file)) {
        jsonResponse(['success' => false, 'message' => 'No such file in uploads.'], 400);
    }

    $url = BANNER_URL_BASE . '/' . $file;
    $stmt = $mysqli->prepare('UPDATE work_items SET banner_url = ? WHERE id = ?');
    $stmt->bind_param('si', $url, $id);
    $stmt->execute();

    jsonResponse(['success' => true, 'banner_url' => $url]);
}

if ($action === 'clear-banner') {
    $id = itemId($mysqli);
    $stmt = $mysqli->prepare('UPDATE work_items SET banner_url = NULL WHERE id = ?');
    $stmt->bind_param('i', $id);
    $stmt->execute();
    jsonResponse(['success' => true]);
}

if ($action === 'reorder') {
    /*
     * The whole order in one request. Dragging one row changes the position of
     * everything after it, so sending a row at a time would be a dozen writes
     * and a half-applied order if one of them failed.
     */
    $ids = $_POST['ids'] ?? null;
    if (!is_array($ids) || !$ids) {
        jsonResponse(['success' => false, 'message' => 'Send the order as ids[].'], 400);
    }

    $stmt = $mysqli->prepare('UPDATE work_items SET sort_order = ? WHERE id = ?');
    $mysqli->begin_transaction();
    foreach (array_values($ids) as $position => $id) {
        $itemId = (int) $id;
        if ($itemId < 1) {
            continue;
        }
        $stmt->bind_param('ii', $position, $itemId);
        $stmt->execute();
    }
    $mysqli->commit();

    jsonResponse(['success' => true, 'ordered' => count($ids)]);
}

if ($action === 'category-add') {
    $label = trim((string) ($_POST['label'] ?? ''));
    if ($label === '') {
        jsonResponse(['success' => false, 'message' => 'Give it a name.'], 400);
    }

    // The slug is derived, not typed: it ends up on every item row, and a
    // category called "Motion & Film" should not put that there.
    $slug = strtolower(trim(preg_replace('/[^a-z0-9]+/i', '-', $label), '-'));
    if ($slug === '' || mb_strlen($slug) > 32) {
        jsonResponse(['success' => false, 'message' => 'That name will not make a usable slug.'], 400);
    }

    /*
     * The slug is permanent once items are stored against it, so renaming a
     * category to "Website" leaves it stored as "app" — and adding "App" then
     * collides with a category you would not recognise from its label. Say
     * which one, and what to do about it.
     */
    $stmt = $mysqli->prepare('SELECT label FROM work_categories WHERE slug = ?');
    $stmt->bind_param('s', $slug);
    $stmt->execute();
    $clash = $stmt->get_result()->fetch_row();
    if ($clash) {
        jsonResponse([
            'success' => false,
            'message' => $clash[0] === $label
                ? "“{$label}” already exists."
                : "“{$clash[0]}” is already stored as “{$slug}” — rename that one to “{$label}” instead of adding a second.",
        ], 409);
    }

    $next = (int) $mysqli->query('SELECT COALESCE(MAX(sort_order), -1) + 1 FROM work_categories')->fetch_row()[0];
    $stmt = $mysqli->prepare('INSERT INTO work_categories (slug, label, sort_order) VALUES (?, ?, ?)');
    $stmt->bind_param('ssi', $slug, $label, $next);
    $stmt->execute();

    jsonResponse(['success' => true, 'slug' => $slug, 'label' => $label]);
}

if ($action === 'category-rename') {
    $slug = trim((string) ($_POST['slug'] ?? ''));
    $label = trim((string) ($_POST['label'] ?? ''));
    if ($slug === '' || $label === '') {
        jsonResponse(['success' => false, 'message' => 'Needs a category and a name.'], 400);
    }

    /*
     * The slug follows the label. It could have been left alone — it is written
     * on every item that uses it — but then renaming "App" to "Website" leaves
     * a category stored as "app", and adding "App" later collides with
     * something nobody can recognise from its label. Keeping the two in step
     * costs one extra UPDATE and removes that whole class of confusion.
     */
    $newSlug = strtolower(trim(preg_replace('/[^a-z0-9]+/i', '-', $label), '-'));
    if ($newSlug === '' || mb_strlen($newSlug) > 32) {
        jsonResponse(['success' => false, 'message' => 'That name will not make a usable slug.'], 400);
    }

    if ($newSlug !== $slug) {
        $stmt = $mysqli->prepare('SELECT label FROM work_categories WHERE slug = ?');
        $stmt->bind_param('s', $newSlug);
        $stmt->execute();
        if ($clash = $stmt->get_result()->fetch_row()) {
            jsonResponse([
                'success' => false,
                'message' => "“{$clash[0]}” already uses that name.",
            ], 409);
        }
    }

    // Both or neither: an item pointing at a category that no longer exists
    // would drop out of its group on the site.
    $mysqli->begin_transaction();
    try {
        $stmt = $mysqli->prepare('UPDATE work_categories SET slug = ?, label = ? WHERE slug = ?');
        $stmt->bind_param('sss', $newSlug, $label, $slug);
        $stmt->execute();

        $moved = 0;
        if ($newSlug !== $slug) {
            $stmt = $mysqli->prepare('UPDATE work_items SET kind = ? WHERE kind = ?');
            $stmt->bind_param('ss', $newSlug, $slug);
            $stmt->execute();
            $moved = $stmt->affected_rows;
        }
        $mysqli->commit();
    } catch (Throwable $e) {
        $mysqli->rollback();
        jsonResponse(['success' => false, 'message' => 'The rename did not go through.'], 500);
    }

    jsonResponse(['success' => true, 'slug' => $newSlug, 'label' => $label, 'moved' => $moved]);
}

if ($action === 'category-delete') {
    $slug = trim((string) ($_POST['slug'] ?? ''));

    // Refused rather than cascaded: quietly moving work into another category
    // is a worse surprise than being told to empty this one first.
    $stmt = $mysqli->prepare('SELECT COUNT(*) FROM work_items WHERE kind = ?');
    $stmt->bind_param('s', $slug);
    $stmt->execute();
    $inUse = (int) $stmt->get_result()->fetch_row()[0];
    if ($inUse > 0) {
        jsonResponse([
            'success' => false,
            'message' => "{$inUse} item" . ($inUse === 1 ? ' is' : 's are') . ' still in that category.',
        ], 409);
    }

    $stmt = $mysqli->prepare('DELETE FROM work_categories WHERE slug = ?');
    $stmt->bind_param('s', $slug);
    $stmt->execute();
    jsonResponse(['success' => true]);
}

if ($action === 'delete') {
    $id = itemId($mysqli);

    $stmt = $mysqli->prepare('SELECT source, source_ref, title, banner_url FROM work_items WHERE id = ?');
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    if (!$row) {
        jsonResponse(['success' => false, 'message' => 'No item with that id.'], 404);
    }

    $stmt = $mysqli->prepare('DELETE FROM work_items WHERE id = ?');
    $stmt->bind_param('i', $id);
    $stmt->execute();

    // Its cached case study goes too, or the next adopt of the same id would
    // find a stale one waiting for it.
    if ($row['source'] === 'behance') {
        $key = 'project:' . $row['source_ref'];
        $stmt = $mysqli->prepare('DELETE FROM behance_cache WHERE cache_key = ?');
        $stmt->bind_param('s', $key);
        $stmt->execute();
    }

    /*
     * The banner file only if nothing else points at it. Two rows sharing one
     * image is normal — a republished project inherits the banner of the
     * project it replaced — and deleting the file then breaks the live one.
     */
    $removedFile = false;
    $banner = (string) ($row['banner_url'] ?? '');
    if ($banner !== '' && str_starts_with($banner, BANNER_URL_BASE . '/')) {
        $stmt = $mysqli->prepare('SELECT COUNT(*) FROM work_items WHERE banner_url = ?');
        $stmt->bind_param('s', $banner);
        $stmt->execute();
        $stillUsed = (int) $stmt->get_result()->fetch_row()[0];

        if ($stillUsed === 0) {
            // basename, and then checked to be inside the uploads directory:
            // the path came out of the database, but it is about to be unlinked.
            $file = BANNER_DIR . '/' . basename($banner);
            $real = realpath($file);
            if ($real && str_starts_with($real, realpath(BANNER_DIR) . '/') && is_file($real)) {
                $removedFile = unlink($real);
            }
        }
    }

    jsonResponse([
        'success' => true,
        'message' => "Deleted “{$row['title']}”." . ($removedFile ? ' Its banner went too.' : ''),
    ]);
}

if ($action === 'add-project') {
    /*
     * Adding a project the profile page never showed us. Behance renders twelve
     * and loads the rest from the browser with an Adobe token no cron job can
     * hold — but a project page is public, so what cannot be discovered can
     * still be named.
     */
    $given = trim((string) ($_POST['url'] ?? ''));

    // Only ever a project id reaches the shell, and only ever digits.
    $id = preg_match('#behance\.net/gallery/(\d+)#', $given, $m)
        ? $m[1]
        : (ctype_digit($given) ? $given : null);
    if ($id === null) {
        jsonResponse([
            'success' => false,
            'message' => 'That needs to be a behance.net/gallery/… link, or the number from one.',
        ], 400);
    }

    /*
     * A project already here is not a mistake — pasting its link again is how
     * you force it to be pulled now rather than waiting for its turn in the
     * refresh. Safe to repeat: the upsert names only the columns Behance owns,
     * so the banner, intro, features, category and position all survive it.
     */
    $exists = $mysqli->prepare("SELECT title FROM work_items WHERE source = 'behance' AND source_ref = ?");
    $exists->bind_param('s', $id);
    $exists->execute();
    $known = (bool) $exists->get_result()->fetch_row();

    $script = escapeshellarg(__DIR__ . '/behance-sync.php');
    $output = [];
    $status = 0;
    exec('/usr/bin/php ' . $script . ' --add ' . escapeshellarg($id) . ' 2>&1', $output, $status);

    jsonResponse([
        'success' => $status === 0,
        'message' => $status === 0
            ? ($known ? 'Refreshed. ' : 'Added. ') . trim(implode(' ', $output))
            : 'Behance would not give up that project — check the link, then try again.',
    ]);
}

jsonResponse(['success' => false, 'message' => 'Unknown action.'], 400);

<?php
/*
 * One table for everything shown as work, whatever it came from.
 *
 * Two sources feed it: Behance, which syncs on a schedule, and the client work
 * that has always lived in this database. They are the same thing on screen, so
 * they are the same thing here.
 *
 * The columns split in two, and the split is the whole point:
 *
 *   synced   title, url, cover, accent, published — overwritten on every pull,
 *            because Behance is the source of truth for them
 *   owned    banner, intro, features, visible, sort_order, kind, live_url — set
 *            in the admin and never touched by a sync, because Behance has no
 *            idea about them
 *
 * That is what makes "manage the Behance entries here" possible without being
 * able to write anything back to Behance.
 */

function ensureWorkTable(mysqli $db): void
{
    // CREATE TABLE IF NOT EXISTS does nothing to a table that already exists,
    // so a column added later has to be added explicitly.
    static $migrated = false;
    $db->query('CREATE TABLE IF NOT EXISTS work_items (
        id           INT AUTO_INCREMENT PRIMARY KEY,

        source       ENUM("behance","client") NOT NULL,
        source_ref   VARCHAR(64) NOT NULL,

        title        VARCHAR(255) NOT NULL,
        subtitle     VARCHAR(500) NULL,
        url          VARCHAR(500) NULL,
        cover_url    VARCHAR(500) NULL,
        accent       VARCHAR(9) NULL,
        published_at DATETIME NULL,
        payload      LONGTEXT NULL,

        tags         VARCHAR(500) NULL,

        banner_url   VARCHAR(500) NULL,
        intro        TEXT NULL,
        features     TEXT NULL,
        live_url     VARCHAR(255) NULL,
        kind         VARCHAR(32) NOT NULL DEFAULT "design",
        visible      TINYINT(1) NOT NULL DEFAULT 1,
        sort_order   INT NOT NULL DEFAULT 0,

        synced_at    INT NULL,
        gone_at      INT NULL,
        created_at   TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

        UNIQUE KEY source_item (source, source_ref),
        KEY ordering (visible, sort_order)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4');

    if (!$migrated) {
        $columns = [];
        $rows = $db->query('SHOW COLUMNS FROM work_items');
        while ($row = $rows->fetch_assoc()) {
            $columns[] = $row['Field'];
        }
        if (!in_array('tags', $columns, true)) {
            $db->query('ALTER TABLE work_items ADD COLUMN tags VARCHAR(500) NULL AFTER payload');
        }
        if (!in_array('intro', $columns, true)) {
            $db->query('ALTER TABLE work_items ADD COLUMN intro TEXT NULL AFTER banner_url');
        }
        if (!in_array('features', $columns, true)) {
            $db->query('ALTER TABLE work_items ADD COLUMN features TEXT NULL AFTER intro');
        }
        if (!in_array('gone_at', $columns, true)) {
            $db->query('ALTER TABLE work_items ADD COLUMN gone_at INT NULL AFTER synced_at');
        }
        $migrated = true;
    }
}

/**
 * Categories are rows, not an enum. An enum means a migration every time you
 * want a new one, and the point of the admin is that adding "motion" or
 * "print" should not need me.
 */
function ensureCategories(mysqli $db): void
{
    $db->query('CREATE TABLE IF NOT EXISTS work_categories (
        id         INT AUTO_INCREMENT PRIMARY KEY,
        slug       VARCHAR(32) NOT NULL UNIQUE,
        label      VARCHAR(64) NOT NULL,
        sort_order INT NOT NULL DEFAULT 0
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4');

    // Seeded once with what the work already uses, so nothing starts uncategorised.
    $existing = (int) $db->query('SELECT COUNT(*) FROM work_categories')->fetch_row()[0];
    if ($existing === 0) {
        $db->query('INSERT INTO work_categories (slug, label, sort_order) VALUES
            ("app", "App", 0), ("design", "Design", 1), ("client", "Client", 2)');
    }
}

/*
 * Renaming a tag without being able to rename it on Behance: the raw tag stays
 * exactly as it arrived, and this says what to call it here. Same split as a
 * banner over a cover — the sync owns the value, the admin owns the display.
 */
function ensureTagLabels(mysqli $db): void
{
    $db->query('CREATE TABLE IF NOT EXISTS work_tag_labels (
        tag    VARCHAR(64) NOT NULL PRIMARY KEY,
        label  VARCHAR(64) NOT NULL,
        hidden TINYINT(1) NOT NULL DEFAULT 0
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4');
}

/** Raw tag => how to show it. Absent means show it as it came. */
function tagLabels(mysqli $db): array
{
    ensureTagLabels($db);
    $labels = [];
    $rows = $db->query('SELECT tag, label, hidden FROM work_tag_labels');
    while ($row = $rows->fetch_assoc()) {
        $labels[$row['tag']] = ['label' => $row['label'], 'hidden' => (bool) $row['hidden']];
    }
    return $labels;
}

/**
 * Tags arrive from Behance in whatever case they were typed — mostly lower.
 * Title casing them by hand would flatten the ones that carry their own
 * capitals, so a word that already has some is left exactly as it is, and the
 * acronyms are named rather than guessed at: "ux" is not "Ux".
 *
 * A label set in the admin overrides all of this, which is where the likes of
 * "SoundCloud" get their inner capital.
 */
function titleTag(string $tag): string
{
    static $acronyms = [
        'ui' => 'UI', 'ux' => 'UX', 'ai' => 'AI', 'api' => 'API', 'ux/ui' => 'UX/UI',
        'css' => 'CSS', 'html' => 'HTML', 'js' => 'JS', 'php' => 'PHP', 'sql' => 'SQL',
        'dj' => 'DJ', 'seo' => 'SEO', '3d' => '3D', '2d' => '2D', 'nft' => 'NFT',
        'cv' => 'CV', 'wow' => 'WoW', 'fpl' => 'FPL',
    ];

    $words = preg_split('/\s+/', trim($tag)) ?: [];
    $out = array_map(static function (string $word) use ($acronyms): string {
        $lower = mb_strtolower($word);
        if (isset($acronyms[$lower])) {
            return $acronyms[$lower];
        }
        /*
         * SHOUTING is not capitalisation. Behance tags arrive however they
         * were typed, and "ILLUSTRATION" next to "Design" reads as a mistake —
         * so an all-caps word longer than an acronym is title cased, while
         * anything with its own internal capitals is left alone (SoundCloud,
         * iOS, WoW).
         */
        if (mb_strtoupper($word) === $word && mb_strlen($word) > 3) {
            return mb_strtoupper(mb_substr($lower, 0, 1)) . mb_substr($lower, 1);
        }
        if ($word !== $lower) {
            return $word;
        }
        return mb_strtoupper(mb_substr($word, 0, 1)) . mb_substr($word, 1);
    }, $words);

    return implode(' ', $out);
}

/** Tags as stored: comma separated, trimmed, empties dropped. */
function splitTags(?string $tags): array
{
    if (!$tags) {
        return [];
    }
    return array_values(array_filter(array_map('trim', explode(',', $tags))));
}

/*
 * Features are written one to a line, which is the only format that is as easy
 * to type as it is to read back. Blank lines are how you space a list out while
 * writing it, so they are dropped rather than rendered as gaps.
 */
function splitLines(?string $text): array
{
    if (!$text) {
        return [];
    }
    return array_values(array_filter(array_map('trim', preg_split('/\r\n|\r|\n/', $text))));
}

/** The category slugs currently allowed, in the order the admin put them. */
function categorySlugs(mysqli $db): array
{
    $slugs = [];
    $rows = $db->query('SELECT slug FROM work_categories ORDER BY sort_order, id');
    while ($row = $rows->fetch_row()) {
        $slugs[] = $row[0];
    }
    return $slugs;
}

/**
 * Anything from Behance that was not in the latest pull. Republishing a project
 * gives it a new id, so the old one lingers for ever otherwise — which is how
 * a renamed project ends up on the site twice, one of them a dead link.
 *
 * Hidden and flagged rather than deleted: it may carry a banner, an intro and a
 * position that took effort, and a project can come back.
 */
function markMissing(mysqli $db, array $seenRefs): int
{
    if (!$seenRefs) {
        return 0;   // An empty list means a failed fetch, not an empty profile.
    }

    $in = implode(',', array_fill(0, count($seenRefs), '?'));
    $types = str_repeat('s', count($seenRefs));

    $stmt = $db->prepare(
        "UPDATE work_items SET visible = 0, gone_at = COALESCE(gone_at, UNIX_TIMESTAMP())
         WHERE source = 'behance' AND source_ref NOT IN ({$in})"
    );
    $stmt->bind_param($types, ...$seenRefs);
    $stmt->execute();
    $marked = $stmt->affected_rows;

    /*
     * Anything that came back is shown again as well as unflagged — but only
     * where gone_at was set, so this can never override a deliberate hide. The
     * flag is what tells the two apart.
     */
    $stmt = $db->prepare(
        "UPDATE work_items SET gone_at = NULL, visible = 1
         WHERE source = 'behance' AND gone_at IS NOT NULL AND source_ref IN ({$in})"
    );
    $stmt->bind_param($types, ...$seenRefs);
    $stmt->execute();

    return $marked;
}

/**
 * Write what a pull found, leaving everything the admin owns alone. The column
 * list in the UPDATE is deliberately short: anything not named here survives a
 * sync, which is how a banner or a hidden flag outlives the next pull.
 */
function upsertSyncedItem(mysqli $db, array $item): void
{
    $stmt = $db->prepare(
        'INSERT INTO work_items
            (source, source_ref, title, subtitle, url, cover_url, accent, published_at, payload, kind, sort_order, synced_at)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
         ON DUPLICATE KEY UPDATE
            title = VALUES(title),
            subtitle = VALUES(subtitle),
            url = VALUES(url),
            cover_url = VALUES(cover_url),
            accent = VALUES(accent),
            published_at = VALUES(published_at),
            payload = VALUES(payload),
            synced_at = VALUES(synced_at)'
    );

    $stmt->bind_param(
        'ssssssssssii',
        $item['source'],
        $item['source_ref'],
        $item['title'],
        $item['subtitle'],
        $item['url'],
        $item['cover_url'],
        $item['accent'],
        $item['published_at'],
        $item['payload'],
        $item['kind'],
        $item['sort_order'],
        $item['synced_at']
    );
    $stmt->execute();
}

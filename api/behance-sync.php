<?php
/*
 * Pulls the Behance profile into the cache. Run from cron, never from the web.
 *
 *   php behance-sync.php              the list, plus any project not yet
 *                                     cached. Existing case studies are left
 *                                     alone — see REFRESH_PER_RUN below.
 *   php behance-sync.php --all        list and every project, refreshed
 *   php behance-sync.php --project ID one project, refreshed — for when a
 *   php behance-sync.php --add URL    single case study comes back wrong, or to
 *                                     adopt one the profile never showed us.
 *                                     The same thing; two names for two moods.
 *
 * Why this exists as a separate, scheduled job rather than a read-through in
 * the request path:
 *
 *  - Behance refuses anything that is not a browser. It answers 429 to plain
 *    curl and to PHP's curl no matter what headers they send, while a real
 *    Chrome on the same network is served normally — so the block is on the
 *    TLS handshake, not on how often you ask. Hence curl-impersonate below,
 *    which performs a browser's handshake.
 *  - A portfolio changes when you publish something, not when someone visits.
 *
 * Firefox rather than Chrome: curl-impersonate ships both, and Adobe currently
 * refuses its Chrome fingerprints while serving the Firefox one.
 *
 * The web endpoint only ever reads what this leaves behind.
 */

if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit("This is a cron job, not an endpoint.\n");
}

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/behance-parse.php';
require_once __DIR__ . '/work-schema.php';

const SYNC_USER = 'jonnypyper';
const SYNC_UA = 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) '
    . 'AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36';

/*
 * How many already-cached case studies to pull again each run.
 *
 * Zero on purpose. The scheduled job still finds new projects and still keeps
 * the list — titles, covers, dates — in step with Behance, but it no longer
 * rewrites the case study of a project that is already here. Artwork changing
 * under you at three in the morning is a surprise; nothing about a portfolio
 * needs that. Refreshing one is a decision now: paste its link in the admin,
 * or `--project <id>` from the shell.
 *
 * Set it back to 2 to have the site chase Behance on its own again.
 */
const REFRESH_PER_RUN = 0;

/*
 * Adopting a project by URL, because the profile page is not the whole profile.
 *
 * Behance renders twelve projects and loads the rest over GraphQL — and that
 * endpoint answers 403 to anyone without an Adobe Bearer token, which a cron
 * job has no way to hold. A project page, though, is served to anybody. So what
 * cannot be discovered can still be fetched, once someone says which.
 */
$adopt = null;
/*
 * --project is the same fetch: name one and it is pulled again. It used to walk
 * the profile list looking for a match, which quietly did nothing at all for a
 * project past page one — the very projects most likely to need naming.
 */
$addIndex = array_search('--add', $argv, true);
if ($addIndex === false) {
    $addIndex = array_search('--project', $argv, true);
}
if ($addIndex !== false && isset($argv[$addIndex + 1])) {
    // A gallery URL or a bare id — whichever is to hand.
    $adopt = preg_match('#/gallery/(\d+)#', $argv[$addIndex + 1], $m)
        ? $m[1]
        : (ctype_digit(trim($argv[$addIndex + 1])) ? trim($argv[$addIndex + 1]) : null);
    if ($adopt === null) {
        fwrite(STDERR, "behance-sync: needs a behance.net/gallery/... URL or a project id\n");
        exit(1);
    }
}

$refreshAll = in_array('--all', $argv, true);



/*
 * --from <dir> parses HTML already on disk instead of fetching. It exists
 * because the rate limit can lock this box out for a long stretch, and because
 * a saved page is the only way to work on the parser without spending requests
 * against the very limit that is the problem. Expects profile.html, and
 * project-<id>.html for any project.
 */
$fromDir = null;
$fromIndex = array_search('--from', $argv, true);
if ($fromIndex !== false && isset($argv[$fromIndex + 1])) {
    $fromDir = rtrim($argv[$fromIndex + 1], '/');
    if (!is_dir($fromDir)) {
        fwrite(STDERR, "behance-sync: no such directory {$fromDir}\n");
        exit(1);
    }
}

ensureWorkTable($mysqli);

$mysqli->query('CREATE TABLE IF NOT EXISTS behance_cache (
    cache_key  VARCHAR(64) NOT NULL PRIMARY KEY,
    body       LONGTEXT NOT NULL,
    fetched_at INT NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4');

/**
 * Tags live on a project's own page, not in the profile listing, so they arrive
 * with the case study rather than with the list.
 */
function tagItem(mysqli $db, string $id, array $tags): void
{
    $joined = implode(', ', array_slice($tags, 0, 12));
    $stmt = $db->prepare('UPDATE work_items SET tags = ? WHERE source = "behance" AND source_ref = ?');
    $stmt->bind_param('ss', $joined, $id);
    $stmt->execute();
}

/*
 * curl-impersonate, which speaks TLS the way a browser does. Plain curl and
 * PHP's curl are both refused — see the note at the top. Falls back to system
 * curl if the binary is missing, so a fresh box degrades to "cannot fetch"
 * rather than to a fatal error.
 */
const IMPERSONATE = '/opt/curl-impersonate/curl_ff117';

function fetchPage(string $url): ?string
{
    $binary = is_executable(IMPERSONATE) ? IMPERSONATE : 'curl';

    /*
     * Behance sits behind Varnish, which happily served a three minute old copy
     * of the profile — so a sync run straight after publishing something would
     * miss it and, worse, conclude the new project had disappeared. The site
     * itself sends cache-control: no-store, so this only asks for what Behance
     * says it intends to serve.
     */
    $fresh = $url . (str_contains($url, '?') ? '&' : '?') . '_=' . time();

    $command = sprintf(
        '%s -s -L --max-time 40 --compressed %s %s',
        escapeshellarg($binary),
        $binary === 'curl' ? '-A ' . escapeshellarg(SYNC_UA) : '',
        escapeshellarg($fresh)
    );

    $body = shell_exec($command);
    // A refusal is a 3.7KB error page, so length is the cheapest way to tell a
    // real answer from a polite no.
    return (is_string($body) && strlen($body) > 100000) ? $body : null;
}

function store(mysqli $db, string $key, $data): void
{
    $body = json_encode(['fetched' => time(), 'data' => $data]);
    $now = time();
    $stmt = $db->prepare(
        'INSERT INTO behance_cache (cache_key, body, fetched_at) VALUES (?, ?, ?)
         ON DUPLICATE KEY UPDATE body = VALUES(body), fetched_at = VALUES(fetched_at)'
    );
    $stmt->bind_param('ssi', $key, $body, $now);
    $stmt->execute();
}

/*
 * The projects whose cached case study is the oldest, so an edited project
 * comes down on its own rather than waiting for someone to remember. Ordered
 * by when each was last fetched, never cached first.
 *
 * A fixed few per run rather than "anything older than a day": the cost of a
 * run then never depends on how many projects there are, which is the property
 * that matters against a host that blocks us for asking too often. Thirteen
 * projects at two a run, four runs a day, is a full cycle every day and a half.
 */
function stalest(mysqli $db, array $already, int $limit): array
{
    $skip = array_map('strval', array_column($already, 'id'));

    /*
     * Candidates come from work_items rather than from the profile list, which
     * matters: a project adopted by URL is never in that list, so ranking over
     * the list would refresh everything except the projects that most need it.
     */
    $rows = $db->query(
        "SELECT w.source_ref, w.title, w.payload, COALESCE(c.fetched_at, 0) AS at
           FROM work_items w
           LEFT JOIN behance_cache c ON c.cache_key = CONCAT('project:', w.source_ref)
          WHERE w.source = 'behance' AND w.gone_at IS NULL
          ORDER BY at ASC"
    );

    $due = [];
    while ($row = $rows->fetch_assoc()) {
        if (in_array($row['source_ref'], $skip, true)) {
            continue;   // already being fetched this run
        }
        $payload = $row['payload'] ? json_decode($row['payload'], true) : [];
        $due[] = [
            'id' => $row['source_ref'],
            'name' => $row['title'],
            // Behance redirects a wrong slug to the right one, so a missing one
            // is not a reason to skip the project.
            'slug' => $payload['slug'] ?? 'x',
        ];
        if (count($due) >= $limit) {
            break;
        }
    }

    return $due;
}

/** The stored body for a cache key, or null. */
function cacheRow(mysqli $db, string $key): ?string
{
    $stmt = $db->prepare('SELECT body FROM behance_cache WHERE cache_key = ?');
    $stmt->bind_param('s', $key);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_row();
    return $row ? $row[0] : null;
}

function cached(mysqli $db, string $key): bool
{
    $stmt = $db->prepare('SELECT 1 FROM behance_cache WHERE cache_key = ?');
    $stmt->bind_param('s', $key);
    $stmt->execute();
    return (bool) $stmt->get_result()->fetch_row();
}

/*
 * Adopting runs on its own: it needs one project page, not the profile, and the
 * profile is exactly the thing that could not see this project in the first
 * place.
 */
if ($adopt !== null) {
    ensureWorkTable($mysqli);
    $page = fetchPage('https://www.behance.net/gallery/' . $adopt . '/x');
    $detail = $page === null ? null : parseProjectDetail($page);
    if ($detail === null) {
        fwrite(STDERR, "behance-sync: could not read project {$adopt}\n");
        exit(1);
    }

    upsertSyncedItem($mysqli, [
        'source' => 'behance',
        'source_ref' => (string) $adopt,
        'title' => $detail['name'],
        'subtitle' => null,
        'url' => $detail['url'] ?? ('https://www.behance.net/gallery/' . $adopt . '/' . ($detail['slug'] ?? '')),
        'cover_url' => $detail['coverLarge'] ?? $detail['cover'],
        'accent' => $detail['accent'],
        'published_at' => $detail['published'] ? date('Y-m-d H:i:s', $detail['published']) : null,
        'payload' => json_encode($detail),
        'kind' => 'design',
        // Last, rather than first: it is being added long after the rest, and
        // where it belongs is a decision for the admin, not for this.
        'sort_order' => 999,
        'synced_at' => time(),
    ]);
    // The same rule as a refresh: an empty answer never replaces a full one.
    $keep = false;
    if (!$detail['modules'] && cached($mysqli, 'project:' . $adopt)) {
        $before = json_decode((string) cacheRow($mysqli, 'project:' . $adopt), true);
        $keep = ($before['data']['modules'] ?? []) !== [];
    }
    if ($keep) {
        fwrite(STDERR, "  ~ {$detail['name']}: came back empty, keeping the cached copy\n");
    } else {
        store($mysqli, 'project:' . $adopt, $detail);
        tagItem($mysqli, (string) $adopt, $detail['tags'] ?? []);
    }

    echo "pulled: {$detail['name']} (" . count($detail['modules']) . " modules)\n";
    if (!empty($detail['mature']) && !$detail['modules']) {
        fwrite(STDERR, "  ! flagged as mature on Behance — its case study is withheld\n");
    }
    exit(0);
}

$html = $fromDir
    ? (@file_get_contents($fromDir . '/profile.html') ?: null)
    : fetchPage('https://www.behance.net/' . SYNC_USER);
if ($html === null) {
    fwrite(STDERR, "behance-sync: could not fetch the profile\n");
    exit(1);
}

$projects = parseProjectList($html);
if ($projects === null) {
    // Deliberately not cleared: whatever is stored stays serving. A parse
    // failure means Behance changed shape, which is a problem to fix, not a
    // reason to empty the site.
    fwrite(STDERR, "behance-sync: profile fetched but the project list could not be parsed\n");
    exit(1);
}

store($mysqli, 'list:' . SYNC_USER, $projects);

/*
 * Every pull lands as rows as well as a blob. The blob is what the site reads
 * for speed; the rows are what the admin manages — show or hide, order, a
 * banner of your own. Anything the admin owns survives this, because the
 * upsert does not name those columns.
 */
foreach ($projects as $position => $project) {
    upsertSyncedItem($mysqli, [
        'source' => 'behance',
        'source_ref' => (string) $project['id'],
        'title' => $project['name'],
        'subtitle' => null,
        'url' => $project['url'],
        'cover_url' => $project['coverLarge'] ?? $project['cover'],
        'accent' => $project['accent'],
        'published_at' => $project['published'] ? date('Y-m-d H:i:s', $project['published']) : null,
        'payload' => json_encode($project),
        // Behance cannot say whether something is an app; the admin can, and
        // its answer is not overwritten after this first insert.
        'kind' => 'design',
        'sort_order' => $position,
        'synced_at' => time(),
    ]);
}

/*
 * Only when the list is the whole list. Behance renders twelve projects and
 * fetches the rest over GraphQL from the browser, so a scrape sees page one —
 * and hiding everything absent from it hides the thirteenth project, then the
 * twelfth, one more each time something new is published. Two were lost that
 * way before anyone noticed, which is what this guard is for: a list that is
 * known to be partial cannot be evidence that anything is gone.
 */
$gone = 0;
if (profileHasMore($html)) {
    fwrite(STDERR, "  ~ profile is paginated (" . count($projects) . " of more) — "
        . "nothing hidden, since absent may only mean page two\n");
} else {
    $gone = markMissing($mysqli, array_map(static fn($p) => (string) $p['id'], $projects));
}

echo 'list: ' . count($projects) . " projects (cached and written to work_items)\n";
if ($gone) {
    echo "  no longer on Behance, hidden: {$gone}\n";
}

/*
 * Tags for anything whose case study is already cached. Free — it reads what
 * is on disk rather than asking Behance — and it means a rate limit only delays
 * new projects, not the ones already pulled.
 */
$backfilled = 0;
foreach ($projects as $project) {
    $row = cacheRow($mysqli, 'project:' . $project['id']);
    $tags = $row ? (json_decode($row, true)['data']['tags'] ?? null) : null;
    if (is_array($tags) && $tags) {
        tagItem($mysqli, (string) $project['id'], $tags);
        $backfilled++;
    }
}
echo "tags from cache: {$backfilled}\n";

/*
 * What to fetch: anything missing, plus a couple of the stalest. Deciding it up
 * front rather than inside the loop is what makes the refresh possible at all —
 * "the oldest two" is a question about the whole set, not about one project.
 */
$due = [];
foreach ($projects as $project) {
    if ($refreshAll || !cached($mysqli, 'project:' . $project['id'])) {
        $due[] = $project;
    }
}

$refreshing = [];
if (!$refreshAll && REFRESH_PER_RUN > 0) {
    $refreshing = stalest($mysqli, $due, REFRESH_PER_RUN);
    $due = array_merge($due, $refreshing);
}
$refreshingIds = array_column($refreshing, 'id');

// Each project's case study. Spaced out, because the whole point is to stay
// well under whatever rate limit tripped in the first place.
$fetched = 0;
foreach ($due as $project) {
    $key = 'project:' . $project['id'];
    if ($fromDir) {
        $page = @file_get_contents($fromDir . '/project-' . $project['id'] . '.html') ?: null;
        if ($page === null) {
            continue; // Not saved locally; a later online run will pick it up.
        }
    } else {
        sleep(3);
        $page = fetchPage('https://www.behance.net/gallery/' . $project['id'] . '/' . $project['slug']);
    }
    $detail = $page === null ? null : parseProjectDetail($page);
    if ($detail === null) {
        fwrite(STDERR, "  ! {$project['name']}: not fetched\n");
        continue;
    }

    /*
     * Behance withholds the modules of a project flagged as mature from anyone
     * who is not signed in, and returns the rest of it as normal. Parsed, that
     * is a project with a title, tags and no case study — indistinguishable
     * from a project nobody has put anything in. Saying so here is the only
     * way anyone finds out, because the flag is on Behance and can only be
     * cleared there.
     */
    if (!empty($detail['mature']) && !$detail['modules']) {
        fwrite(STDERR, "  ! {$project['name']}: flagged as mature on Behance — "
            . "its case study is withheld from logged-out visitors. Untick adult "
            . "content in the project's settings, then run this again.\n");
    }
    /*
     * Refreshing must never make things worse. A page that parses but carries
     * no modules is what Behance serves for a mature project, and would be what
     * it served during an outage or a shape change — so it is not allowed to
     * replace a case study that already has content. The old copy stays, and
     * the next run tries again.
     */
    if (!$detail['modules'] && cached($mysqli, $key)) {
        $before = json_decode((string) cacheRow($mysqli, $key), true);
        if (($before['data']['modules'] ?? []) !== []) {
            fwrite(STDERR, "  ~ {$project['name']}: came back empty, keeping the cached copy\n");
            continue;
        }
    }

    store($mysqli, $key, $detail);
    tagItem($mysqli, (string) $project['id'], $detail['tags'] ?? []);
    $fetched++;
    $why = in_array($project['id'], $refreshingIds, false) ? 'refreshed' : 'new';
    echo "  + {$project['name']} (" . count($detail['modules']) . " modules, {$why})\n";
}

echo "projects fetched: {$fetched}\n";

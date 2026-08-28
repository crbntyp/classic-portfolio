<?php
/*
 * Parsing Behance's profile and project pages.
 *
 * There is no API to call: Adobe retired the public developer programme, so
 * api.behance.net answers "a client or user is required" and there is nowhere
 * left to register a key. What both pages do is server render everything into
 * one <script type="application/json"> block, which is what this reads.
 *
 * That makes it a scrape. It will break the day Behance changes shape — so
 * every function here returns null rather than throwing or half-parsing, and
 * the callers keep serving the last good copy when it does.
 */

function embeddedState(string $html): ?array
{
    $needle = '<script type="application/json"';
    $blocks = [];
    $at = 0;

    while (($start = strpos($html, $needle, $at)) !== false) {
        $open = strpos($html, '>', $start);
        if ($open === false) {
            break;
        }
        $end = strpos($html, '</script>', $open);
        if ($end === false) {
            break;
        }
        $blocks[] = substr($html, $open + 1, $end - $open - 1);
        $at = $end + 9;
    }

    usort($blocks, static fn($a, $b) => strlen($b) - strlen($a));
    foreach ($blocks as $block) {
        $decoded = json_decode($block, true);
        if (is_array($decoded)) {
            return $decoded;
        }
    }
    return null;
}

/**
 * The full-resolution cover, for a project page's hero. It is the one entry
 * Behance publishes without a width, so the width-based picker below skips it;
 * it 302s to a rendition host, which a browser follows.
 */
function coverOriginal(array $covers): ?string
{
    foreach (['/original_webp/', '/original/'] as $marker) {
        foreach ($covers as $cover) {
            if (isset($cover['url']) && str_contains($cover['url'], $marker)) {
                return $cover['url'];
            }
        }
    }
    return null;
}

function coverAt(array $covers, int $want): ?string
{
    $best = null;
    $bestGap = PHP_INT_MAX;
    foreach ($covers as $cover) {
        $width = (int) ($cover['width'] ?? 0);
        $url = $cover['url'] ?? null;
        if (!$url || !$width) {
            continue;
        }
        $gap = abs($width - $want);
        if ($gap < $bestGap) {
            $best = $url;
            $bestGap = $gap;
        }
    }
    return $best ?? ($covers[0]['url'] ?? null);
}

/**
 * The best rendition of a module image at a wanted width, with its dimensions.
 *
 * Behance lists every size it made, JPG and WEBP alike, so this picks the
 * nearest width and prefers WEBP where both exist — the same picture, a smaller
 * file. Unlike coverAt() it returns the whole entry, because a case study image
 * needs its height as well as its address.
 */
function bestImage(array $sizes, int $want): ?array
{
    $best = null;
    $bestGap = PHP_INT_MAX;
    foreach ($sizes as $size) {
        $width = (int) ($size['width'] ?? 0);
        if (!$width || empty($size['url'])) {
            continue;
        }
        $gap = abs($width - $want);
        $better = $gap < $bestGap
            || ($gap === $bestGap && ($size['type'] ?? '') === 'WEBP');
        if ($better) {
            $best = $size;
            $bestGap = $gap;
        }
    }
    return $best;
}

/*
 * How wide a row of a media collection is, in the units Behance records on each
 * component. Inferred rather than documented: the forty squares of the WoW
 * project carry flexWidth 260 apiece and render as eight rows of five, which is
 * 10400 over exactly 1300 a row. Every other collection sums to less than one
 * row and renders as one, which agrees.
 *
 * Any value from 1300 to 1559 reproduces all of them; 1300 is the one that
 * comes out exact. If a future collection wraps differently from Behance, this
 * is the number to question first.
 */
const COLLECTION_ROW = 1300;

/**
 * A collection's components, packed into rows the way Behance packs them, each
 * row a justified line of pictures at a common height.
 *
 * The rendition is chosen per row rather than per project: five across a case
 * study is a cell about 200px wide, and sending a 1400px picture for it costs
 * five times the bytes for nothing. Doubled first, because these are read on
 * retina screens.
 */
function packCollection(array $components): array
{
    $rows = [];
    $row = [];
    $width = 0.0;

    foreach ($components as $component) {
        $sizes = $component['imageSizes']['allAvailable'] ?? [];
        if (!$sizes) {
            continue;
        }
        $flex = (float) ($component['flexWidth'] ?? 0);
        if ($flex <= 0) {
            $flex = ($component['width'] ?? 1) / max(1, $component['height'] ?? 1);
        }

        // Would it overflow the line? Then the line is finished. A single
        // component wider than a whole row still gets its own line rather than
        // an empty one before it.
        if ($row && $width + $flex > COLLECTION_ROW) {
            $rows[] = $row;
            $row = [];
            $width = 0.0;
        }

        $row[] = ['sizes' => $sizes, 'flex' => round($flex, 3)];
        $width += $flex;
    }
    if ($row) {
        $rows[] = $row;
    }

    $packed = [];
    foreach ($rows as $row) {
        $want = max(400, (int) round(2 * 1400 / max(1, count($row))));
        $items = [];
        foreach ($row as $entry) {
            $best = bestImage($entry['sizes'], $want);
            if (!$best) {
                continue;
            }
            $items[] = [
                'url' => $best['url'],
                'width' => $best['width'] ?? null,
                'height' => $best['height'] ?? null,
                'flex' => $entry['flex'],
            ];
        }
        if ($items) {
            $packed[] = $items;
        }
    }
    return $packed;
}

/**
 * Text modules arrive as Behance's own HTML, which the site then injects into
 * its own page. That is third-party markup in a first-party document, so it is
 * cut back to an allowlist: the tags that carry meaning, and no attributes
 * except a link's href. It also strips the inline styling Behance ships, which
 * would otherwise drag its typography across into this design.
 */
function cleanHtml(string $html): string
{
    $allowed = '<p><br><strong><b><em><i><u><a><ul><ol><li><h2><h3><h4><blockquote><code>';

    /*
     * strip_tags removes a tag without leaving anything in its place, so a
     * paragraph Behance wrapped in a <div> runs straight into the next one:
     * "…Seven tracks</div><div>Remember there is no music…" becomes
     * "tracksRemember". Block tags that are about to be dropped become a space
     * first. Only block ones — doing this to a <span> would break a word in
     * half rather than join two.
     */
    $html = preg_replace('#</?(div|section|article|header|footer|table|tbody|tr|td|th)\b[^>]*>#i', ' ', $html);
    $html = strip_tags($html, $allowed);

    // Drop every attribute, then put back href on links that point somewhere sane.
    $html = preg_replace_callback('#<a\b[^>]*>#i', static function ($match) {
        if (preg_match('#href\s*=\s*["\']([^"\']+)["\']#i', $match[0], $href)
            && preg_match('#^(https?:)?//#i', $href[1])) {
            return '<a href="' . htmlspecialchars($href[1], ENT_QUOTES) . '" target="_blank" rel="noopener">';
        }
        return '<a>';
    }, $html);
    $html = preg_replace('#<(?!a\b)([a-z0-9]+)\b[^>]*>#i', '<$1>', $html);

    return trim($html);
}

/** Behance reports stats as {"all": n}; older shapes are plain ints. */
function statCount($stat): int
{
    if (is_array($stat)) {
        return (int) ($stat['all'] ?? $stat['total'] ?? 0);
    }
    return (int) ($stat ?? 0);
}

/** The profile page's Work section. Null means the shape changed. */
function parseProjectList(string $html): ?array
{
    $state = embeddedState($html);
    $found = $state['profile']['activeSection']['work']['profileProjects'] ?? null;
    if (!is_array($found)) {
        return null;
    }

    $projects = [];
    foreach ($found as $project) {
        if (($project['privacyLevel'] ?? '') !== 'PUBLIC' || !empty($project['isPrivate'])) {
            continue;
        }
        $covers = $project['covers']['allAvailable'] ?? [];
        $colors = $project['colors'] ?? null;
        $projects[] = [
            'id' => $project['id'] ?? null,
            'name' => $project['name'] ?? 'Untitled',
            'slug' => $project['slug'] ?? null,
            'url' => $project['url'] ?? null,
            'published' => isset($project['publishedOn']) ? (int) $project['publishedOn'] : null,
            'cover' => coverAt($covers, 808),
            'coverSmall' => coverAt($covers, 404),
            // Full resolution, for a project page's hero.
            'coverLarge' => coverOriginal($covers) ?? coverAt($covers, 808),
            // The cover's dominant colour: enough to tint a card before its
            // image has loaded.
            'accent' => $colors
                ? sprintf('#%02x%02x%02x', $colors['r'] ?? 0, $colors['g'] ?? 0, $colors['b'] ?? 0)
                : null,
            'views' => statCount($project['stats']['views'] ?? null),
            'appreciations' => statCount($project['stats']['appreciations'] ?? null),
        ];
    }
    return $projects;
}

/**
 * Whether the profile page held back projects it did not render.
 *
 * Behance paginates the work section at twelve and loads the rest over GraphQL
 * from the browser, so a scrape of the page is page one and nothing else. That
 * makes "not in the list" mean one of two very different things — deleted, or
 * simply thirteenth — and only this can tell them apart.
 */
function profileHasMore(string $html): bool
{
    $state = embeddedState($html);
    return (bool) ($state['profile']['activeSection']['work']['hasMore'] ?? false);
}

/** One project page: the case study, its tags and the tools it was made with. */
function parseProjectDetail(string $html): ?array
{
    $state = embeddedState($html);
    $project = $state['project']['project'] ?? null;
    if (!is_array($project)) {
        return null;
    }

    $modules = [];
    foreach (($project['allModules'] ?? $project['modules'] ?? []) as $module) {
        $type = $module['__typename'] ?? '';
        if ($type === 'TextModule' && !empty($module['text'])) {
            $modules[] = ['type' => 'text', 'html' => cleanHtml($module['text'])];
            continue;
        }
        if ($type === 'ImageModule') {
            $sizes = $module['imageSizes'] ?? [];
            $src = $module['src'] ?? null;
            if (!$src) {
                continue;
            }

            /*
             * Behance lists the bigger renditions with their dimensions but no
             * URL — only the 600px "disp" one carries a link. The paths differ
             * by a single segment, so the larger one is derived from the small
             * one. A 600px screenshot stretched across a case study looks like
             * a mistake; 1400 is what Behance's own project page uses.
             */
            $width = $sizes['size_disp']['width'] ?? $module['width'] ?? null;
            $height = $sizes['size_disp']['height'] ?? $module['height'] ?? null;
            foreach (['size_1400' => '1400', 'size_max_1200' => 'max_1200'] as $key => $segment) {
                if (!isset($sizes[$key])) {
                    continue;
                }
                $bigger = str_replace('/project_modules/disp/', '/project_modules/' . $segment . '/', $src);
                if ($bigger !== $src) {
                    $src = $bigger;
                    $width = $sizes[$key]['width'] ?? $width;
                    $height = $sizes[$key]['height'] ?? $height;
                }
                break;
            }

            $modules[] = [
                'type' => 'image',
                'url' => $src,
                'caption' => $module['caption'] ?? null,
                'width' => $width,
                'height' => $height,
            ];
            continue;
        }
        /*
         * A set of images posted as one block — covers, flyers, a series. It
         * was being dropped on the floor, which is why a project could show a
         * title, a lede and nothing else while the same project on Behance was
         * full of artwork.
         *
         * Kept as a group rather than flattened into separate images, because
         * the grouping is the design: Behance lays a collection out as one
         * justified row, every item the same height, widths in proportion. Each
         * component's flexWidth is that proportion — it tracks the aspect ratio,
         * so a wider piece takes more of the row.
         */
        if ($type === 'MediaCollectionModule') {
            $rows = packCollection($module['components'] ?? []);
            if ($rows) {
                $modules[] = [
                    'type' => 'collection',
                    'rows' => $rows,
                    // One caption for the whole block, which is how it is written.
                    'caption' => $module['caption'] ?? null,
                ];
            }
            continue;
        }

        if ($type === 'VideoModule') {
            $modules[] = [
                'type' => 'video',
                'url' => $module['videoData']['renditions'][0]['url'] ?? $module['src'] ?? null,
                'poster' => $module['videoData']['posterUrl'] ?? null,
            ];
        }
    }

    return [
        'id' => $project['id'] ?? null,
        'name' => $project['name'] ?? 'Untitled',
        'slug' => $project['slug'] ?? null,
        'url' => $project['url'] ?? null,
        'description' => $project['description'] ?? '',
        'published' => isset($project['publishedOn']) ? (int) $project['publishedOn'] : null,
        'tags' => array_values(array_filter(array_map(
            static fn($tag) => $tag['title'] ?? null,
            $project['tags'] ?? []
        ))),
        'tools' => array_values(array_filter(array_map(
            static fn($tool) => $tool['title'] ?? null,
            $project['tools'] ?? []
        ))),
        /*
         * A project page carries its own covers and colour, in the same shape
         * the profile list uses. Parsing them here is what lets a project be
         * adopted from its page alone — the profile could not see it, so
         * nothing else is going to supply these.
         */
        'cover' => coverAt($project['covers'] ?? [], 808),
        'coverSmall' => coverAt($project['covers'] ?? [], 404),
        'coverLarge' => coverOriginal($project['covers'] ?? []) ?? coverAt($project['covers'] ?? [], 808),
        'accent' => isset($project['colors'])
            ? sprintf('#%02x%02x%02x', $project['colors']['r'] ?? 0, $project['colors']['g'] ?? 0, $project['colors']['b'] ?? 0)
            : null,
        'modules' => $modules,
        /*
         * Set on the project at Behance's end. It matters because a mature
         * project is served to a logged-out visitor complete except for its
         * modules — so without this an empty case study looks like an empty
         * project rather than a withheld one.
         */
        'mature' => (bool) ($project['hasMatureContent'] ?? false),
        'views' => statCount($project['stats']['views'] ?? null),
        'appreciations' => statCount($project['stats']['appreciations'] ?? null),
    ];
}

<?php
/*
 * What is in the audio folder.
 *
 *   GET audio-list.php   the mp3s available to the site's now-playing control
 *
 * Read-only and behind the session, because it is only ever used to populate
 * a picker in the admin. Files get there by hand (scp), not through the
 * browser: the image uploader validates with getimagesize(), which says
 * nothing about audio, so accepting music would mean a second upload path
 * with its own MIME and magic-byte checks. Not worth writing until picking
 * from what is already there proves annoying.
 */

require_once __DIR__ . '/config.php';

requireAuth();

// api/ sits at portfolio/api, so the site root is two levels up.
const AUDIO_DIR = __DIR__ . '/../../audio';

/*
 * mp3 and the MP4 family only. Both decode in every browser that matters,
 * which opus and ogg do not — Safari would silently fail, and a control that
 * works everywhere except one browser is worse than one that never appeared.
 *
 * An .mp4 carrying video plays fine through <audio> (the video track is
 * simply ignored), but it ships the picture for audio-only playback. There is
 * ffmpeg on this box if a video ever needs stripping down first.
 */
const AUDIO_EXTS = ['mp3', 'm4a', 'mp4'];

/*
 * Tags read with ffprobe and then cleaned, because these files come off
 * YouTube and their tags say so: the artist arrives as "Mogwai - Topic" and
 * the title as "Stephan Bodzin - Lila (Official)". Good enough to seed the
 * fields in the admin, never good enough to publish unread — which is why
 * what the site shows is the saved library, not this.
 */
function tidyArtist(string $raw): string
{
    $a = preg_replace('/\s*-\s*Topic\s*$/i', '', trim($raw));
    // YouTube's auto-channels shout. Title-case anything that is all caps.
    if ($a !== '' && $a === mb_strtoupper($a, 'UTF-8')) {
        $a = mb_convert_case(mb_strtolower($a, 'UTF-8'), MB_CASE_TITLE, 'UTF-8');
    }
    return $a;
}

function tidyTitle(string $raw, string $artist): string
{
    $t = trim($raw);
    // "Artist - Title (Official)" → "Title"
    if ($artist !== '' && stripos($t, $artist) === 0) {
        $t = trim(preg_replace('/^' . preg_quote($artist, '/') . '\s*[-–—]\s*/i', '', $t));
    }
    $t = preg_replace('/\s*\((?:official(?:\s+(?:video|audio|music\s+video))?)\)\s*$/i', '', $t);
    return trim($t);
}

function readTags(string $path): array
{
    $cmd = 'ffprobe -v quiet -show_entries format_tags=title,artist -of json '
         . escapeshellarg($path) . ' 2>/dev/null';
    $out = @shell_exec($cmd);
    $tags = json_decode((string) $out, true)['format']['tags'] ?? [];

    // ffprobe casing varies by container.
    $get = static function (array $t, string $key): string {
        foreach ($t as $k => $v) {
            if (strcasecmp($k, $key) === 0) return (string) $v;
        }
        return '';
    };

    $artist = tidyArtist($get($tags, 'artist'));
    return [
        'artist' => $artist,
        'title' => tidyTitle($get($tags, 'title'), $artist),
    ];
}

$files = [];

foreach (glob(AUDIO_DIR . '/*.{' . implode(',', AUDIO_EXTS) . '}', GLOB_BRACE) ?: [] as $path) {
    if (!is_file($path)) continue;
    $tags = readTags($path);
    $files[] = [
        'name' => basename($path),
        'size' => filesize($path) ?: 0,
        'title' => $tags['title'],
        'artist' => $tags['artist'],
    ];
}

// Newest first is the wrong order here — you pick by name, not by when it
// landed, and the list is short.
usort($files, static fn(array $a, array $b): int => strcasecmp($a['name'], $b['name']));

jsonResponse(['success' => true, 'files' => $files]);

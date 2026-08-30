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

$files = [];

foreach (glob(AUDIO_DIR . '/*.{' . implode(',', AUDIO_EXTS) . '}', GLOB_BRACE) ?: [] as $path) {
    if (!is_file($path)) continue;
    $files[] = [
        'name' => basename($path),
        'size' => filesize($path) ?: 0,
    ];
}

// Newest first is the wrong order here — you pick by name, not by when it
// landed, and the list is short.
usort($files, static fn(array $a, array $b): int => strcasecmp($a['name'], $b['name']));

jsonResponse(['success' => true, 'files' => $files]);

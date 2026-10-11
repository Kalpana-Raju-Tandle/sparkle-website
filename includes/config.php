<?php
declare(strict_types=1);

/*
 * Sparkle Constructions — site configuration.
 * Everything a client would edit day-to-day (phone, address, prices, profiles)
 * lives in the admin panel → Settings, not here.
 */

date_default_timezone_set('Asia/Kolkata');

define('ROOT', dirname(__DIR__));
define('DB_FILE', ROOT . '/data/sparkle.sqlite');
define('UPLOAD_DIR', ROOT . '/uploads/gallery/');
define('THUMB_DIR', ROOT . '/uploads/thumbs/');
define('MAX_UPLOAD_BYTES', 15 * 1024 * 1024);
define('VIDEO_DIR', ROOT . '/uploads/videos/');
define('MAX_VIDEO_BYTES', 300 * 1024 * 1024);      // videos arrive in small chunks, so this is not tied to PHP's upload limit
define('CHUNK_DIR', ROOT . '/data/tmp/');           // half-uploaded videos (data/ is not web-accessible)
define('SITE_NAME', 'Sparkle Constructions');

// Web path of the site root, so the site also works from a sub-folder.
(function () {
    $doc  = realpath($_SERVER['DOCUMENT_ROOT'] ?? '') ?: '';
    $root = realpath(ROOT) ?: ROOT;
    $base = '';
    if ($doc !== '' && str_starts_with($root, $doc)) {
        $base = str_replace('\\', '/', substr($root, strlen($doc)));
    }
    define('BASE_URL', rtrim($base, '/'));
})();

// Construction stages, in build order (used by the gallery "journey" slider).
const STAGES = [
    1 => 'Site Marking',
    2 => 'Excavation',
    3 => 'Footing & Steel',
    4 => 'Plinth & Columns',
    5 => 'Walls & Slabs',
    6 => 'Plastering',
    7 => 'Finishing',
    8 => 'Handover',
];

const CATEGORIES = [
    'journey'   => 'Construction Journey',
    'completed' => 'Completed Homes',
    'designs'   => '3D Designs',
    'team'      => 'Our Team',
];

// Package details (rates themselves are editable in Settings).
const PACKAGES = [
    'essential' => [
        'name' => 'Essential',
        'tag'  => 'Smart, honest value',
        'items' => [
            'Complete structural construction',
            'Quality-tested cement & steel',
            'Standard electrical & plumbing',
            'Plastering & basic finishes',
            'Site engineer supervision',
        ],
    ],
    'classic' => [
        'name' => 'Classic',
        'tag'  => 'Our most chosen',
        'items' => [
            'Everything in Essential',
            '3D elevation & floor plan design',
            'Upgraded tiles & fittings',
            'Wooden doors & window frames',
            'Weekly photo progress updates',
        ],
    ],
    'signature' => [
        'name' => 'Signature',
        'tag'  => 'Fully tailored luxury',
        'items' => [
            'Everything in Classic',
            'Premium cladding & elevation work',
            'Designer lighting & false ceilings',
            'Modular-ready kitchen & wardrobes',
            'Dedicated project manager',
        ],
    ],
];

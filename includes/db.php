<?php
declare(strict_types=1);

require_once __DIR__ . '/config.php';

const SETTING_DEFAULTS = [
    'phone'            => '+91 00000 00000',
    'phone_alt'        => '',
    'whatsapp'         => '910000000000',
    'email'            => 'hello@example.com',
    'address_primary'  => "Sparkle Constructions\nMandya, Mandya District\nKarnataka, India",
    'address_secondary'=> '',
    'hours'            => 'Mon – Sat · 9:00 AM – 7:00 PM',
    'price_essential'  => '1999',
    'price_classic'    => '2199',
    'price_signature'  => '2499',
    'mrp_essential'    => '2100',
    'mrp_classic'      => '2200',
    'mrp_signature'    => '2500',
    'count_completed'  => '75',
    'count_designs'    => '75',
    'count_inspected'  => '75',
    'enquiry_whatsapp' => '918073144110',
    'wa_greeting'      => "Hi,\nWarm greetings from Sparkle Constructions, Mandya.",
    'client_name'      => 'Devraj A H',
    'client_role'      => 'Founder, Sparkle Constructions',
    'client_bio'       => "Sparkle Constructions was started with one belief: a family’s home should be built with the same care the family puts into dreaming it. Our founder personally reviews every project’s design, material quality and site progress, and stays reachable from the first sketch to the day of griha pravesh.\n\nWith a focus on sustainable, durable and thoughtfully designed homes, the company serves families across the region with transparent costs and honest timelines.",
    'designer_name'    => 'Lead Designer',
    'designer_role'    => 'Architectural & 3D Designer',
    'designer_bio'     => "Our in-house designer turns your ideas, plot and budget into elevations and floor plans you can walk through before a single brick is laid — then stays involved on site so the finished home matches the design.",
    'pw_changed'       => '0',
];

function db(): PDO
{
    static $pdo = null;
    if ($pdo) return $pdo;

    $fresh = !file_exists(DB_FILE);
    $pdo = new PDO('sqlite:' . DB_FILE, null, null, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
    $pdo->exec('PRAGMA journal_mode = WAL; PRAGMA foreign_keys = ON;');
    migrate($pdo, $fresh);
    return $pdo;
}

function migrate(PDO $pdo, bool $fresh): void
{
    $pdo->exec("
    CREATE TABLE IF NOT EXISTS settings (k TEXT PRIMARY KEY, v TEXT NOT NULL DEFAULT '');
    CREATE TABLE IF NOT EXISTS admins (
        id INTEGER PRIMARY KEY AUTOINCREMENT, username TEXT UNIQUE NOT NULL, password_hash TEXT NOT NULL);
    CREATE TABLE IF NOT EXISTS gallery (
        id INTEGER PRIMARY KEY AUTOINCREMENT, file TEXT NOT NULL, title TEXT NOT NULL DEFAULT '',
        category TEXT NOT NULL DEFAULT 'completed', stage INTEGER NOT NULL DEFAULT 0,
        sort INTEGER NOT NULL DEFAULT 0, w INTEGER NOT NULL DEFAULT 0, h INTEGER NOT NULL DEFAULT 0,
        created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP);
    CREATE TABLE IF NOT EXISTS projects (
        id INTEGER PRIMARY KEY AUTOINCREMENT, title TEXT NOT NULL, type TEXT NOT NULL DEFAULT 'Residential',
        summary TEXT NOT NULL DEFAULT '', gallery_id INTEGER REFERENCES gallery(id) ON DELETE SET NULL,
        featured INTEGER NOT NULL DEFAULT 0, sort INTEGER NOT NULL DEFAULT 0);
    CREATE TABLE IF NOT EXISTS enquiries (
        id INTEGER PRIMARY KEY AUTOINCREMENT, name TEXT NOT NULL, phone TEXT NOT NULL, email TEXT NOT NULL DEFAULT '',
        service TEXT NOT NULL DEFAULT '', message TEXT NOT NULL DEFAULT '', source TEXT NOT NULL DEFAULT 'contact',
        is_read INTEGER NOT NULL DEFAULT 0, created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP);
    CREATE TABLE IF NOT EXISTS login_attempts (ip TEXT NOT NULL, at INTEGER NOT NULL);
    ");

    // Videos: `file` holds the cover image (so every photo slot can show it), `video` the clip itself.
    $cols = array_column($pdo->query('PRAGMA table_info(gallery)')->fetchAll(), 'name');
    if (!in_array('video', $cols, true)) $pdo->exec("ALTER TABLE gallery ADD COLUMN video TEXT NOT NULL DEFAULT ''");

    $ins = $pdo->prepare('INSERT OR IGNORE INTO settings (k, v) VALUES (?, ?)');
    foreach (SETTING_DEFAULTS as $k => $v) $ins->execute([$k, $v]);

    // One-off: move existing sites to the Economy / Standard / Premium offer rates.
    if (!$pdo->query("SELECT 1 FROM settings WHERE k = 'pkg_rates_v2'")->fetchColumn()) {
        $up = $pdo->prepare('UPDATE settings SET v = ? WHERE k = ?');
        foreach (['price_essential', 'price_classic', 'price_signature'] as $k) $up->execute([SETTING_DEFAULTS[$k], $k]);
        $pdo->exec("INSERT INTO settings (k, v) VALUES ('pkg_rates_v2', '1')");
    }

    if (!(int)$pdo->query('SELECT COUNT(*) FROM gallery')->fetchColumn()) seed_gallery($pdo);

    // One-off: regroup the original site photos into the 15-stage construction journey, in build order.
    if (!$pdo->query("SELECT 1 FROM settings WHERE k = 'journey_v2'")->fetchColumn()) {
        $journey = [   // file => [stage, title]
            'j-site-marking.jpg'      => [1,  'Site marking & layout'],
            'j-excavation.jpg'        => [2,  'Excavation to hard strata'],
            'j-footing-steel.jpg'     => [3,  'Footing steel before the pour'],
            'j-footing-concrete.jpg'  => [3,  'Footing concrete cast'],
            'j-plinth-blockwork.jpg'  => [4,  'Size stone masonry (SSM)'],
            'j-walls-columns.jpg'     => [4,  'Size stone masonry with column steel'],
            'j-plinth-beam.jpg'       => [5,  'Plinth beam steel'],
            'j-plinth-palms.jpg'      => [6,  'Plinth beam concrete'],
            'j-floor-concrete.jpg'    => [7,  'Floor concrete finishing after anti-termite treatment'],
            'j-columns.jpg'           => [8,  'Columns rising'],
            'j-brick-duplex.jpg'      => [9,  'Brick masonry, upper floors'],
            'j-walls-interior.jpg'    => [10, 'Block walls'],
            'j-structure-stair.jpg'   => [11, 'Structure with staircase'],
            'j-stair-soffit.jpg'      => [11, 'Staircase & niche detailing'],
            'j-plaster-triplex.jpg'   => [12, 'Exterior plastering'],
            'j-plaster-exterior.jpg'  => [12, 'Exterior plastering complete'],
            'j-roof-finish.jpg'       => [13, 'Whitewash finishing'],
            'j-near-complete.jpg'     => [14, 'Building work completed'],
            'j-glass-railing.jpg'     => [14, 'Completed home with glass railing'],
            'j-night-handover.jpg'    => [14, 'Completed home, lit up at night'],
            'j-griha-pravesh.jpg'     => [15, 'Griha pravesh day'],
        ];
        $up = $pdo->prepare("UPDATE gallery SET stage = ?, title = ?, sort = ? WHERE file = ? AND category = 'journey'");
        $i = 0;
        foreach ($journey as $file => [$stage, $title]) $up->execute([$stage, $title, $i++, $file]);
        $pdo->exec("INSERT INTO settings (k, v) VALUES ('journey_v2', '1')");
    }

    // One-off: the original site photos take their stage's name (stages 1–2 keep their own titles).
    if (!$pdo->query("SELECT 1 FROM settings WHERE k = 'journey_v3'")->fetchColumn()) {
        $up = $pdo->prepare("UPDATE gallery SET title = ? WHERE category = 'journey' AND stage = ? AND file LIKE 'j-%'");
        foreach (STAGES as $n => $name) if ($n > 2) $up->execute([$name, $n]);
        $pdo->exec("INSERT INTO settings (k, v) VALUES ('journey_v3', '1')");
    }

    // First admin. Password is forced-change nagged until changed in Settings.
    if (!(int)$pdo->query('SELECT COUNT(*) FROM admins')->fetchColumn()) {
        $pdo->prepare('INSERT INTO admins (username, password_hash) VALUES (?, ?)')
            ->execute(['admin', password_hash('Sparkle@2026', PASSWORD_DEFAULT)]);
    }
}

function seed_gallery(PDO $pdo): void
{
    $f = ROOT . '/data/seed.json';
    if (!is_file($f)) return;
    $items = json_decode((string)file_get_contents($f), true) ?: [];
    $g = $pdo->prepare('INSERT INTO gallery (file,title,category,stage,sort,w,h) VALUES (?,?,?,?,?,?,?)');
    $p = $pdo->prepare('INSERT INTO projects (title,type,summary,gallery_id,featured,sort) VALUES (?,?,?,?,?,?)');
    $types = ['completed' => 'Completed Home', 'designs' => '3D Design'];
    $sums = [
        'completed' => 'A home delivered by our team — built to the approved design with site-engineer supervision at every stage.',
        'designs'   => 'A contemporary elevation concept, designed to be built sustainably and lived in comfortably.',
    ];
    $pdo->beginTransaction();
    $n = 0; $feat = ['completed' => 0, 'designs' => 0];
    foreach ($items as $i => $it) {
        $g->execute([$it['file'], $it['title'], $it['category'], $it['stage'], $i, $it['w'], $it['h']]);
        $gid = (int)$pdo->lastInsertId();
        if (isset($types[$it['category']])) {
            $f = $feat[$it['category']] < 3 ? 1 : 0;   // 3 + 3 featured on the home page
            $feat[$it['category']] += $f;
            $p->execute([$it['title'], $types[$it['category']], $sums[$it['category']], $gid, $f, $n++]);
        }
    }
    $pdo->commit();
}

function setting(string $key): string
{
    static $all = null;
    if ($all === null) {
        $all = [];
        foreach (db()->query('SELECT k, v FROM settings') as $r) $all[$r['k']] = $r['v'];
    }
    return $all[$key] ?? (SETTING_DEFAULTS[$key] ?? '');
}

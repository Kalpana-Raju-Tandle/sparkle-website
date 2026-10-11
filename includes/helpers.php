<?php
declare(strict_types=1);

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/icons.php';

if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params(['httponly' => true, 'samesite' => 'Lax', 'secure' => !empty($_SERVER['HTTPS'])]);
    session_start();
}

function e(?string $s): string { return htmlspecialchars((string)$s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); }
function url(string $p = ''): string
{
    // Clean page addresses: about.php -> about, index.php -> / (admin keeps .php)
    if (preg_match('#^/?([a-z0-9-]+)\.php((?:[?\#].*)?)$#i', $p, $m)) $p = ($m[1] === 'index' ? '' : $m[1]) . $m[2];
    return BASE_URL . '/' . ltrim($p, '/');
}
function asset(string $p): string
{
    $f = ROOT . '/assets/' . $p;
    return url('assets/' . $p) . (is_file($f) ? '?v=' . filemtime($f) : '');
}
function gimg(string $file): string  { return url('uploads/gallery/' . rawurlencode($file)); }
function gthumb(string $file): string
{
    return is_file(THUMB_DIR . $file) ? url('uploads/thumbs/' . rawurlencode($file)) : gimg($file);
}
function rupees(int|string $n): string { return '₹' . number_format((int)$n); }
function nl2p(string $s): string
{
    $out = '';
    foreach (preg_split('/\n\s*\n/', trim($s)) as $p) $out .= '<p>' . nl2br(e(trim($p))) . '</p>';
    return $out;
}
function redirect(string $to): never { header('Location: ' . $to); exit; }

function csrf_token(): string
{
    return $_SESSION['csrf'] ??= bin2hex(random_bytes(24));
}
function csrf_field(): string { return '<input type="hidden" name="_csrf" value="' . e(csrf_token()) . '">'; }
function csrf_ok(): bool
{
    $t = $_POST['_csrf'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
    return is_string($t) && hash_equals(csrf_token(), $t);
}

function flash(?string $msg = null, string $type = 'ok'): ?array
{
    if ($msg !== null) { $_SESSION['flash'] = ['m' => $msg, 't' => $type]; return null; }
    $f = $_SESSION['flash'] ?? null; unset($_SESSION['flash']);
    return $f;
}

function is_admin(): bool { return !empty($_SESSION['admin_id']); }
function require_admin(): void
{
    if (!is_admin()) redirect(url('admin/login.php'));
}

function phone_href(): string { return 'tel:' . preg_replace('/[^\d+]/', '', setting('phone')); }
/** Main phone first, then the extra numbers from Settings (comma or slash separated). */
function phone_numbers(): array
{
    $out = [];
    foreach (array_merge([setting('phone')], preg_split('#[,/\n]+#', setting('phone_alt'))) as $n) {
        $n = trim($n);
        if ($n !== '') $out[] = ['label' => $n, 'href' => 'tel:' . preg_replace('/[^\d+]/', '', $n)];
    }
    return $out;
}
/** Google Maps search link for an address block (the first line, the business name, is skipped). */
function maps_href(string $address): string
{
    $lines = array_values(array_filter(array_map('trim', explode("\n", $address))));
    return 'https://www.google.com/maps/search/?api=1&query=' . rawurlencode(implode(', ', array_slice($lines, 1)) ?: $address);
}
function wa_href(string $text = ''): string
{
    return 'https://wa.me/' . preg_replace('/\D/', '', setting('whatsapp')) . ($text ? '?text=' . rawurlencode($text) : '');
}

function packages(): array
{
    $out = [];
    foreach (PACKAGES as $k => $p) $out[$k] = $p + ['rate' => (int)setting('price_' . $k)];
    return $out;
}
function starting_rate(): int { return min(array_map(fn($p) => $p['rate'], packages())); }

/** Anything still on factory defaults that the client must replace before launch. */
function setup_todos(): array
{
    $t = [];
    if (setting('phone') === SETTING_DEFAULTS['phone']) $t[] = 'Phone number';
    if (setting('whatsapp') === SETTING_DEFAULTS['whatsapp']) $t[] = 'WhatsApp number';
    if (setting('email') === SETTING_DEFAULTS['email']) $t[] = 'Email address';
    if (setting('client_name') === SETTING_DEFAULTS['client_name']) $t[] = 'Client / founder name';
    if (setting('designer_name') === SETTING_DEFAULTS['designer_name']) $t[] = 'Designer name';
    if (setting('pw_changed') !== '1') $t[] = 'Admin password (still the starter one)';
    return $t;
}

/** Save a validated image upload → gallery + thumbnail. Returns [ok, message|filename, w, h]. */
function save_image(array $f): array
{
    if (($f['error'] ?? 1) !== UPLOAD_ERR_OK) {
        $m = [UPLOAD_ERR_INI_SIZE => 'exceeds the server upload limit', UPLOAD_ERR_FORM_SIZE => 'too large'][$f['error'] ?? 0] ?? 'upload failed';
        return [false, $m];
    }
    if ($f['size'] > MAX_UPLOAD_BYTES) return [false, 'larger than 15 MB'];
    $mime = (new finfo(FILEINFO_MIME_TYPE))->file($f['tmp_name']);
    $ext  = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'][$mime] ?? null;
    if (!$ext) return [false, 'only JPG, PNG or WebP images are allowed'];
    $info = @getimagesize($f['tmp_name']);
    if (!$info) return [false, 'not a valid image'];

    $name = bin2hex(random_bytes(8)) . '.' . $ext;
    foreach ([UPLOAD_DIR, THUMB_DIR] as $d) if (!is_dir($d)) mkdir($d, 0755, true);
    [$w, $h] = $info;

    if (extension_loaded('gd')) {
        $src = match ($ext) { 'jpg' => imagecreatefromjpeg($f['tmp_name']), 'png' => imagecreatefrompng($f['tmp_name']), 'webp' => imagecreatefromwebp($f['tmp_name']) };
        if ($ext === 'jpg' && function_exists('exif_read_data')) {
            $o = @exif_read_data($f['tmp_name'])['Orientation'] ?? 1;
            $src = match ($o) { 3 => imagerotate($src, 180, 0), 6 => imagerotate($src, -90, 0), 8 => imagerotate($src, 90, 0), default => $src };
        }
        $resize = function ($im, int $max) {
            $w = imagesx($im); $h = imagesy($im); $r = min(1, $max / max($w, $h));
            $n = imagecreatetruecolor((int)round($w * $r), (int)round($h * $r));
            imagecopyresampled($n, $im, 0, 0, 0, 0, imagesx($n), imagesy($n), $w, $h);
            return $n;
        };
        $big = $resize($src, 1600); $thumb = $resize($src, 720);
        $name = pathinfo($name, PATHINFO_FILENAME) . '.jpg';
        imagejpeg($big, UPLOAD_DIR . $name, 82); imagejpeg($thumb, THUMB_DIR . $name, 78);
        $w = imagesx($big); $h = imagesy($big);
    } else {
        move_uploaded_file($f['tmp_name'], UPLOAD_DIR . $name);
    }
    return [true, $name, $w, $h];
}

function delete_image_files(string $file): void
{
    $file = basename($file);
    foreach ([UPLOAD_DIR, THUMB_DIR] as $d) if (is_file($d . $file)) @unlink($d . $file);
}

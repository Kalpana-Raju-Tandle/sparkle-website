<?php
// Local dev only:  php -S localhost:8000 router.php   (blocks the private folders like Apache's .htaccess does)
$p = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
if (preg_match('#^/(data|includes)/#', $p) || preg_match('#^/uploads/.*\.(php|phtml|phar)$#i', $p)) { http_response_code(403); exit('Forbidden'); }
// Clean addresses like Apache's .htaccess: /about -> about.php, /about.php -> 301 /about
if (preg_match('#^/([a-z0-9-]+)\.php$#i', $p, $m) && strncmp($p, '/admin/', 7) !== 0 && $_SERVER['REQUEST_METHOD'] === 'GET') {
    header('Location: ' . ($m[1] === 'index' ? '/' : '/' . $m[1]) . (($q = $_SERVER['QUERY_STRING'] ?? '') !== '' ? '?' . $q : ''), true, 301); exit;
}
if (preg_match('#^/([a-z0-9-]+)/?$#i', $p, $m) && is_file(__DIR__ . "/{$m[1]}.php")) {
    $_SERVER['SCRIPT_NAME'] = "/{$m[1]}.php"; $_SERVER['SCRIPT_FILENAME'] = __DIR__ . "/{$m[1]}.php";
    chdir(__DIR__); require __DIR__ . "/{$m[1]}.php"; return true;
}
return false;

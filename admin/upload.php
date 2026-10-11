<?php
require_once __DIR__ . '/../includes/helpers.php';
header('Content-Type: application/json; charset=utf-8');

if (!is_admin()) { http_response_code(401); exit(json_encode(['ok' => false, 'error' => 'Please sign in again.'])); }
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !csrf_ok()) { http_response_code(403); exit(json_encode(['ok' => false, 'error' => 'Session expired — refresh the page.'])); }

$fail = fn(string $m) => exit(json_encode(['ok' => false, 'error' => $m]));
$isVideo = isset($_FILES['chunk']);
if (!$isVideo && empty($_FILES['photo'])) $fail('No file received (it may exceed the server upload limit).');

$video = '';
if ($isVideo) {
    // Videos come in several small pieces; only the last request carries the cover image and finishes the upload.
    [$ok, $res] = save_video_chunk((string)($_POST['upload_id'] ?? ''), (int)($_POST['index'] ?? -1), (int)($_POST['total'] ?? 0), $_FILES['chunk']);
    if (!$ok) $fail($res);
    if ($res === '') exit(json_encode(['ok' => true, 'more' => true]));
    $video = $res;
    if (empty($_FILES['poster'])) { delete_video_file($video); $fail('The video cover image was missing — please try again.'); }
    $image = $_FILES['poster'];
} else {
    $image = $_FILES['photo'];
}

$cat = array_key_exists($_POST['category'] ?? '', CATEGORIES) ? $_POST['category'] : 'completed';
$stage = $cat === 'journey' ? max(1, min(count(STAGES), (int)($_POST['stage'] ?? 1))) : 0;
$title = trim((string)($_POST['title'] ?? '')) ?: ucfirst(pathinfo((string)($_POST['name'] ?? $image['name']), PATHINFO_FILENAME));
$title = mb_substr(preg_replace('/[_-]+/', ' ', $title), 0, 120);

[$ok, $res, $w, $h] = array_pad(save_image($image), 4, 0);
if (!$ok) { if ($video) delete_video_file($video); $fail($res); }

$pdo = db();
$sort = (int)$pdo->query('SELECT COALESCE(MAX(sort),0)+1 FROM gallery')->fetchColumn();
$pdo->prepare('INSERT INTO gallery (file,title,category,stage,sort,w,h,video) VALUES (?,?,?,?,?,?,?,?)')->execute([$res, $title, $cat, $stage, $sort, $w, $h, $video]);
$gid = (int)$pdo->lastInsertId();

if (!empty($_POST['as_project']) && in_array($cat, ['completed', 'designs'], true)) {
    $pdo->prepare('INSERT INTO projects (title,type,summary,gallery_id,featured,sort) VALUES (?,?,?,?,0,?)')
        ->execute([$title, $cat === 'designs' ? '3D Design' : 'Completed Home', '', $gid, $sort]);
}
echo json_encode(['ok' => true, 'id' => $gid, 'thumb' => gthumb($res), 'title' => $title]);

<?php
require_once __DIR__ . '/../includes/helpers.php';
header('Content-Type: application/json; charset=utf-8');

if (!is_admin()) { http_response_code(401); exit(json_encode(['ok' => false, 'error' => 'Please sign in again.'])); }
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !csrf_ok()) { http_response_code(403); exit(json_encode(['ok' => false, 'error' => 'Session expired — refresh the page.'])); }
if (empty($_FILES['photo'])) { exit(json_encode(['ok' => false, 'error' => 'No file received (it may exceed the server upload limit).'])); }

$cat = array_key_exists($_POST['category'] ?? '', CATEGORIES) ? $_POST['category'] : 'completed';
$stage = $cat === 'journey' ? max(1, min(count(STAGES), (int)($_POST['stage'] ?? 1))) : 0;
$title = trim((string)($_POST['title'] ?? '')) ?: ucfirst(pathinfo($_FILES['photo']['name'], PATHINFO_FILENAME));
$title = mb_substr(preg_replace('/[_-]+/', ' ', $title), 0, 120);

[$ok, $res, $w, $h] = array_pad(save_image($_FILES['photo']), 4, 0);
if (!$ok) exit(json_encode(['ok' => false, 'error' => $res]));

$pdo = db();
$sort = (int)$pdo->query('SELECT COALESCE(MAX(sort),0)+1 FROM gallery')->fetchColumn();
$pdo->prepare('INSERT INTO gallery (file,title,category,stage,sort,w,h) VALUES (?,?,?,?,?,?,?)')->execute([$res, $title, $cat, $stage, $sort, $w, $h]);
$gid = (int)$pdo->lastInsertId();

if (!empty($_POST['as_project']) && in_array($cat, ['completed', 'designs'], true)) {
    $pdo->prepare('INSERT INTO projects (title,type,summary,gallery_id,featured,sort) VALUES (?,?,?,?,0,?)')
        ->execute([$title, $cat === 'designs' ? '3D Design' : 'Completed Home', '', $gid, $sort]);
}
echo json_encode(['ok' => true, 'id' => $gid, 'thumb' => gthumb($res), 'title' => $title]);

<?php
require __DIR__ . '/_layout.php';
$pdo = db();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_ok()) { flash('Session expired — try again.', 'err'); redirect('projects'); }
    $id = (int)($_POST['id'] ?? 0);
    switch ($_POST['do'] ?? '') {
        case 'save':
            $pdo->prepare('UPDATE projects SET title=?, type=?, summary=?, featured=?, sort=? WHERE id=?')->execute([
                mb_substr(trim($_POST['title']), 0, 120), mb_substr(trim($_POST['type']), 0, 60), mb_substr(trim($_POST['summary']), 0, 400),
                isset($_POST['featured']) ? 1 : 0, (int)$_POST['sort'], $id]);
            flash('Project saved.'); break;
        case 'delete':
            $pdo->prepare('DELETE FROM projects WHERE id=?')->execute([$id]); flash('Project removed (the photo stays in the gallery).'); break;
        case 'add':
            $gid = (int)($_POST['gallery_id'] ?? 0);
            $g = $pdo->prepare("SELECT title, category FROM gallery WHERE id=?"); $g->execute([$gid]); $g = $g->fetch();
            if ($g) {
                $pdo->prepare('INSERT INTO projects (title,type,summary,gallery_id,featured,sort) VALUES (?,?,?,?,0,?)')
                    ->execute([$g['title'], $g['category'] === 'designs' ? '3D Design' : 'Completed Home', '', $gid, 999]);
                flash('Project added.');
            }
    }
    redirect('projects');
}

$rows = $pdo->query('SELECT p.*, g.file FROM projects p LEFT JOIN gallery g ON g.id=p.gallery_id ORDER BY p.featured DESC, p.sort, p.id')->fetchAll();
$avail = $pdo->query("SELECT id, title FROM gallery WHERE category IN ('completed','designs') AND id NOT IN (SELECT gallery_id FROM projects WHERE gallery_id IS NOT NULL) ORDER BY id DESC")->fetchAll();
admin_head('Projects', 'projects');
?>
<div class="panel">
  <h2>Add a project from the gallery</h2>
  <?php if ($avail): ?>
    <form method="post" class="inline"><?= csrf_field() ?><input type="hidden" name="do" value="add">
      <select name="gallery_id"><?php foreach ($avail as $a): ?><option value="<?= (int)$a['id'] ?>"><?= e($a['title']) ?></option><?php endforeach; ?></select>
      <button class="btn sm">Add project</button></form>
  <?php else: ?><p class="muted">Every Completed / 3D photo is already a project. Upload more in Photos &amp; Videos.</p><?php endif; ?>
</div>
<p class="muted">Tick <b>Featured</b> to show a project on the home page (the first five featured are shown). Locations are intentionally not shown on the website.</p>
<div class="panel">
  <?php foreach ($rows as $p): ?>
    <form class="prow" method="post"><?= csrf_field() ?><input type="hidden" name="id" value="<?= (int)$p['id'] ?>">
      <?php if ($p['file']): ?><img src="<?= gthumb($p['file']) ?>" alt=""><?php endif; ?>
      <div class="prow__f">
        <input name="title" value="<?= e($p['title']) ?>" aria-label="Title" required>
        <input name="type" value="<?= e($p['type']) ?>" aria-label="Type">
        <input name="summary" value="<?= e($p['summary']) ?>" aria-label="Summary" placeholder="Short summary (optional)">
        <div class="acts"><label class="chk"><input type="checkbox" name="featured" <?= $p['featured'] ? 'checked' : '' ?>> Featured</label>
          <label class="ord">Order <input type="number" name="sort" value="<?= (int)$p['sort'] ?>"></label>
          <button class="btn sm" name="do" value="save">Save</button>
          <button class="btn sm danger" name="do" value="delete" onclick="return confirm('Remove this project?')">Remove</button></div>
      </div>
    </form>
  <?php endforeach; ?>
</div>
<?php admin_foot();

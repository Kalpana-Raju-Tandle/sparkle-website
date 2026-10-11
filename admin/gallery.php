<?php
require __DIR__ . '/_layout.php';
$pdo = db();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_ok()) { flash('Session expired — try again.', 'err'); redirect('gallery.php'); }
    $id = (int)($_POST['id'] ?? 0);
    if (($_POST['do'] ?? '') === 'delete') {
        $f = $pdo->prepare('SELECT file FROM gallery WHERE id=?'); $f->execute([$id]); $file = $f->fetchColumn();
        if ($file) {
            $pdo->prepare('DELETE FROM projects WHERE gallery_id=?')->execute([$id]);
            $pdo->prepare('DELETE FROM gallery WHERE id=?')->execute([$id]);
            delete_image_files($file);
            flash('Photo deleted.');
        }
    } elseif (($_POST['do'] ?? '') === 'save') {
        $cat = array_key_exists($_POST['category'] ?? '', CATEGORIES) ? $_POST['category'] : 'completed';
        $stage = $cat === 'journey' ? max(1, min(count(STAGES), (int)($_POST['stage'] ?? 1))) : 0;
        $pdo->prepare('UPDATE gallery SET title=?, category=?, stage=?, sort=? WHERE id=?')
            ->execute([mb_substr(trim((string)$_POST['title']), 0, 120), $cat, $stage, (int)($_POST['sort'] ?? 0), $id]);
        flash('Saved.');
    }
    redirect('gallery.php?cat=' . urlencode($_GET['cat'] ?? ''));
}

$cat = $_GET['cat'] ?? '';
$rows = $cat && isset(CATEGORIES[$cat])
    ? $pdo->prepare('SELECT * FROM gallery WHERE category=? ORDER BY stage, sort, id') : null;
if ($rows) { $rows->execute([$cat]); $rows = $rows->fetchAll(); }
else $rows = $pdo->query('SELECT * FROM gallery ORDER BY category, stage, sort, id')->fetchAll();

admin_head('Gallery & Uploads', 'gallery');
?>
<div class="panel">
  <h2>Upload photos</h2>
  <p class="muted">Drag &amp; drop JPG / PNG / WebP photos (up to 15 MB each). They go live on the website immediately.</p>
  <div class="upopts">
    <label>Put them in
      <select id="upCat"><?php foreach (CATEGORIES as $k => $v): ?><option value="<?= $k ?>"><?= e($v) ?></option><?php endforeach; ?></select></label>
    <label id="stageWrap">Construction stage
      <select id="upStage"><?php foreach (STAGES as $n => $v): ?><option value="<?= $n ?>"><?= $n ?> · <?= e($v) ?></option><?php endforeach; ?></select></label>
    <label id="projWrap" class="chk"><input type="checkbox" id="upProj" checked> Also list under Projects</label>
  </div>
  <div class="drop" id="drop" tabindex="0">
    <strong>Drop photos here</strong><span>or click to choose from your computer</span>
    <input type="file" id="file" accept="image/jpeg,image/png,image/webp" multiple hidden>
  </div>
  <ul class="uplist" id="uplist"></ul>
</div>

<div class="panel">
  <h2>All photos <small>(<?= count($rows) ?>)</small></h2>
  <div class="filters">
    <a href="gallery.php" class="<?= !$cat ? 'on' : '' ?>">All</a>
    <?php foreach (CATEGORIES as $k => $v): ?><a href="?cat=<?= $k ?>" class="<?= $cat === $k ? 'on' : '' ?>"><?= e($v) ?></a><?php endforeach; ?>
  </div>
  <div class="thumbs">
    <?php foreach ($rows as $g): ?>
      <form class="thumb" method="post" action="gallery.php?cat=<?= e($cat) ?>">
        <?= csrf_field() ?><input type="hidden" name="id" value="<?= (int)$g['id'] ?>">
        <img src="<?= gthumb($g['file']) ?>" alt="" loading="lazy">
        <input name="title" value="<?= e($g['title']) ?>" aria-label="Title">
        <select name="category" class="catsel"><?php foreach (CATEGORIES as $k => $v): ?><option value="<?= $k ?>" <?= $g['category'] === $k ? 'selected' : '' ?>><?= e($v) ?></option><?php endforeach; ?></select>
        <select name="stage" aria-label="Stage" <?= $g['category'] !== 'journey' ? 'hidden' : '' ?>><?php foreach (STAGES as $n => $v): ?><option value="<?= $n ?>" <?= (int)$g['stage'] === $n ? 'selected' : '' ?>><?= $n ?> · <?= e($v) ?></option><?php endforeach; ?></select>
        <label class="ord">Order <input type="number" name="sort" value="<?= (int)$g['sort'] ?>"></label>
        <div class="acts"><button class="btn sm" name="do" value="save">Save</button>
          <button class="btn sm danger" name="do" value="delete" onclick="return confirm('Delete this photo permanently?')">Delete</button></div>
      </form>
    <?php endforeach; ?>
  </div>
</div>

<script>
const csrf = <?= json_encode(csrf_token()) ?>, uploadUrl = <?= json_encode(url('admin/upload.php')) ?>;
const $ = s => document.querySelector(s);
const cat = $('#upCat'), sync = () => { $('#stageWrap').style.display = cat.value === 'journey' ? '' : 'none'; $('#projWrap').style.display = ['completed','designs'].includes(cat.value) ? '' : 'none'; };
cat.onchange = sync; sync();
document.querySelectorAll('.catsel').forEach(s => s.onchange = () => { s.form.querySelector('[name=stage]').hidden = s.value !== 'journey'; });

const drop = $('#drop'), file = $('#file'), list = $('#uplist');
drop.onclick = () => file.click();
drop.onkeydown = e => (e.key === 'Enter' || e.key === ' ') && file.click();
['dragenter', 'dragover'].forEach(ev => drop.addEventListener(ev, e => { e.preventDefault(); drop.classList.add('over'); }));
['dragleave', 'drop'].forEach(ev => drop.addEventListener(ev, e => { e.preventDefault(); drop.classList.remove('over'); }));
drop.addEventListener('drop', e => send([...e.dataTransfer.files]));
file.onchange = () => { send([...file.files]); file.value = ''; };

let pending = 0;
function send(files) {
  files.filter(f => f.type.startsWith('image/')).forEach(f => {
    const li = document.createElement('li'); li.innerHTML = '<span></span><i><b></b></i><em>Uploading…</em>'; li.firstChild.textContent = f.name; list.prepend(li);
    const fd = new FormData(); fd.append('photo', f); fd.append('_csrf', csrf); fd.append('category', cat.value); fd.append('stage', $('#upStage').value);
    if ($('#upProj').checked) fd.append('as_project', '1');
    const x = new XMLHttpRequest(); x.open('POST', uploadUrl); x.setRequestHeader('X-CSRF-Token', csrf); pending++;
    x.upload.onprogress = e => li.querySelector('b').style.width = (e.loaded / e.total * 100) + '%';
    x.onload = () => { let r; try { r = JSON.parse(x.responseText) } catch { r = { ok: false, error: 'Server error' } }
      li.className = r.ok ? 'ok' : 'bad'; li.querySelector('em').textContent = r.ok ? 'Live on website ✓' : '✕ ' + r.error;
      if (--pending === 0 && r.ok) setTimeout(() => location.reload(), 1200); };
    x.onerror = () => { li.className = 'bad'; li.querySelector('em').textContent = '✕ Network error'; pending--; };
    x.send(fd);
  });
}
</script>
<?php admin_foot();

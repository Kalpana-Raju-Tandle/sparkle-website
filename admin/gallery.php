<?php
require __DIR__ . '/_layout.php';
$pdo = db();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_ok()) { flash('Session expired — try again.', 'err'); redirect('gallery.php'); }
    $id = (int)($_POST['id'] ?? 0);
    if (($_POST['do'] ?? '') === 'delete') {
        $f = $pdo->prepare('SELECT file, video FROM gallery WHERE id=?'); $f->execute([$id]); $item = $f->fetch();
        if ($item) {
            $pdo->prepare('DELETE FROM projects WHERE gallery_id=?')->execute([$id]);
            $pdo->prepare('DELETE FROM gallery WHERE id=?')->execute([$id]);
            delete_image_files($item['file']);
            delete_video_file($item['video']);
            flash($item['video'] ? 'Video deleted.' : 'Photo deleted.');
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

admin_head('Photos & Videos', 'gallery');
?>
<div class="panel">
  <h2>Upload photos &amp; videos</h2>
  <p class="muted">Drag &amp; drop photos (JPG / PNG / WebP, up to 15 MB) or videos (MP4 / MOV / WebM, up to <?= MAX_VIDEO_BYTES >> 20 ?> MB). They go live on the website immediately — videos show with a ▶ play button.</p>
  <div class="upopts">
    <label>Put them in
      <select id="upCat"><?php foreach (CATEGORIES as $k => $v): ?><option value="<?= $k ?>"><?= e($v) ?></option><?php endforeach; ?></select></label>
    <label id="stageWrap">Construction stage
      <select id="upStage"><?php foreach (STAGES as $n => $v): ?><option value="<?= $n ?>"><?= $n ?> · <?= e($v) ?></option><?php endforeach; ?></select></label>
    <label id="projWrap" class="chk"><input type="checkbox" id="upProj" checked> Also list under Projects</label>
  </div>
  <div class="drop" id="drop" tabindex="0">
    <strong>Drop photos or videos here</strong><span>or click to choose from your computer or phone</span>
    <input type="file" id="file" accept="image/jpeg,image/png,image/webp,video/mp4,video/quicktime,video/webm,.mp4,.mov,.m4v,.webm" multiple hidden>
  </div>
  <ul class="uplist" id="uplist"></ul>
</div>

<div class="panel">
  <h2>All photos &amp; videos <small>(<?= count($rows) ?>)</small></h2>
  <div class="filters">
    <a href="gallery.php" class="<?= !$cat ? 'on' : '' ?>">All</a>
    <?php foreach (CATEGORIES as $k => $v): ?><a href="?cat=<?= $k ?>" class="<?= $cat === $k ? 'on' : '' ?>"><?= e($v) ?></a><?php endforeach; ?>
  </div>
  <div class="thumbs">
    <?php foreach ($rows as $g): ?>
      <form class="thumb <?= $g['video'] ? 'is-video' : '' ?>" method="post" action="gallery.php?cat=<?= e($cat) ?>">
        <?= csrf_field() ?><input type="hidden" name="id" value="<?= (int)$g['id'] ?>">
        <?php if ($g['video']): ?><a class="vid" href="<?= e(gvideo($g)) ?>" target="_blank" rel="noopener" title="Play video"><img src="<?= gthumb($g['file']) ?>" alt="" loading="lazy"><span>▶ Video</span></a>
        <?php else: ?><img src="<?= gthumb($g['file']) ?>" alt="" loading="lazy"><?php endif; ?>
        <input name="title" value="<?= e($g['title']) ?>" aria-label="Title">
        <select name="category" class="catsel"><?php foreach (CATEGORIES as $k => $v): ?><option value="<?= $k ?>" <?= $g['category'] === $k ? 'selected' : '' ?>><?= e($v) ?></option><?php endforeach; ?></select>
        <select name="stage" aria-label="Stage" <?= $g['category'] !== 'journey' ? 'hidden' : '' ?>><?php foreach (STAGES as $n => $v): ?><option value="<?= $n ?>" <?= (int)$g['stage'] === $n ? 'selected' : '' ?>><?= $n ?> · <?= e($v) ?></option><?php endforeach; ?></select>
        <label class="ord">Order <input type="number" name="sort" value="<?= (int)$g['sort'] ?>"></label>
        <div class="acts"><button class="btn sm" name="do" value="save">Save</button>
          <button class="btn sm danger" name="do" value="delete" onclick="return confirm('Delete this <?= $g['video'] ? 'video' : 'photo' ?> permanently?')">Delete</button></div>
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
const CHUNK = <?= video_chunk_bytes() ?>, MAX_VIDEO = <?= MAX_VIDEO_BYTES ?>;
function row(name) {
  const li = document.createElement('li'); li.innerHTML = '<span></span><i><b></b></i><em>Uploading…</em>'; li.firstChild.textContent = name; list.prepend(li);
  return { bar: p => li.querySelector('b').style.width = p + '%', msg: t => li.querySelector('em').textContent = t,
    done: (ok, t) => { li.className = ok ? 'ok' : 'bad'; li.querySelector('em').textContent = t; } };
}
function withOptions(fd) {
  fd.append('category', cat.value); fd.append('stage', $('#upStage').value);
  if ($('#upProj').checked) fd.append('as_project', '1');
  return fd;
}
function post(fd, onprogress) {
  fd.append('_csrf', csrf);
  return new Promise(res => {
    const x = new XMLHttpRequest(); x.open('POST', uploadUrl); x.setRequestHeader('X-CSRF-Token', csrf);
    if (onprogress) x.upload.onprogress = e => onprogress(e.loaded);
    x.onload = () => { try { res(JSON.parse(x.responseText)) } catch { res({ ok: false, error: x.status === 413 ? 'The server refused the file as too large' : 'Server error' }) } };
    x.onerror = () => res({ ok: false, error: 'Network error — check the connection and try again', network: true });
    x.send(fd);
  });
}
function finished(ok) { if (--pending === 0 && ok) setTimeout(() => location.reload(), 1200); }

async function sendPhoto(f) {
  const r = row(f.name); pending++;
  const fd = withOptions(new FormData()); fd.append('photo', f);
  const out = await post(fd, n => r.bar(n / f.size * 100));
  r.done(out.ok, out.ok ? 'Live on website ✓' : '✕ ' + out.error); finished(out.ok);
}

/* Grab a frame from the video in the browser to use as its cover image — this also proves browsers can play it. */
function coverFrom(f) {
  return new Promise((resolve, reject) => {
    const v = document.createElement('video'), src = URL.createObjectURL(f);
    let t;
    const fail = () => { clearTimeout(t); URL.revokeObjectURL(src); reject(new Error("This video's format can't play in web browsers. Please save/share it as MP4 (H.264) and upload again.")); };
    t = setTimeout(fail, 20000);
    v.muted = true; v.playsInline = true; v.preload = 'auto'; v.src = src;
    v.onerror = fail;
    v.onloadedmetadata = () => { v.currentTime = Math.min(1, (v.duration || 2) / 3); };
    v.onseeked = () => {
      clearTimeout(t);
      if (!v.videoWidth) return fail();
      const k = Math.min(1, 1600 / Math.max(v.videoWidth, v.videoHeight)), c = document.createElement('canvas');
      c.width = Math.round(v.videoWidth * k); c.height = Math.round(v.videoHeight * k);
      c.getContext('2d').drawImage(v, 0, 0, c.width, c.height);
      c.toBlob(b => { URL.revokeObjectURL(src); b ? resolve(b) : fail(); }, 'image/jpeg', 0.85);
    };
  });
}

async function sendVideo(f) {
  const r = row(f.name); pending++;
  if (f.size > MAX_VIDEO) { r.done(false, '✕ Larger than ' + Math.round(MAX_VIDEO / 1048576) + ' MB — please trim or compress it'); return finished(false); }
  let cover;
  try { r.msg('Preparing…'); cover = await coverFrom(f); } catch (e) { r.done(false, '✕ ' + e.message); return finished(false); }
  const id = [...crypto.getRandomValues(new Uint8Array(12))].map(b => b.toString(16).padStart(2, '0')).join('');
  const total = Math.max(1, Math.ceil(f.size / CHUNK));
  r.msg('Uploading…');
  for (let i = 0; i < total; i++) {
    let out;
    for (let attempt = 0; attempt < 3; attempt++) {      // retry a piece on a flaky connection
      const fd = new FormData();
      fd.append('upload_id', id); fd.append('index', i); fd.append('total', total);
      fd.append('chunk', f.slice(i * CHUNK, (i + 1) * CHUNK), 'part');
      if (i === total - 1) { withOptions(fd); fd.append('poster', cover, 'cover.jpg'); fd.append('name', f.name); }
      out = await post(fd, n => r.bar(Math.min(100, (i * CHUNK + n) / f.size * 100)));
      if (out.ok || !out.network) break;
    }
    if (!out.ok) { r.done(false, '✕ ' + out.error); return finished(false); }
  }
  r.bar(100); r.done(true, 'Live on website ✓'); finished(true);
}

function send(files) {
  files.forEach(f => {
    if (f.type.startsWith('image/')) sendPhoto(f);
    else if (f.type.startsWith('video/') || /\.(mp4|mov|m4v|webm)$/i.test(f.name)) sendVideo(f);
  });
}
</script>
<?php admin_foot();

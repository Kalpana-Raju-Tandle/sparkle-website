<?php
require __DIR__ . '/_layout.php';
$pdo = db();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_ok()) { flash('Session expired — try again.', 'err'); redirect('gallery'); }
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
    redirect('gallery?' . http_build_query(array_filter(['cat' => $_GET['cat'] ?? '', 'sort' => $_GET['sort'] ?? ''])));
}

$cat = isset(CATEGORIES[$_GET['cat'] ?? '']) ? $_GET['cat'] : '';
$sort = ($_GET['sort'] ?? '') === 'site' ? 'site' : 'new';      // default: newest uploads first
$order = $sort === 'new' ? 'id DESC' : ($cat ? 'stage, sort, id' : 'category, stage, sort, id');
$st = $pdo->prepare('SELECT * FROM gallery' . ($cat ? ' WHERE category = ?' : '') . " ORDER BY $order");
$st->execute($cat ? [$cat] : []);
$rows = $st->fetchAll();
$just = array_flip(array_map('intval', array_filter(explode(',', (string)($_GET['just'] ?? '')))));   // ids uploaded a moment ago
$recent = time() - 3 * 86400;                                                                      // "New" = added in the last 3 days
$q = fn(array $over) => 'gallery?' . http_build_query(array_filter(array_merge(['cat' => $cat, 'sort' => $sort === 'site' ? 'site' : ''], $over)));

$counts = array_column($pdo->query('SELECT category, COUNT(*) n FROM gallery GROUP BY category')->fetchAll(), 'n', 'category');
$nVideos = (int)$pdo->query("SELECT COUNT(*) FROM gallery WHERE video != ''")->fetchColumn();
$total = (int)array_sum($counts);
$catIcons = ['journey' => 'layers', 'completed' => 'home', 'designs' => 'pen', 'team' => 'users'];
admin_head('Photos & Videos', 'gallery', '<a class="btn btn--gold" href="#upload">' . icon('upload', 18) . ' Upload</a>');
?>
<div class="upgrid" id="upload">
  <section class="panel upcard">
    <div class="step"><span>1</span><div><h2>Where should they appear?</h2><p class="muted">Pick the section of the website.</p></div></div>
    <div class="segs" role="radiogroup" aria-label="Website section">
      <?php foreach (CATEGORIES as $k => $v): ?>
        <label class="seg"><input type="radio" name="upCatR" value="<?= $k ?>" <?= $k === 'journey' ? 'checked' : '' ?>><span><?= icon($catIcons[$k] ?? 'image', 18) ?><b><?= e($v) ?></b><small><?= (int)($counts[$k] ?? 0) ?> items</small></span></label>
      <?php endforeach; ?>
    </div>
    <select id="upCat" hidden><?php foreach (CATEGORIES as $k => $v): ?><option value="<?= $k ?>"><?= e($v) ?></option><?php endforeach; ?></select>

    <div id="stageWrap" class="stagepick">
      <p class="stagepick__lbl">Construction stage</p>
      <div class="stagechips" role="radiogroup" aria-label="Construction stage">
        <?php foreach (STAGES as $n => $v): ?>
          <label class="stagechip"><input type="radio" name="upStageR" value="<?= $n ?>" <?= $n === 1 ? 'checked' : '' ?>><span><i><?= sprintf('%02d', $n) ?></i><?= e($v) ?></span></label>
        <?php endforeach; ?>
      </div>
      <select id="upStage" hidden><?php foreach (STAGES as $n => $v): ?><option value="<?= $n ?>"><?= e($v) ?></option><?php endforeach; ?></select>
    </div>
    <label id="projWrap" class="switch"><input type="checkbox" id="upProj" checked><span class="switch__ui"></span><span>Also show on the <b>Projects</b> page</span></label>

    <div class="step"><span>2</span><div><h2>Add your files</h2><p class="muted">Photos up to 15 MB · videos up to <?= MAX_VIDEO_BYTES >> 20 ?> MB — they go live instantly.</p></div></div>
    <div class="drop" id="drop" tabindex="0" role="button" aria-label="Choose photos or videos to upload">
      <span class="drop__ic"><?= icon('upload', 26) ?></span>
      <strong>Drag &amp; drop photos or videos</strong>
      <span class="drop__or">or</span>
      <span class="btn btn--dark sm drop__btn">Browse files</span>
      <span class="drop__types"><i>JPG</i><i>PNG</i><i>WebP</i><i>MP4</i><i>MOV</i><i>WebM</i></span>
      <input type="file" id="file" accept="image/jpeg,image/png,image/webp,video/mp4,video/quicktime,video/webm,.mp4,.mov,.m4v,.webm" multiple hidden>
    </div>
    <ul class="uplist" id="uplist"></ul>
  </section>

  <aside class="upside">
    <section class="panel libstat">
      <h2>Your library</h2>
      <div class="libstat__big"><b><?= $total ?></b><span>items on the website</span></div>
      <div class="libstat__pair"><div><?= icon('image', 16) ?><b><?= $total - $nVideos ?></b><small>Photos</small></div><div><?= icon('video', 16) ?><b><?= $nVideos ?></b><small>Videos</small></div></div>
      <ul class="libstat__bars">
        <?php foreach (CATEGORIES as $k => $v): $c = (int)($counts[$k] ?? 0); ?>
          <li><span><?= e($v) ?></span><b><?= $c ?></b><i><em style="width:<?= $total ? round($c / $total * 100) : 0 ?>%"></em></i></li>
        <?php endforeach; ?>
      </ul>
    </section>
    <section class="panel tips">
      <h2><?= icon('award', 18) ?> Tips for great uploads</h2>
      <ul>
        <li>Hold the phone <b>sideways</b> for site photos and videos.</li>
        <li>Short clips (<b>15–60 seconds</b>) load fastest for visitors.</li>
        <li>Upload videos as <b>MP4</b> so they play on every phone.</li>
        <li>Give each item a clear title, e.g. <i>“Slab casting – Mandya”</i>.</li>
      </ul>
    </section>
  </aside>
</div>

<section class="panel lib">
  <div class="lib__bar">
    <h2>Media library <small><?= count($rows) ?> shown</small></h2>
    <div class="lib__tools">
      <div class="sortsw" role="group" aria-label="Sort">
        <a href="<?= e($q(['sort' => ''])) ?>" class="<?= $sort === 'new' ? 'on' : '' ?>"><?= icon('clock', 14) ?> Newest first</a>
        <a href="<?= e($q(['sort' => 'site'])) ?>" class="<?= $sort === 'site' ? 'on' : '' ?>"><?= icon('grid', 14) ?> Website order</a>
      </div>
      <label class="search"><?= icon('search', 16) ?><input type="search" id="libSearch" placeholder="Search by title…" aria-label="Search by title"></label>
    </div>
  </div>
  <div class="tabs">
    <a href="<?= e($q(['cat' => ''])) ?>" class="<?= !$cat ? 'on' : '' ?>">All <i><?= $total ?></i></a>
    <?php foreach (CATEGORIES as $k => $v): ?><a href="<?= e($q(['cat' => $k])) ?>" class="<?= $cat === $k ? 'on' : '' ?>"><?= e($v) ?> <i><?= (int)($counts[$k] ?? 0) ?></i></a><?php endforeach; ?>
  </div>
  <div class="thumbs" id="libGrid">
    <?php foreach ($rows as $g): $added = strtotime($g['created_at'] . ' UTC'); $isJust = isset($just[(int)$g['id']]); ?>
      <form class="thumb <?= $g['video'] ? 'is-video' : '' ?> <?= $isJust ? 'is-just' : '' ?>" method="post" action="<?= e($q([])) ?>" data-title="<?= e(mb_strtolower($g['title'])) ?>">
        <?= csrf_field() ?><input type="hidden" name="id" value="<?= (int)$g['id'] ?>">
        <div class="thumb__media">
          <img src="<?= gthumb($g['file']) ?>" alt="" loading="lazy">
          <span class="thumb__cat"><?= e(CATEGORIES[$g['category']] ?? '') ?><?= $g['category'] === 'journey' ? ' · ' . sprintf('%02d', (int)$g['stage']) : '' ?></span>
          <?php if ($isJust): ?><span class="thumb__new thumb__new--just"><?= icon('check', 12) ?> Just uploaded</span><?php elseif ($added > $recent): ?><span class="thumb__new">New</span><?php endif; ?>
          <?php if ($g['video']): ?><a class="thumb__play" href="<?= e(gvideo($g)) ?>" target="_blank" rel="noopener" title="Play video"><?= icon('video', 14) ?> Video</a><?php endif; ?>
          <a class="thumb__open" href="<?= e($g['video'] ? gvideo($g) : gimg($g['file'])) ?>" target="_blank" rel="noopener" title="Open full size"><?= icon('external', 15) ?></a>
        </div>
        <div class="thumb__body">
          <input name="title" class="thumb__title" value="<?= e($g['title']) ?>" aria-label="Title">
          <small class="thumb__date"><?= icon('clock', 12) ?> Added <?= e(date('d M Y, g:i a', $added)) ?></small>
          <div class="thumb__row">
            <select name="category" class="catsel" aria-label="Section"><?php foreach (CATEGORIES as $k => $v): ?><option value="<?= $k ?>" <?= $g['category'] === $k ? 'selected' : '' ?>><?= e($v) ?></option><?php endforeach; ?></select>
            <label class="ord" title="Display order (lower comes first)">#<input type="number" name="sort" value="<?= (int)$g['sort'] ?>" aria-label="Display order"></label>
          </div>
          <select name="stage" aria-label="Stage" <?= $g['category'] !== 'journey' ? 'hidden' : '' ?>><?php foreach (STAGES as $n => $v): ?><option value="<?= $n ?>" <?= (int)$g['stage'] === $n ? 'selected' : '' ?>><?= sprintf('%02d', $n) ?> · <?= e($v) ?></option><?php endforeach; ?></select>
          <div class="acts">
            <button class="btn sm btn--save" name="do" value="save"><?= icon('check', 15) ?> Save</button>
            <button class="iconbtn danger" name="do" value="delete" title="Delete" aria-label="Delete" onclick="return confirm('Delete this <?= $g['video'] ? 'video' : 'photo' ?> permanently?')"><?= icon('trash', 16) ?></button>
          </div>
        </div>
      </form>
    <?php endforeach; ?>
  </div>
  <p class="muted lib__empty" id="libEmpty" hidden>No items match your search.</p>
</section>

<script>
const csrf = <?= json_encode(csrf_token()) ?>, uploadUrl = <?= json_encode(url('admin/upload')) ?>;
const $ = s => document.querySelector(s);
const cat = $('#upCat'), sync = () => { $('#stageWrap').style.display = cat.value === 'journey' ? '' : 'none'; $('#projWrap').style.display = ['completed','designs'].includes(cat.value) ? '' : 'none'; };
cat.onchange = sync; sync();
// Chips and switches drive the (hidden) selects the upload code reads.
document.querySelectorAll('[name=upCatR]').forEach(r => r.onchange = () => { cat.value = r.value; sync(); });
document.querySelectorAll('[name=upStageR]').forEach(r => r.onchange = () => { $('#upStage').value = r.value; });
// A card's Save button lights up once something on that card is edited.
const firstJust = document.querySelector('.thumb.is-just');
if (firstJust) setTimeout(() => firstJust.scrollIntoView({ behavior: 'smooth', block: 'center' }), 300);
const cards0 = document.querySelectorAll('#libGrid .thumb');
cards0.forEach(f => f.addEventListener('input', () => f.classList.add('is-dirty')));
const search = $('#libSearch'), cards = [...document.querySelectorAll('#libGrid .thumb')];
search.oninput = () => {
  const q = search.value.trim().toLowerCase(); let shown = 0;
  cards.forEach(c => { const hit = !q || c.dataset.title.includes(q); c.hidden = !hit; shown += hit; });
  $('#libEmpty').hidden = shown > 0;
};
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
const doneIds = [];
function finished(ok, id) {
  if (ok && id) doneIds.push(id);
  if (--pending === 0 && doneIds.length) setTimeout(() => { location.href = 'gallery?just=' + doneIds.join(',') + '#libGrid'; }, 1000);
}

async function sendPhoto(f) {
  const r = row(f.name); pending++;
  const fd = withOptions(new FormData()); fd.append('photo', f);
  const out = await post(fd, n => r.bar(n / f.size * 100));
  r.done(out.ok, out.ok ? 'Live on website ✓' : '✕ ' + out.error); finished(out.ok, out.id);
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
  let out;
  for (let i = 0; i < total; i++) {
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
  r.bar(100); r.done(true, 'Live on website ✓'); finished(true, out.id);
}

function send(files) {
  files.forEach(f => {
    if (f.type.startsWith('image/')) sendPhoto(f);
    else if (f.type.startsWith('video/') || /\.(mp4|mov|m4v|webm)$/i.test(f.name)) sendVideo(f);
  });
}
</script>
<?php admin_foot();

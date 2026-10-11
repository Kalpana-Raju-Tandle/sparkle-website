<?php
require __DIR__ . '/_layout.php';
$pdo = db();
$n = fn($sql) => (int)$pdo->query($sql)->fetchColumn();
$recent = $pdo->query('SELECT * FROM enquiries ORDER BY id DESC LIMIT 5')->fetchAll();
$uploads = $pdo->query('SELECT * FROM gallery ORDER BY id DESC LIMIT 8')->fetchAll();
$todos = setup_todos();
$hour = (int)date('G');
$greet = $hour < 12 ? 'Good morning' : ($hour < 17 ? 'Good afternoon' : 'Good evening');
$stats = [
    ['New enquiries', $n('SELECT COUNT(*) FROM enquiries WHERE is_read=0'), 'enquiries', 'mail', 'is-hot'],
    ['Photos', $n("SELECT COUNT(*) FROM gallery WHERE video=''"), 'gallery', 'image', ''],
    ['Videos', $n("SELECT COUNT(*) FROM gallery WHERE video!=''"), 'gallery', 'video', ''],
    ['Projects', $n('SELECT COUNT(*) FROM projects'), 'projects', 'home', ''],
];
admin_head('Dashboard', 'index');
?>
<?php if ($todos): ?>
  <div class="note note--warn"><?= icon('award', 18) ?><span><b>Finish setting up:</b> <?= e(implode(' · ', $todos)) ?>. <a href="<?= url('admin/settings') ?>">Open Settings →</a></span></div>
<?php endif; ?>

<section class="welcome">
  <div>
    <p class="welcome__kick"><?= e($greet) ?></p>
    <h2>Show your latest work <em>today.</em></h2>
    <p>Add site photos and construction videos — they appear on your website the moment they finish uploading.</p>
    <div class="welcome__btns">
      <a class="btn btn--gold" href="gallery"><?= icon('camera', 18) ?> Upload photos &amp; videos</a>
      <a class="btn btn--ghost" href="enquiries"><?= icon('mail', 18) ?> Read enquiries</a>
    </div>
  </div>
  <div class="welcome__art" aria-hidden="true"><?= icon('home', 120) ?></div>
</section>

<div class="stats">
  <?php foreach ($stats as [$label, $val, $href, $ic, $cls]): ?>
    <a class="stat <?= $val && $cls ? $cls : '' ?>" href="<?= $href ?>">
      <span class="stat__ic"><?= icon($ic, 20) ?></span>
      <b><?= $val ?></b><span><?= e($label) ?></span>
    </a>
  <?php endforeach; ?>
</div>

<div class="cols">
  <section class="panel">
    <div class="panel__head"><h2>Latest uploads</h2><a href="gallery">See all →</a></div>
    <?php if (!$uploads): ?><p class="muted">No photos yet — <a href="gallery">upload the first one</a>.</p><?php endif; ?>
    <div class="recent">
      <?php foreach ($uploads as $g): ?>
        <a class="recent__it" href="gallery?cat=<?= e($g['category']) ?>" title="<?= e($g['title']) ?>">
          <img src="<?= gthumb($g['file']) ?>" alt="" loading="lazy">
          <?php if ($g['video']): ?><span class="recent__vid">▶</span><?php endif; ?>
          <small><?= e(CATEGORIES[$g['category']] ?? '') ?></small>
        </a>
      <?php endforeach; ?>
    </div>
  </section>

  <section class="panel">
    <div class="panel__head"><h2>Latest enquiries</h2><a href="enquiries">See all →</a></div>
    <?php if (!$recent): ?><p class="muted">Nothing yet — enquiries from the Contact page and the cost estimator will show up here.</p><?php endif; ?>
    <ul class="enqlist">
      <?php foreach ($recent as $r): ?>
        <li class="<?= $r['is_read'] ? '' : 'is-new' ?>">
          <span class="enqlist__av"><?= e(mb_strtoupper(mb_substr($r['name'], 0, 1))) ?></span>
          <div><b><?= e($r['name']) ?></b><small><?= e($r['source'] === 'estimate' ? 'Cost estimator' : 'Contact form') ?> · <?= e(date('d M, g:i a', strtotime($r['created_at'] . ' UTC'))) ?></small></div>
          <a class="enqlist__call" href="tel:<?= e($r['phone']) ?>" aria-label="Call <?= e($r['name']) ?>"><?= icon('phone', 16) ?></a>
        </li>
      <?php endforeach; ?>
    </ul>
  </section>
</div>
<?php admin_foot();

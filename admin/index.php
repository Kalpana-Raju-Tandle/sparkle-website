<?php
require __DIR__ . '/_layout.php';
$pdo = db();
$n = fn($sql) => (int)$pdo->query($sql)->fetchColumn();
$recent = $pdo->query('SELECT * FROM enquiries ORDER BY id DESC LIMIT 5')->fetchAll();
$todos = setup_todos();
admin_head('Dashboard', 'index');
?>
<?php if ($todos): ?>
  <div class="note note--warn"><b>Before launch, please update:</b> <?= e(implode(' · ', $todos)) ?>. <a href="<?= url('admin/settings.php') ?>">Open Settings →</a></div>
<?php endif; ?>
<div class="tiles">
  <a class="tile" href="enquiries.php"><b><?= $n('SELECT COUNT(*) FROM enquiries WHERE is_read=0') ?></b><span>New enquiries</span></a>
  <a class="tile" href="gallery.php"><b><?= $n('SELECT COUNT(*) FROM gallery') ?></b><span>Photos in gallery</span></a>
  <a class="tile" href="projects.php"><b><?= $n('SELECT COUNT(*) FROM projects') ?></b><span>Projects</span></a>
  <a class="tile" href="enquiries.php"><b><?= $n('SELECT COUNT(*) FROM enquiries') ?></b><span>Total enquiries</span></a>
</div>
<div class="panel">
  <h2>Quick start</h2>
  <p><b>Add photos to the website:</b> go to <a href="gallery.php">Gallery &amp; Uploads</a>, drag &amp; drop your photos, pick where they belong (Construction Journey stage, Completed Homes or 3D Designs) — they appear on the website instantly.</p>
</div>
<div class="panel">
  <h2>Latest enquiries</h2>
  <?php if (!$recent): ?><p class="muted">Nothing yet — enquiries from the Contact page and the estimator will show up here.</p><?php endif; ?>
  <?php foreach ($recent as $r): ?>
    <div class="row"><b><?= e($r['name']) ?></b> <a href="tel:<?= e($r['phone']) ?>"><?= e($r['phone']) ?></a> <span class="muted"><?= e($r['created_at']) ?> · <?= e($r['source']) ?></span></div>
  <?php endforeach; ?>
</div>
<?php admin_foot();

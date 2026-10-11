<?php
require __DIR__ . '/_layout.php';
$pdo = db();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (csrf_ok()) {
        $id = (int)($_POST['id'] ?? 0);
        if (($_POST['do'] ?? '') === 'read') $pdo->prepare('UPDATE enquiries SET is_read=1 WHERE id=?')->execute([$id]);
        if (($_POST['do'] ?? '') === 'readall') $pdo->exec('UPDATE enquiries SET is_read=1');
        if (($_POST['do'] ?? '') === 'delete') $pdo->prepare('DELETE FROM enquiries WHERE id=?')->execute([$id]);
    }
    redirect('enquiries');
}
$rows = $pdo->query('SELECT * FROM enquiries ORDER BY id DESC LIMIT 300')->fetchAll();
admin_head('Enquiries', 'enquiries');
?>
<form method="post" class="inline"><?= csrf_field() ?><button class="btn sm" name="do" value="readall">Mark all as read</button></form>
<div class="panel">
  <?php if (!$rows): ?><p class="muted">No enquiries yet.</p><?php endif; ?>
  <?php foreach ($rows as $r): ?>
    <article class="enq <?= $r['is_read'] ? '' : 'new' ?>">
      <header><b><?= e($r['name']) ?></b>
        <a href="tel:<?= e($r['phone']) ?>"><?= e($r['phone']) ?></a>
        <?php if ($r['email']): ?><a href="mailto:<?= e($r['email']) ?>"><?= e($r['email']) ?></a><?php endif; ?>
        <span class="muted"><?= e($r['created_at']) ?> UTC · <?= e($r['source']) ?></span></header>
      <?php if ($r['service']): ?><p><b>Interested in:</b> <?= e($r['service']) ?></p><?php endif; ?>
      <?php if ($r['message']): ?><p><?= nl2br(e($r['message'])) ?></p><?php endif; ?>
      <form method="post" class="inline"><?= csrf_field() ?><input type="hidden" name="id" value="<?= (int)$r['id'] ?>">
        <?php if (!$r['is_read']): ?><button class="btn sm" name="do" value="read">Mark read</button><?php endif; ?>
        <a class="btn sm" target="_blank" rel="noopener" href="https://wa.me/<?= e(preg_replace('/\D/', '', $r['phone'])) ?>">WhatsApp</a>
        <button class="btn sm danger" name="do" value="delete" onclick="return confirm('Delete this enquiry?')">Delete</button></form>
    </article>
  <?php endforeach; ?>
</div>
<?php admin_foot();

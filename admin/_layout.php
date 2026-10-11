<?php
require_once __DIR__ . '/../includes/helpers.php';
require_admin();
header('X-Robots-Tag: noindex, nofollow');

function admin_head(string $title, string $active): void
{
    $unread = (int)db()->query('SELECT COUNT(*) FROM enquiries WHERE is_read = 0')->fetchColumn();
    $f = flash();
    $links = [
        'index'     => ['Dashboard', 'index.php', 'grid'],
        'gallery'   => ['Photos & Videos', 'gallery.php', 'camera'],
        'projects'  => ['Projects', 'projects.php', 'home'],
        'enquiries' => ['Enquiries', 'enquiries.php', 'mail'],
        'settings'  => ['Settings', 'settings.php', 'tool'],
    ];
    $subs = [
        'index'     => 'Everything on your website, at a glance.',
        'gallery'   => 'Upload site photos and construction videos — they go live instantly.',
        'projects'  => 'Choose which homes are featured and how they are described.',
        'enquiries' => 'Messages from the Contact page and the cost estimator.',
        'settings'  => 'Phone numbers, addresses, prices and your login password.',
    ];
    ?><!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<meta name="robots" content="noindex,nofollow"><title><?= e($title) ?> · Sparkle Admin</title>
<link rel="icon" href="<?= asset('img/icon.png') ?>">
<link rel="preconnect" href="https://fonts.googleapis.com"><link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@1,500&family=Inter:wght@400;500;600&family=Outfit:wght@500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?= asset('css/admin.css') ?>"></head>
<body class="adm">
<header class="topbar">
  <button class="topbar__menu" id="admMenu" aria-label="Open menu" aria-expanded="false"><span></span><span></span><span></span></button>
  <a class="topbar__logo" href="<?= url('admin/') ?>"><img src="<?= asset('img/logo.png') ?>" alt="Sparkle Constructions"></a>
  <a class="topbar__site" href="<?= url('index.php') ?>" target="_blank" rel="noopener" aria-label="View website"><?= icon('arrow', 18) ?></a>
</header>
<aside class="side" id="admSide">
  <a class="side__logo" href="<?= url('admin/') ?>"><img src="<?= asset('img/logo.png') ?>" alt="Sparkle Constructions"></a>
  <p class="side__label">Menu</p>
  <nav>
    <?php foreach ($links as $k => [$l, $h, $ic]): ?>
      <a href="<?= url('admin/' . $h) ?>" class="<?= $k === $active ? 'on' : '' ?>"><span class="side__ic"><?= icon($ic, 18) ?></span><?= e($l) ?><?= $k === 'enquiries' && $unread ? "<b>$unread</b>" : '' ?></a>
    <?php endforeach; ?>
  </nav>
  <div class="side__foot">
    <a class="side__site" href="<?= url('index.php') ?>" target="_blank" rel="noopener"><span class="side__ic"><?= icon('arrow', 16) ?></span>View website</a>
    <a class="side__out" href="<?= url('admin/logout.php') ?>"><span class="side__ic"><?= icon('x', 16) ?></span>Sign out</a>
  </div>
</aside>
<div class="scrim" id="admScrim"></div>
<main class="main">
  <div class="phead">
    <div><p class="phead__kick"><i></i>Sparkle Admin</p><h1><?= e($title) ?></h1><p class="phead__sub"><?= e($subs[$active] ?? '') ?></p></div>
    <a class="btn btn--ghost phead__site" href="<?= url('index.php') ?>" target="_blank" rel="noopener">View website <?= icon('arrow', 16) ?></a>
  </div>
<?php if ($f): ?><div class="note note--<?= e($f['t']) ?>"><?= e($f['m']) ?></div><?php endif;
}

function admin_foot(): void
{
    echo <<<'HTML'
</main>
<script>
(() => {
  const b = document.getElementById('admMenu'), s = document.getElementById('admSide'), m = document.getElementById('admScrim');
  const set = on => { document.body.classList.toggle('menu-open', on); b.setAttribute('aria-expanded', on); };
  b.onclick = () => set(!document.body.classList.contains('menu-open'));
  m.onclick = () => set(false);
  addEventListener('keydown', e => e.key === 'Escape' && set(false));
})();
</script>
</body></html>
HTML;
}

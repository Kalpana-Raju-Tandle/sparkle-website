<?php
require_once __DIR__ . '/../includes/helpers.php';
require_admin();
header('X-Robots-Tag: noindex, nofollow');

/**
 * Admin page shell. $actions = optional HTML for the buttons beside the page title.
 */
function admin_head(string $title, string $active, string $actions = ''): void
{
    $pdo = db();
    $unread = (int)$pdo->query('SELECT COUNT(*) FROM enquiries WHERE is_read = 0')->fetchColumn();
    $user = (string)$pdo->query('SELECT username FROM admins WHERE id = ' . (int)$_SESSION['admin_id'])->fetchColumn() ?: 'Admin';
    $f = flash();
    $links = [
        'index'     => ['Dashboard', '', 'grid'],
        'gallery'   => ['Photos & Videos', 'gallery', 'camera'],
        'projects'  => ['Projects', 'projects', 'home'],
        'enquiries' => ['Enquiries', 'enquiries', 'mail'],
        'settings'  => ['Settings', 'settings', 'tool'],
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
<link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@1,500&family=Inter:wght@400;500;600&family=Outfit:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?= asset('css/admin.css') ?>"></head>
<body class="adm">
<aside class="side" id="admSide">
  <a class="side__logo" href="<?= url('admin/') ?>"><img src="<?= asset('img/logo.png') ?>" alt="Sparkle Constructions"></a>
  <p class="side__label">Workspace</p>
  <nav>
    <?php foreach ($links as $k => [$l, $h, $ic]): ?>
      <a href="<?= url('admin/' . $h) ?>" class="<?= $k === $active ? 'on' : '' ?>"><span class="side__ic"><?= icon($ic, 18) ?></span><span class="side__txt"><?= e($l) ?></span><?= $k === 'enquiries' && $unread ? "<b>$unread</b>" : '' ?></a>
    <?php endforeach; ?>
  </nav>
  <a class="side__promo" href="<?= url('admin/gallery') ?>">
    <span class="side__promo-ic"><?= icon('upload', 18) ?></span>
    <b>Share today's progress</b><small>Upload site photos &amp; videos in seconds.</small>
  </a>
  <div class="side__user">
    <span class="avatar"><?= e(mb_strtoupper(mb_substr($user, 0, 1))) ?></span>
    <div><b><?= e(ucfirst($user)) ?></b><small>Administrator</small></div>
    <a class="side__out" href="<?= url('admin/logout') ?>" title="Sign out" aria-label="Sign out"><?= icon('logout', 18) ?></a>
  </div>
</aside>
<div class="scrim" id="admScrim"></div>
<div class="shell">
  <header class="topbar">
    <button class="topbar__menu" id="admMenu" aria-label="Open menu" aria-expanded="false"><span></span><span></span><span></span></button>
    <nav class="crumbs" aria-label="Breadcrumb"><a href="<?= url('admin/') ?>">Admin</a><i>/</i><span><?= e($title) ?></span></nav>
    <div class="topbar__right">
      <span class="topbar__date"><?= icon('calendar', 15) ?><?= e(date('D, d M Y')) ?></span>
      <a class="topbar__site" href="<?= url('index.php') ?>" target="_blank" rel="noopener"><?= icon('external', 15) ?><span>View website</span></a>
      <span class="avatar avatar--sm" title="Signed in as <?= e(ucfirst($user)) ?>"><?= e(mb_strtoupper(mb_substr($user, 0, 1))) ?></span>
      <a class="topbar__out" href="<?= url('admin/logout') ?>" title="Sign out"><?= icon('logout', 15) ?><span>Sign out</span></a>
    </div>
  </header>
  <main class="main">
    <div class="phead">
      <div><h1><?= e($title) ?></h1><p class="phead__sub"><?= e($subs[$active] ?? '') ?></p></div>
      <?php if ($actions): ?><div class="phead__act"><?= $actions ?></div><?php endif; ?>
    </div>
<?php if ($f): ?><div class="note note--<?= e($f['t']) ?>"><?= icon($f['t'] === 'err' ? 'x' : 'check', 18) ?><span><?= e($f['m']) ?></span></div><?php endif;
}

function admin_foot(): void
{
    echo <<<'HTML'
  </main>
  <footer class="adm-credit">Powered by <b>EagleIQ</b></footer>
</div>
<script>
(() => {
  const b = document.getElementById('admMenu'), m = document.getElementById('admScrim');
  const set = on => { document.body.classList.toggle('menu-open', on); b.setAttribute('aria-expanded', on); };
  b.onclick = () => set(!document.body.classList.contains('menu-open'));
  m.onclick = () => set(false);
  addEventListener('keydown', e => e.key === 'Escape' && set(false));
})();
</script>
HTML;
    echo '<script src="' . asset('js/admin.js') . '" defer></script>' . "\n</body></html>";
}

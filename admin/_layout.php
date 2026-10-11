<?php
require_once __DIR__ . '/../includes/helpers.php';
require_admin();
header('X-Robots-Tag: noindex, nofollow');

function admin_head(string $title, string $active): void
{
    $unread = (int)db()->query('SELECT COUNT(*) FROM enquiries WHERE is_read = 0')->fetchColumn();
    $f = flash();
    $links = ['index' => ['Dashboard', 'index.php'], 'gallery' => ['Gallery & Uploads', 'gallery.php'], 'projects' => ['Projects', 'projects.php'],
              'enquiries' => ['Enquiries', 'enquiries.php'], 'settings' => ['Settings', 'settings.php']];
    ?><!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<meta name="robots" content="noindex,nofollow"><title><?= e($title) ?> · Admin</title>
<link rel="icon" href="<?= asset('img/icon.png') ?>">
<link rel="stylesheet" href="<?= asset('css/admin.css') ?>"></head>
<body><aside class="side"><a class="side__logo" href="<?= url('admin/') ?>"><img src="<?= asset('img/logo.png') ?>" alt="Sparkle Constructions"></a>
<nav><?php foreach ($links as $k => [$l, $h]): ?><a href="<?= url('admin/' . $h) ?>" class="<?= $k === $active ? 'on' : '' ?>"><?= e($l) ?><?= $k === 'enquiries' && $unread ? "<b>$unread</b>" : '' ?></a><?php endforeach; ?></nav>
<div class="side__foot"><a href="<?= url('index.php') ?>" target="_blank">View website ↗</a><a href="<?= url('admin/logout.php') ?>">Sign out</a></div></aside>
<main class="main"><h1><?= e($title) ?></h1>
<?php if ($f): ?><div class="note note--<?= e($f['t']) ?>"><?= e($f['m']) ?></div><?php endif;
}

function admin_foot(): void { echo '</main></body></html>'; }

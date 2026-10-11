<?php
require_once __DIR__ . '/helpers.php';
$title  = isset($title) ? $title . ' · ' . SITE_NAME : SITE_NAME . ' — Building Sustainable Homes for Future Generations';
$desc   = $desc ?? 'Sparkle Constructions builds sustainable, beautifully designed homes in Pandavapura and across Karnataka. Transparent pricing from ₹' . number_format(starting_rate()) . ' per sq.ft.';
$active = $active ?? '';
$nav = [
    'index'    => ['Home', 'index.php'],
    'about'    => ['About', 'about.php'],
    'services' => ['Services', 'services.php'],
    'projects' => ['Projects', 'projects.php'],
    'gallery'  => ['Gallery', 'gallery.php'],
    'pricing'  => ['Pricing', 'pricing.php'],
    'contact'  => ['Contact', 'contact.php'],
];
$flash = flash();
// Top bar shows the last line of the primary address, without the PIN code.
$lines = array_values(array_filter(array_map('trim', explode("\n", setting('address_primary')))));
$place = preg_replace('/\s*[-–]\s*\d{6}\s*$/u', '', end($lines) ?: 'Pandavapura');
?><!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e($title) ?></title>
<meta name="description" content="<?= e($desc) ?>">
<meta name="theme-color" content="#d9b99b">
<meta property="og:title" content="<?= e($title) ?>">
<meta property="og:description" content="<?= e($desc) ?>">
<meta property="og:image" content="<?= e(url('assets/img/hero-1.jpg')) ?>">
<link rel="icon" type="image/png" href="<?= asset('img/icon.png') ?>">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@1,500;1,600&family=Inter:wght@300;400;500;600&family=Outfit:wght@300;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?= asset('css/site.css') ?>">
<script type="application/ld+json"><?= json_encode([
    '@context' => 'https://schema.org', '@type' => 'GeneralContractor', 'name' => SITE_NAME,
    'image' => url('assets/img/hero-1.jpg'), 'telephone' => setting('phone'),
    'address' => ['@type' => 'PostalAddress', 'addressLocality' => 'Pandavapura', 'addressRegion' => 'Karnataka', 'addressCountry' => 'IN'],
], JSON_UNESCAPED_SLASHES) ?></script>
</head>
<body class="<?= e($bodyClass ?? '') ?>">
<a class="skip" href="#main">Skip to content</a>
<script>document.documentElement.classList.add('js')</script>

<div class="topbar">
  <div class="container topbar__in">
    <span class="topbar__l"><?= icon('pin', 15) ?> <?= e($place) ?></span>
    <span class="topbar__r">
      <span class="hide-sm"><?= icon('clock', 15) ?> <?= e(setting('hours')) ?></span>
      <a href="<?= e(phone_href()) ?>"><?= icon('phone', 15) ?> <?= e(setting('phone')) ?></a>
      <a href="mailto:<?= e(setting('email')) ?>" class="hide-sm"><?= icon('mail', 15) ?> <?= e(setting('email')) ?></a>
    </span>
  </div>
</div>

<header class="site-header" id="siteHeader">
  <div class="container nav">
    <a class="brand" href="<?= url('index.php') ?>" aria-label="<?= e(SITE_NAME) ?> home">
      <img src="<?= asset('img/logo.png') ?>" alt="<?= e(SITE_NAME) ?> — Building Sustainable Homes for Future Generations" width="715" height="237">
    </a>
    <nav class="menu" id="menu" aria-label="Primary">
      <?php foreach ($nav as $key => [$label, $href]): ?>
        <a href="<?= url($href) ?>" class="<?= $active === $key ? 'is-active' : '' ?>"><?= e($label) ?></a>
      <?php endforeach; ?>
      <a class="btn btn--orange menu__cta" href="<?= url('pricing.php#estimate') ?>">Get a Quote</a>
      <div class="menu__foot">
        <a href="<?= e(phone_href()) ?>"><span><?= icon('phone', 18) ?></span><b><?= e(setting('phone')) ?></b></a>
        <a href="mailto:<?= e(setting('email')) ?>"><span><?= icon('mail', 18) ?></span><b><?= e(setting('email')) ?></b></a>
        <p><?= icon('pin', 15) ?> <?= e($place) ?> &middot; <?= e(setting('hours')) ?></p>
      </div>
    </nav>
    <button class="burger" id="burger" aria-label="Open menu" aria-expanded="false" aria-controls="menu"><span></span><span></span><span></span></button>
  </div>
</header>
<?php if ($flash): ?>
  <div class="toast toast--<?= e($flash['t']) ?>" role="status"><?= e($flash['m']) ?></div>
<?php endif; ?>
<main id="main">

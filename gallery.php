<?php
$active = 'gallery';
$title = 'Gallery';
$desc = 'Watch real Sparkle Constructions sites progress in order — from site marking and foundation to finishing and handover.';
require __DIR__ . '/includes/header.php';

$pdo = db();
$journey = $pdo->query("SELECT * FROM gallery WHERE category='journey' ORDER BY stage, sort, id")->fetchAll();
$all = $pdo->query("SELECT * FROM gallery WHERE category != 'team' ORDER BY category='journey' DESC, stage, sort, id")->fetchAll();
?>
<section class="page-hero" style="--bg:url('<?= asset('img/hero-2.jpg') ?>')">
  <div class="container" data-w="GALLERY">
    <nav class="crumb" aria-label="Breadcrumb"><a href="<?= url('index.php') ?>">Home</a><i></i><span>Gallery</span></nav>
    <span class="eyebrow eyebrow--light">Gallery</span>
    <h1 data-split="hero">From foundation to <em>finishing.</em></h1>
    <p class="lead">Follow a home’s journey in the order it’s built — then browse our completed homes and designs.</p>
    <ul class="ph__facts"><li><?= icon('pin', 16) ?> Pandavapura, Karnataka</li><li><?= icon('award', 16) ?> Engineer-led sites</li><li><?= icon('rupee', 16) ?> From <?= rupees(starting_rate()) ?>/sq.ft</li></ul>
  </div>
</section>

<section class="sheet section--dark gl-j" id="journey">
  <span class="gl-j__word" aria-hidden="true">BUILD</span>
  <div class="container">
    <header class="gl-head rv">
      <p class="pgk"><i></i>Construction journey<i></i></p>
      <h2>Eight stages. <em>Zero guesswork.</em></h2>
      <p class="gl-head__p">Watch real Sparkle sites progress in the order they&rsquo;re built &mdash; from the first marking on bare ground to the keys in your hand.</p>
      <ul class="gl-stats">
        <li><b><?= count($journey) ?></b><span>site photos</span></li>
        <li><b>08</b><span>build stages</span></li>
        <li><b><?= (int)$pdo->query("SELECT COUNT(*) FROM gallery WHERE category='completed'")->fetchColumn() ?></b><span>finished homes</span></li>
        <li><b><?= (int)$pdo->query("SELECT COUNT(*) FROM gallery WHERE category='designs'")->fetchColumn() ?></b><span>3D designs</span></li>
      </ul>
    </header>

    <?php $firstOf = []; foreach ($journey as $k => $g) if (!isset($firstOf[(int)$g['stage']])) $firstOf[(int)$g['stage']] = $k; ?>
    <div class="gv rv" id="gv">
      <div class="gv__chips" role="tablist" aria-label="Construction stages">
        <?php foreach ($firstOf as $st => $k): ?>
          <button type="button" role="tab" class="gv__chip" data-stage="<?= $st ?>" data-i="<?= $k ?>"><b><?= sprintf('%02d', $st) ?></b><?= e(STAGES[$st] ?? '') ?></button>
        <?php endforeach; ?>
      </div>
      <div class="gv__main">
        <div class="gv__viewer">
          <?php foreach ($journey as $k => $j): ?>
            <figure class="gv__img <?= $k === 0 ? 'is-on' : '' ?>"><img src="<?= gimg($j['file']) ?>" alt="<?= e($j['title']) ?>" <?= $k > 1 ? 'loading="lazy"' : '' ?>></figure>
          <?php endforeach; ?>
          <div class="gv__top"><span class="gv__pill" id="gvPill"></span><span class="gv__cnt"><b id="gvNo">01</b> / <?= sprintf('%02d', count($journey)) ?></span></div>
          <div class="gv__cap"><small>Real site photo</small><b id="gvCap"></b></div>
          <button type="button" class="gv__nav gv__nav--prev" id="gvPrev" aria-label="Previous photo"><?= icon('arrow', 22) ?></button>
          <button type="button" class="gv__nav gv__nav--next" id="gvNext" aria-label="Next photo"><?= icon('arrow', 22) ?></button>
          <button type="button" class="gv__open" id="gvOpen" aria-label="View full size"><?= icon('arrow', 18) ?></button>
        </div>
      </div>
      <div class="gv__strip" id="gvStrip">
        <?php $lastSt = 0; foreach ($journey as $k => $j): $st = (int)$j['stage']; if ($st !== $lastSt): $lastSt = $st; ?>
          <span class="gv__chap"><b><?= sprintf('%02d', $st) ?></b><?= e(STAGES[$st] ?? '') ?></span>
        <?php endif; ?>
          <button type="button" class="gv__th <?= $k === 0 ? 'is-on' : '' ?>" data-i="<?= $k ?>" data-stage="<?= $st ?>" data-cap="<?= e($j['title']) ?>" aria-label="<?= e($j['title']) ?>"><img src="<?= gthumb($j['file']) ?>" alt="" loading="lazy"></button>
        <?php endforeach; ?>
      </div>
      <?php foreach ($journey as $j): ?><a class="gv__hid" href="<?= gimg($j['file']) ?>" data-lb="gv" data-cap="Stage <?= (int)$j['stage'] ?> · <?= e($j['title']) ?>" hidden></a><?php endforeach; ?>
    </div>
  </div>
</section>

<?php $cnt = array_count_values(array_column($all, 'category')); ?>
<section class="sheet gl-all">
  <div class="container">
    <header class="gl-head gl-head--l rv"><p class="pgk"><i></i>The full collection<i></i></p><h2>Every photo, <em>every stage.</em></h2><p class="gl-head__p">Site progress, finished homes and 3D designs &mdash; tap a filter, tap a photo, and see it full size.</p></header>

    <div class="chips glf" data-filter="#galGrid" role="tablist" aria-label="Filter photos">
      <button class="chip is-on" data-f="*">All <sup><?= count($all) ?></sup></button>
      <?php foreach (['journey', 'completed', 'designs'] as $c): if (empty($cnt[$c])) continue; ?>
        <button class="chip" data-f="<?= $c ?>"><?= e(CATEGORIES[$c]) ?> <sup><?= (int)$cnt[$c] ?></sup></button>
      <?php endforeach; ?>
    </div>

    <div class="masonry glm" id="galGrid">
      <?php foreach ($all as $n => $g): ?>
        <a class="mi" style="--i:<?= min($n, 14) ?>" data-cat="<?= e($g['category']) ?>" href="<?= gimg($g['file']) ?>" data-lb="gal" data-cap="<?= e($g['title']) ?>">
          <img src="<?= gthumb($g['file']) ?>" alt="<?= e($g['title']) ?>" loading="lazy" width="<?= (int)$g['w'] ?>" height="<?= (int)$g['h'] ?>">
          <em><?= e(CATEGORIES[$g['category']] ?? '') ?></em>
          <i class="mi__z" aria-hidden="true"><?= icon('arrow', 18) ?></i>
          <span><?= e($g['title']) ?></span>
        </a>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>

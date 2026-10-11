<?php
$active = 'pricing';
$title = 'Pricing & Estimator';
$desc = 'Construction from ₹1,999 per sq.ft. Compare our packages and get an instant indicative estimate for your home.';
require __DIR__ . '/includes/header.php';
$pk = packages();
?>
<section class="page-hero" style="--bg:url('<?= asset('img/hero-1.jpg') ?>')">
  <div class="container" data-w="PRICING">
    <nav class="crumb" aria-label="Breadcrumb"><a href="<?= url('index.php') ?>">Home</a><i></i><span>Pricing</span></nav>
    <span class="eyebrow eyebrow--light">Pricing</span>
    <h1 data-split="hero">Construction from <span class="hl"><?= rupees(starting_rate()) ?></span> per sq.ft.</h1>
    <p class="lead">Clear packages. Itemised quotes. No hidden surprises.</p>
    <ul class="ph__facts"><li><?= icon('pin', 16) ?> Pandavapura, Karnataka</li><li><?= icon('award', 16) ?> Engineer-led sites</li><li><?= icon('rupee', 16) ?> From <?= rupees(starting_rate()) ?>/sq.ft</li></ul>
  </div>
</section>

<section class="sheet pk pg-plans">
  <span class="pz__word" aria-hidden="true">₹</span>
  <div class="container">
    <header class="pz2__head rv">
      <p class="pz__kick"><i></i>Packages<i></i></p>
      <h2>Pick the package that <em>fits your home</em></h2>
      <p>Three clear packages, one honest rate per sq.ft of built-up area.</p>
    </header>
    <?php $pkImg = ['essential' => 'hero-1.jpg', 'classic' => 'hero-3.jpg', 'signature' => 'hero-4.jpg']; $pi = 0; ?>
    <div class="pk__grid">
      <?php foreach ($pk as $key => $p): $pi++; $feat = $key === 'classic'; ?>
        <article class="pk__card rv <?= $feat ? 'is-feat' : '' ?>">
          <?php if ($feat): ?><span class="pk__flag"><?= icon('award', 14) ?> Most chosen</span><?php endif; ?>
          <div class="pk__ph">
            <img src="<?= asset('img/' . ($pkImg[$key] ?? 'hero-3.jpg')) ?>" alt="<?= e($p['name']) ?> package home" loading="lazy">
            <span class="pk__no">0<?= $pi ?></span>
            <div class="pk__nm"><small><?= e($p['tag']) ?></small><h3><?= e($p['name']) ?></h3></div>
          </div>
          <div class="pk__body">
            <div class="pk__price"><span class="pk__from"><?= $key === 'essential' ? 'From' : 'Rate' ?></span><b><?= rupees($p['rate']) ?></b><span>/ sq.ft</span></div>
            <ul class="pk__list"><?php foreach ($p['items'] as $it): ?><li><i></i><?= e($it) ?></li><?php endforeach; ?></ul>
            <a class="pk__cta" href="#estimate" data-pick="<?= e($key) ?>"><span>Estimate with <?= e($p['name']) ?></span><em><?= icon('arrow', 18) ?></em></a>
          </div>
        </article>
      <?php endforeach; ?>
    </div>
    <p class="pg-fine rv">Rates are per sq.ft of built-up area and indicative. Your final, itemised quote is prepared after a site visit and approved drawings.</p>
  </div>
</section>

<section class="sheet pg-est" id="estimate">
  <div class="container">
    <header class="pz2__head rv"><p class="pz__kick"><i></i>Instant estimator<i></i></p><h2>What could your <em>home cost?</em></h2><p>Slide, tap, and see an indicative figure instantly &mdash; then ask for a detailed quote.</p></header>
    <div class="est reveal" data-rates='<?= e(json_encode(array_map(fn($p) => ['n' => $p['name'], 'r' => $p['rate']], $pk))) ?>'>
      <div class="est__controls">
        <label>Built-up area per floor (sq.ft)
          <input type="range" id="area" min="400" max="4000" step="50" value="1200">
          <output id="areaOut">1,200 sq.ft</output>
        </label>
        <label>Number of floors
          <div class="seg" id="floors">
            <?php foreach ([1, 2, 3] as $f): ?><button type="button" data-v="<?= $f ?>" class="<?= $f === 1 ? 'is-on' : '' ?>"><?= $f === 1 ? 'Ground' : ($f === 2 ? 'G + 1' : 'G + 2') ?></button><?php endforeach; ?>
          </div>
        </label>
        <label>Package
          <div class="seg" id="pkg">
            <?php foreach ($pk as $key => $p): ?><button type="button" data-v="<?= e($key) ?>" class="<?= $key === 'classic' ? 'is-on' : '' ?>"><?= e($p['name']) ?></button><?php endforeach; ?>
          </div>
        </label>
      </div>
      <div class="est__result">
        <span>Indicative construction cost</span>
        <strong id="total">₹0</strong>
        <small id="detail"></small>
        <form method="post" action="<?= url('contact.php') ?>" class="est__form">
          <?= csrf_field() ?>
          <input type="hidden" name="source" value="estimate">
          <input type="hidden" name="service" value="New home construction">
          <input type="hidden" name="message" id="estMsg">
          <input class="hp" type="text" name="website" tabindex="-1" autocomplete="off" aria-hidden="true">
          <input name="name" placeholder="Your name" required autocomplete="name">
          <input name="phone" type="tel" placeholder="Phone number" required autocomplete="tel">
          <button class="btn btn--accent" type="submit">Send me a detailed quote</button>
        </form>
      </div>
    </div>
  </div>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>

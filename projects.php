<?php
$active = 'projects';
$title = 'Projects';
$desc = 'Completed homes and 3D designs by Sparkle Constructions.';
require __DIR__ . '/includes/header.php';

$rows = db()->query("SELECT p.*, g.file, g.category FROM projects p JOIN gallery g ON g.id = p.gallery_id ORDER BY p.featured DESC, p.sort, p.id")->fetchAll();
?>
<section class="page-hero" style="--bg:url('<?= asset('img/hero-1.jpg') ?>')">
  <div class="container" data-w="PROJECTS">
    <nav class="crumb" aria-label="Breadcrumb"><a href="<?= url('index.php') ?>">Home</a><i></i><span>Projects</span></nav>
    <span class="eyebrow eyebrow--light">Projects</span>
    <h1 data-split="hero">Homes we’ve built. <em>Homes we’ve imagined.</em></h1>
    <p class="lead">A selection of completed residences and 3D design concepts from our studio.</p>
    <ul class="ph__facts"><li><?= icon('pin', 16) ?> Pandavapura, Karnataka</li><li><?= icon('award', 16) ?> Engineer-led sites</li><li><?= icon('rupee', 16) ?> From <?= rupees(starting_rate()) ?>/sq.ft</li></ul>
  </div>
</section>

<?php
  $feat = array_values(array_filter($rows, fn($r) => (int)$r['featured'] === 1));
  $cnt = array_count_values(array_column($rows, 'category'));
  $typeLabel = ['completed' => 'Completed home', 'designs' => '3D design concept'];
?>
<?php if ($feat): ?>
<!-- ============ FEATURED SHOWCASE ============ -->
<section class="sheet pjs" id="featured">
  <span class="pjs__word" aria-hidden="true">FEATURED</span>
  <div class="container">
    <header class="gl-head rv">
      <p class="pgk"><i></i>Featured projects<i></i></p>
      <h2>Selected work, <em>presented properly.</em></h2>
      <p class="gl-head__p">A closer look at some of our best-loved homes and design concepts.</p>
    </header>
    <div class="pjs__grid rv" id="pjs">
      <div class="pjs__stage">
        <?php foreach ($feat as $i => $p): ?>
          <figure class="pjs__img <?= $i === 0 ? 'is-on' : '' ?>"><img src="<?= gimg($p['file']) ?>" alt="<?= e($p['title']) ?>" <?= $i > 0 ? 'loading="lazy"' : '' ?>></figure>
        <?php endforeach; ?>
        <span class="pjs__badge"><?= icon('award', 14) ?> Featured</span>
        <span class="pjs__cnt"><b id="pjsNo">01</b> / <?= sprintf('%02d', count($feat)) ?></span>
        <button type="button" class="pjs__zoom" id="pjsZoom" aria-label="View full size"><?= icon('arrow', 18) ?></button>
      </div>
      <div class="pjs__side">
        <?php foreach ($feat as $i => $p): ?>
          <article class="pjs__info <?= $i === 0 ? 'is-on' : '' ?>">
            <span class="pjs__type"><?= e($typeLabel[$p['category']] ?? $p['type']) ?></span>
            <h3><?= e($p['title']) ?></h3>
            <p><?= e($p['summary']) ?></p>
            <dl class="pjs__spec">
              <div><dt>Category</dt><dd><?= e($p['type']) ?></dd></div>
              <div><dt>Delivered by</dt><dd>Sparkle Constructions</dd></div>
              <div><dt>Approach</dt><dd><?= $p['category'] === 'designs' ? 'Designed to be built sustainably' : 'Engineer-supervised at every stage' ?></dd></div>
            </dl>
            <div class="pjs__act">
              <a class="btn btn--lg" href="<?= url('contact.php?service=' . rawurlencode($p['category'] === 'designs' ? 'Architectural & 3D design' : 'New home construction')) ?>">Build something similar <?= icon('arrow', 18) ?></a>
            </div>
          </article>
        <?php endforeach; ?>
        <ol class="pjs__list" role="tablist" aria-label="Featured projects">
          <?php foreach ($feat as $i => $p): ?>
            <li><button type="button" class="<?= $i === 0 ? 'is-on' : '' ?>" data-i="<?= $i ?>"><b><?= sprintf('%02d', $i + 1) ?></b><span><?= e($p['title']) ?></span></button></li>
          <?php endforeach; ?>
        </ol>
      </div>
    </div>
    <?php foreach ($feat as $p): ?><a class="pjs__hid" href="<?= gimg($p['file']) ?>" data-lb="pjs" data-cap="<?= e($p['title']) ?>" hidden></a><?php endforeach; ?>
  </div>
</section>
<script>
(function(){
  var root=document.getElementById('pjs'); if(!root) return;
  var imgs=[].slice.call(root.querySelectorAll('.pjs__img')), infos=[].slice.call(root.querySelectorAll('.pjs__info')),
      btns=[].slice.call(root.querySelectorAll('.pjs__list button')), hid=[].slice.call(document.querySelectorAll('.pjs__hid')),
      no=document.getElementById('pjsNo'), i=0, timer;
  function set(n){ i=(n+imgs.length)%imgs.length;
    imgs.forEach(function(e,k){e.classList.toggle('is-on',k===i)});
    infos.forEach(function(e,k){e.classList.toggle('is-on',k===i)});
    btns.forEach(function(e,k){e.classList.toggle('is-on',k===i)});
    no.textContent=('0'+(i+1)).slice(-2); }
  function auto(){ clearInterval(timer); timer=setInterval(function(){set(i+1)},6500); }
  btns.forEach(function(b,k){b.addEventListener('click',function(){set(k);auto();})});
  document.getElementById('pjsZoom').addEventListener('click',function(){ if(hid[i]) hid[i].click(); });
  root.querySelector('.pjs__stage').addEventListener('click',function(e){ if(!e.target.closest('button') && hid[i]) hid[i].click(); });
  root.addEventListener('mouseenter',function(){clearInterval(timer)}); root.addEventListener('mouseleave',auto);
  auto();
})();
</script>
<?php endif; ?>

<!-- ============ ALL PROJECTS ============ -->
<section class="sheet pj" id="all">
  <div class="container">
    <header class="gl-head gl-head--l rv">
      <p class="pgk"><i></i>The portfolio<i></i></p>
      <h2>All <em>projects</em></h2>
      <p class="gl-head__p">Completed residences and 3D design concepts &mdash; tap any project to see it full size.</p>
    </header>
    <div class="chips glf" role="tablist" data-filter="#projGrid">
      <button class="chip is-on" data-f="*">All <sup><?= count($rows) ?></sup></button>
      <?php if (!empty($cnt['completed'])): ?><button class="chip" data-f="completed">Completed Homes <sup><?= (int)$cnt['completed'] ?></sup></button><?php endif; ?>
      <?php if (!empty($cnt['designs'])): ?><button class="chip" data-f="designs">3D Designs <sup><?= (int)$cnt['designs'] ?></sup></button><?php endif; ?>
    </div>
    <div class="pl-grid" id="projGrid">
      <?php foreach ($rows as $n => $p): ?>
        <a class="pl" style="--i:<?= min($n, 12) ?>" data-cat="<?= e($p['category']) ?>" href="<?= gimg($p['file']) ?>" data-lb="proj" data-cap="<?= e($p['title']) ?>">
          <figure class="pl__ph">
            <img src="<?= gthumb($p['file']) ?>" alt="<?= e($p['title']) ?>" loading="lazy">
            <span class="pl__type"><?= e($typeLabel[$p['category']] ?? $p['type']) ?></span>
            <?php if ((int)$p['featured'] === 1): ?><span class="pl__star" title="Featured"><?= icon('award', 14) ?></span><?php endif; ?>
          </figure>
          <div class="pl__body">
            <div><small><?= sprintf('%02d', $n + 1) ?></small><h3><?= e($p['title']) ?></h3></div>
            <span class="pl__go"><?= icon('arrow', 18) ?></span>
          </div>
        </a>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>

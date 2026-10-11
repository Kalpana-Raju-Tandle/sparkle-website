<?php
$active = 'about';
$title = 'About Us';
$desc = 'Meet the people behind Sparkle Constructions — our founder, our on-site engineers and our designer.';
require __DIR__ . '/includes/header.php';

$team = db()->query("SELECT file FROM gallery WHERE category='team' ORDER BY sort LIMIT 1")->fetchColumn();
function initials(string $n): string
{
    $w = preg_split('/\s+/', trim($n));
    return mb_strtoupper(mb_substr($w[0], 0, 1) . (count($w) > 1 ? mb_substr(end($w), 0, 1) : ''));
}
?>

<?php
  $nHomes   = (int)db()->query("SELECT COUNT(*) FROM gallery WHERE category='completed'")->fetchColumn();
  $nDesigns = (int)db()->query("SELECT COUNT(*) FROM gallery WHERE category='designs'")->fetchColumn();
?>
<section class="page-hero ab-hero" style="--bg:url('<?= asset('img/hero-3.jpg') ?>')">
  <div class="container ab-hero__in" data-w="ABOUT">
    <div class="ab-hero__txt">
      <nav class="crumb" aria-label="Breadcrumb"><a href="<?= url('index.php') ?>">Home</a><i></i><span>About</span></nav>
      <h1 data-split="hero">Sustainable homes, built on <em>trust.</em></h1>
      <p class="lead">We design and build homes the way we&rsquo;d build our own &mdash; durable, efficient and honest from the first meeting.</p>
      <div class="ab-hero__act">
        <a class="btn btn--lg" href="<?= url('contact.php') ?>">Talk to us <?= icon('arrow', 18) ?></a>
        <a class="ab-hero__link" href="<?= url('projects.php') ?>">See our work <?= icon('arrow', 16) ?></a>
      </div>
    </div>
    <div class="ab-hero__vis" aria-hidden="true">
      <figure class="ab-hero__arch"><img src="<?= asset('img/hero-3.jpg') ?>" alt=""></figure>
      <?php if ($team): ?><figure class="ab-hero__team"><img src="<?= gimg($team) ?>" alt=""></figure><?php endif; ?>
      <div class="ab-hero__badge"><span><?= icon('shield', 20) ?></span><div><b>Engineer-led</b><small>every site, every stage</small></div></div>
      <i class="ab-hero__ring"></i>
    </div>
    <ul class="ab-hero__stats">
      <li><b><?= $nHomes ?></b><span>Completed homes</span></li>
      <li><b><?= $nDesigns ?></b><span>3D designs</span></li>
      <li><b>08</b><span>Inspected stages</span></li>
      <li><b><?= rupees(starting_rate()) ?></b><span>Starting / sq.ft</span></li>
    </ul>
  </div>
</section>

<?php $works = db()->query("SELECT file, title FROM gallery WHERE category='completed' ORDER BY sort, id LIMIT 14")->fetchAll(); ?>
<!-- ============ OUR STORY ============ -->
<section class="sheet ab ab--story">
  <div class="container ab__split">
    <div class="ab__vis rv">
      <?php if (isset($works[0])): ?><figure class="ab__arch"><img src="<?= gimg($works[0]['file']) ?>" alt="<?= e($works[0]['title']) ?>" loading="lazy"></figure><?php endif; ?>
      <?php if (isset($works[2])): ?><figure class="ab__card"><img src="<?= gimg($works[2]['file']) ?>" alt="<?= e($works[2]['title']) ?>" loading="lazy"></figure><?php endif; ?>
      <div class="ab__chip"><b><?= $nHomes ?>+</b><span>homes<br>delivered</span></div>
      <i class="ab__orbit" aria-hidden="true"></i>
    </div>
    <div class="ab__copy rv">
      <p class="pgk"><i></i>Our story</p>
      <h2>Building sustainable homes for <em>future generations</em></h2>
      <p>Sparkle Constructions began with a simple frustration: too many families lose money, time and peace of mind to builders who over-promise and under-deliver. We set out to do the opposite &mdash; fixed pricing, qualified supervision and proof of progress at every stage.</p>
      <p>&ldquo;Sustainable&rdquo; for us means homes that use materials wisely, stay cool and bright through good design, and stand strong for decades so the next generation inherits a home, not a repair bill.</p>
      <ul class="ab__pills"><li><?= icon('check', 14) ?> Fixed pricing</li><li><?= icon('check', 14) ?> Qualified supervision</li><li><?= icon('check', 14) ?> Proof at every stage</li></ul>
      <a class="btn btn--lg" href="<?= url('projects.php') ?>">See our projects <?= icon('arrow', 18) ?></a>
    </div>
  </div>
</section>

<!-- ============ VALUES (dark band) ============ -->
<section class="sheet ab ab--vals">
  <span class="ab__word" aria-hidden="true">VALUES</span>
  <div class="container">
    <header class="ab__head rv"><p class="pgk"><i></i>What we stand for<i></i></p><h2>Four values behind <em>every home</em></h2></header>
    <div class="ab__vgrid">
      <?php $vi = 0; foreach ([['leaf', 'Sustainable', 'Smart design, responsible material use, low-maintenance finishes.'],
                      ['camera', 'Transparent', 'Itemised quotes and photo updates at every stage.'],
                      ['shield', 'Dependable', 'Engineer-supervised work to approved drawings.'],
                      ['users', 'Personal', 'Direct access to the people building your home.']] as [$i, $t, $d]): $vi++; ?>
        <article class="ab__v rv"><b><?= sprintf('%02d', $vi) ?></b><span><?= icon($i, 28) ?></span><h3><?= e($t) ?></h3><p><?= e($d) ?></p></article>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- ============ HOW WE WORK ============ -->
<section class="sheet ab ab--how">
  <div class="container">
    <header class="ab__head rv"><p class="pgk"><i></i>How we work<i></i></p><h2>From first call to <em>keys in hand</em></h2><p class="ab__sub">Four clear steps, the same way every time &mdash; so you always know what happens next.</p></header>
    <ol class="ab__how">
      <?php foreach ([['pen','Plan','We listen, visit your plot and turn your ideas into a design you can walk through before building.'],
                      ['rupee','Price','An itemised written quote with fixed rates &mdash; you see exactly what you pay for, before work begins.'],
                      ['tool','Build','Qualified engineers supervise every stage, with photo updates and sign-off before the next one starts.'],
                      ['key','Handover','A final inspection, a clean home and the keys &mdash; with support for your griha pravesh.']] as $n => [$ic, $t, $d]): ?>
        <li class="ab__step rv"><b><?= sprintf('%02d', $n + 1) ?></b><span><?= icon($ic, 26) ?></span><h3><?= $t ?></h3><p><?= $d ?></p></li>
      <?php endforeach; ?>
    </ol>
  </div>
</section>

<!-- ============ RECENT WORK STRIP ============ -->
<?php if ($works): ?>
<section class="sheet ab ab--work">
  <div class="container">
    <header class="ab__head rv"><p class="pgk"><i></i>Recent work<i></i></p><h2>Homes we&rsquo;ve <em>delivered</em></h2><p class="ab__sub">A few of the residences our team has built &mdash; each one supervised from the first marking to the last coat of paint.</p></header>
  </div>
  <div class="ab__strip" aria-hidden="true"><div class="ab__rail">
    <?php for ($r = 0; $r < 2; $r++): foreach ($works as $w): ?><figure><img src="<?= gthumb($w['file']) ?>" alt="" loading="lazy"><figcaption><?= e($w['title']) ?></figcaption></figure><?php endforeach; endfor; ?>
  </div></div>
  <p class="ab__more rv"><a class="btn btn--lg" href="<?= url('projects.php') ?>">View all projects <?= icon('arrow', 18) ?></a></p>
</section>
<?php endif; ?>

<!-- ============ FOUNDER (above the "unqualified contractors" section) ============ -->
<section class="sheet ab ab--founder" id="profile">
  <span class="ab__word" aria-hidden="true">FOUNDER</span>
  <div class="container">
    <header class="ab__head rv"><p class="pgk"><i></i>Leadership<i></i></p><h2>Meet the <em>founder</em></h2></header>
    <div class="ab__prof rv">
      <figure class="ab__ph"><span><?= e(initials(setting('client_name'))) ?></span><i class="ab__ring" aria-hidden="true"></i></figure>
      <div class="ab__pb">
        <span class="ab__q" aria-hidden="true">&ldquo;</span>
        <p class="ab__role"><?= e(setting('client_role')) ?></p>
        <h3><?= e(setting('client_name')) ?></h3>
        <div class="ab__bio"><?= nl2p(setting('client_bio')) ?></div>
        <a class="btn btn--lg" href="<?= url('contact.php') ?>">Talk to us directly <?= icon('arrow', 18) ?></a>
      </div>
    </div>
  </div>
</section>

<!-- ============ WHY NOT UNQUALIFIED CONTRACTORS ============ -->
<section class="sheet ab ab--cmp" id="why">
  <div class="container">
    <header class="ab__head rv">
      <p class="pgk"><i></i>Choose carefully<i></i></p>
      <h2>Why unqualified contractors <em>cost more</em> in the end</h2>
      <p class="ab__sub">A low quote from an unqualified contractor is rarely low for long. Here&rsquo;s what the difference really looks like.</p>
    </header>
    <div class="ab__cmp">
      <article class="ab__c ab__c--bad rv">
        <h3><span><?= icon('shield', 20) ?></span>Unqualified contractors</h3>
        <ul>
          <li><i>&times;</i>Vague, verbal quotes that grow mid-project</li>
          <li><i>&times;</i>No engineer on site &mdash; work done by guesswork</li>
          <li><i>&times;</i>Wrong steel or concrete ratios to &ldquo;save&rdquo; cost</li>
          <li><i>&times;</i>Delays, abandoned work and no accountability</li>
          <li><i>&times;</i>Cracks, leakage and repair bills within years</li>
        </ul>
      </article>
      <span class="ab__vs" aria-hidden="true">VS</span>
      <article class="ab__c ab__c--good rv">
        <h3><span><?= icon('award', 20) ?></span>Sparkle Constructions</h3>
        <ul>
          <li><i><?= icon('check', 13) ?></i>Itemised written quote, starting <?= rupees(starting_rate()) ?>/sq.ft</li>
          <li><i><?= icon('check', 13) ?></i>Qualified site engineers supervising daily</li>
          <li><i><?= icon('check', 13) ?></i>Materials &amp; mixes checked against drawings</li>
          <li><i><?= icon('check', 13) ?></i>Stage-wise schedule with photo proof</li>
          <li><i><?= icon('check', 13) ?></i>Built to last &mdash; with a clear point of contact</li>
        </ul>
      </article>
    </div>
  </div>
</section>

<!-- ============ ON-SITE ENGINEERING + DESIGNER ============ -->
<section class="sheet ab ab--eng" id="engineering">
  <span class="ab__word" aria-hidden="true">BUILT</span>
  <div class="container">
    <header class="ab__head ab__head--l rv">
      <p class="pgk"><i></i>On-site engineering<i></i></p>
      <h2>Engineers on your site. <em>A designer on your side.</em></h2>
      <p class="ab__sub">Every Sparkle home is checked where it matters most &mdash; on site, at every stage &mdash; and designed by someone who understands how it will actually be built.</p>
    </header>
    <div class="ab__eng">
      <div class="ab__team rv">
        <?php if ($team): ?><figure><img src="<?= gimg($team) ?>" alt="The Sparkle Constructions site team" loading="lazy"><figcaption><small>On site, every day</small><b>The Sparkle team</b></figcaption></figure><?php endif; ?>
        <ul>
          <li><span><?= icon('check', 14) ?></span>Daily site supervision by qualified engineers</li>
          <li><span><?= icon('check', 14) ?></span>Pre-pour checks on steel, shuttering and levels</li>
          <li><span><?= icon('check', 14) ?></span>Material testing and quality records</li>
          <li><span><?= icon('check', 14) ?></span>Stage-wise sign-off before the next stage begins</li>
        </ul>
      </div>
      <article class="ab__des rv">
        <figure class="ab__ph ab__ph--g"><span><?= e(initials(setting('designer_name'))) ?></span><i class="ab__ring" aria-hidden="true"></i></figure>
        <p class="ab__role ab__role--l">Our designer</p>
        <h3><?= e(setting('designer_name')) ?></h3>
        <p class="ab__rl"><?= e(setting('designer_role')) ?></p>
        <div class="ab__bio ab__bio--l"><?= nl2p(setting('designer_bio')) ?></div>
        <a class="btn btn--lg" href="<?= url('projects.php') ?>">See the designs <?= icon('arrow', 18) ?></a>
      </article>
    </div>
  </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>

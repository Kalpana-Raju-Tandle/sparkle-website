<?php
$active = 'index';
$bodyClass = 'home';
require __DIR__ . '/includes/header.php';

$pdo = db();
$featured = $pdo->query("SELECT p.*, g.file FROM projects p LEFT JOIN gallery g ON g.id = p.gallery_id WHERE p.featured = 1 ORDER BY p.sort LIMIT 5")->fetchAll();
$counts = [
    'completed' => (int)$pdo->query("SELECT COUNT(*) FROM gallery WHERE category='completed'")->fetchColumn(),
    'designs'   => (int)$pdo->query("SELECT COUNT(*) FROM gallery WHERE category='designs'")->fetchColumn(),
];
$stageImgs = $pdo->query("SELECT * FROM gallery WHERE id IN (SELECT MIN(id) FROM gallery WHERE category='journey' GROUP BY stage) ORDER BY stage")->fetchAll();
?>

<!-- ============ HERO ============ -->
<section class="hero" id="hero">
  <div class="hero__slides" aria-hidden="true">
    <?php foreach (['hero-1', 'hero-2', 'hero-3', 'hero-4'] as $i => $h): ?>
      <div class="hero__slide <?= $i === 0 ? 'is-on' : '' ?>" style="background-image:url('<?= asset("img/$h.jpg") ?>')"></div>
    <?php endforeach; ?>
  </div>
  <div class="hero__shade"></div>
  <div class="hero__frame" aria-hidden="true"></div>

  <div class="container hero__main">
    <div class="hero__copy">
      <p class="hero__kicker"><span></span>Pandavapura · Karnataka</p>
      <h1><span class="h1-a">Building</span> <em>Sustainable</em><br><span class="h1-b">Homes for Future Generations</span></h1>
      <p class="hero__lead">Thoughtfully designed, honestly priced and engineered on site — from the first line on your plot to the day you light the lamp at griha pravesh.</p>
      <div class="hero__btns">
        <a class="btn btn--gold btn--lg" href="<?= url('pricing.php#estimate') ?>">Estimate My Home <?= icon('arrow', 18) ?></a>
        <a class="btn btn--glass btn--lg" href="<?= url('projects.php') ?>">View Our Projects</a>
      </div>
    </div>

    <ol class="hero__dots" id="heroDots" aria-label="Hero slides">
      <?php foreach ([1, 2, 3, 4] as $n): ?>
        <li><button type="button" class="<?= $n === 1 ? 'is-on' : '' ?>" data-i="<?= $n - 1 ?>" aria-label="Slide <?= $n ?>"><b>0<?= $n ?></b><i></i></button></li>
      <?php endforeach; ?>
    </ol>
  </div>

  <div class="container hero__glass">
    <div class="gstat"><span>Construction from</span><strong><?= rupees(starting_rate()) ?><small>/sq.ft</small></strong></div>
    <div class="gstat"><?= icon('shield', 26) ?><div><b>Engineer-supervised</b><span>Every pour, every wall</span></div></div>
    <div class="gstat"><?= icon('rupee', 26) ?><div><b>Fixed, itemised pricing</b><span>No hidden extras</span></div></div>
    <div class="gstat"><?= icon('camera', 26) ?><div><b>Stage-wise updates</b><span>Photo progress reports</span></div></div>
  </div>
</section>

<!-- Quick enquiry bar -->
<div class="container">
  <form class="quick" method="post" action="<?= url('contact.php') ?>">
    <?= csrf_field() ?>
    <input type="hidden" name="source" value="contact">
    <input class="hp" type="text" name="website" tabindex="-1" autocomplete="off" aria-hidden="true">
    <div class="quick__title"><b>Get a free consultation</b><span>We’ll call you back shortly</span></div>
    <input name="name" placeholder="Your name" required autocomplete="name" aria-label="Your name">
    <input name="phone" type="tel" placeholder="Phone number" required autocomplete="tel" aria-label="Phone number">
    <select name="service" aria-label="Service">
      <option value="New home construction">New home construction</option>
      <option value="Architectural & 3D design">Architectural &amp; 3D design</option>
      <option value="Renovation / extension">Renovation / extension</option>
      <option value="Interiors & woodwork">Interiors &amp; woodwork</option>
    </select>
    <button class="btn btn--gold" type="submit">Request a call</button>
  </form>
</div>

<!-- ============ TRUST FEATURES ============ -->
<section class="features">
  <div class="container features__grid">
    <?php foreach ([['shield', 'Quality you can see', 'Tested materials and engineer sign-off at every stage.'],
                    ['rupee', 'Transparent pricing', 'Itemised quotes from ' . rupees(starting_rate()) . '/sq.ft. No hidden extras.'],
                    ['clock', 'On-time delivery', 'A clear stage-wise schedule that we stick to.'],
                    ['camera', 'Photo progress updates', 'Watch your home rise — stage by stage.']] as $i => [$ic, $t, $d]): ?>
      <div class="feat rv"><span class="feat__ic"><?= icon($ic, 28) ?></span><div><h3><?= e($t) ?></h3><p><?= e($d) ?></p></div></div>
    <?php endforeach; ?>
  </div>
</section>

<!-- ============ ABOUT ============ -->
<section class="section about-lux">
  <span class="about-lux__word" aria-hidden="true">Sparkle</span>
  <div class="container about">
    <div class="about__media rv">
      <div class="about__frame" aria-hidden="true"></div>
      <div class="arch"><img src="<?= asset('img/hero-2.jpg') ?>" alt="Contemporary home by Sparkle Constructions" loading="lazy"></div>
      <img class="about__b" src="<?= asset('img/hero-4.jpg') ?>" alt="Home elevation render" loading="lazy">
      <div class="seal" aria-hidden="true">
        <svg viewBox="0 0 150 150"><defs><path id="circ" d="M75,75 m-56,0 a56,56 0 1,1 112,0 a56,56 0 1,1 -112,0"/></defs><text><textPath href="#circ" textLength="344" lengthAdjust="spacing">Sparkle Constructions · Sparkle Constructions ·</textPath></text></svg>
        <img src="<?= asset('img/icon.png') ?>" alt="">
      </div>
      <div class="about__badge"><strong><?= $counts['completed'] + $counts['designs'] ?>+</strong><span>homes &amp; designs<br>in our portfolio</span></div>
    </div>

    <div class="about__copy rv">
      <p class="kicker"><span></span>About Sparkle Constructions</p>
      <h2>Homes that last <em>longer</em> than the loan you take for them</h2>
      <p class="about__lead">We are a design-and-build company focused on sustainable, durable homes. We handle every stage ourselves — design, structure and finishing — so there are no hand-offs, no blame games and no surprises on your bill.</p>

      <div class="afeat">
        <div><span><?= icon('users', 22) ?></span><h3>Engineer-led</h3><p>Qualified engineers supervise every pour and every wall.</p></div>
        <div><span><?= icon('rupee', 22) ?></span><h3>Fixed pricing</h3><p>Itemised quotes, starting <?= rupees(starting_rate()) ?>/sq.ft.</p></div>
        <div><span><?= icon('leaf', 22) ?></span><h3>Sustainable</h3><p>Smart design, durable materials and low-upkeep finishes.</p></div>
        <div><span><?= icon('key', 22) ?></span><h3>One team</h3><p>A single accountable team from drawing to handover.</p></div>
      </div>

      <div class="about__cta">
        <a class="btn btn--maroon btn--lg" href="<?= url('about.php') ?>">Discover our story <?= icon('arrow', 18) ?></a>
        <a class="callus" href="<?= e(phone_href()) ?>"><span><?= icon('phone', 22) ?></span><div><small>Speak to us</small><b><?= e(setting('phone')) ?></b></div></a>
      </div>
    </div>
  </div>
</section>

<!-- ============ SERVICES ============ -->
<section class="sv" id="services-home">
  <div class="container">
    <header class="sv__head rv">
      <div>
        <p class="kicker kicker--light"><span></span>What we do</p>
        <h2>Six services. <em>One</em> home, built layer by layer.</h2>
      </div>
      <div class="sv__headr">
        <p>From the first drawing to the day you get the keys — every layer handled by one accountable team.</p>
        <a class="sv__all" href="<?= url('services.php') ?>">All services <span><?= icon('arrow', 18) ?></span></a>
      </div>
    </header>

    <?php $svcs = [
      ['pen', 'Architectural & 3D Design', 'Floor plans and photo-real elevations so you see your home before we build it.', 'svc-2', ['Custom, vastu-friendly floor plans', 'Photo-real 3D elevations', 'Revisions until you’re delighted']],
      ['layers', 'Structural Engineering', 'Safe foundations, correct steel and sound concrete, certified by site engineers.', 'svc-3', ['Footing & column design', 'Steel and concrete quality control', 'Engineer sign-off at each stage']],
      ['home', 'Residential Construction', 'Independent houses, duplexes and triplexes built to last with strict stage-wise quality checks.', 'svc-1', ['Foundation to terrace, one team', 'Stage-wise schedule', 'Photo progress updates']],
      ['grid', 'Interiors & Woodwork', 'Doors, windows, cladding and finishing details that make a house feel like home.', 'svc-4', ['Wooden doors & frames', 'Cladding & elevation features', 'Lighting and false ceilings']],
      ['tool', 'Renovation & Extension', 'Add a floor, reshape a facade or give an old home a new life.', 'svc-5', ['Structural assessment first', 'Extra floors & facade makeovers', 'Waterproofing and repairs']],
      ['key', 'Turnkey Delivery', 'You choose the vision — we handle approvals, build and hand over the keys.', 'svc-6', ['Single point of contact', 'Coordinated trades & vendors', 'Handover with griha pravesh support']],
    ]; $n = count($svcs); ?>
    <div class="sx rv" id="sx">
      <div class="sx__stage" aria-hidden="true">
        <div class="sx__grid"></div>
        <i class="sx__tick sx__tick--tl"></i><i class="sx__tick sx__tick--br"></i>
        <div class="sx__axis"><span>Handover</span><b></b><span>Plan</span></div>
        <div class="sx__stack">
          <?php foreach ($svcs as $i => [$ic, $t, $d, $im]): ?>
            <div class="sx__slab <?= $i === 0 ? 'is-on' : '' ?>" data-i="<?= $i ?>" style="--i:<?= $i ?>">
              <img src="<?= asset("img/$im.jpg") ?>" alt="">
              <em><?= sprintf('%02d', $i + 1) ?></em>
            </div>
          <?php endforeach; ?>
        </div>
      </div>

      <div class="sx__panel">
        <?php foreach ($svcs as $i => [$ic, $t, $d, $im, $pts]): ?>
          <div class="sx__info <?= $i === 0 ? 'is-on' : '' ?>" data-i="<?= $i ?>">
            <div class="sx__meta"><span class="sx__chip"><?= icon($ic, 24) ?></span><span class="sx__no"><b><?= sprintf('%02d', $i + 1) ?></b> / <?= sprintf('%02d', $n) ?></span></div>
            <div class="sx__mimg"><img src="<?= asset("img/$im.jpg") ?>" alt="" loading="lazy"></div>
            <h3><?= e($t) ?></h3>
            <p><?= e($d) ?></p>
            <ul><?php foreach ($pts as $pt): ?><li><?= icon('check', 16) ?> <?= e($pt) ?></li><?php endforeach; ?></ul>
            <a class="sx__go" href="<?= url('services.php') ?>">Explore service <i><?= icon('arrow', 16) ?></i></a>
          </div>
        <?php endforeach; ?>

        <div class="sx__tabs" role="tablist" aria-label="Services">
          <?php foreach ($svcs as $i => [$ic, $t]): ?>
            <button type="button" role="tab" class="<?= $i === 0 ? 'is-on' : '' ?>" data-i="<?= $i ?>" aria-label="<?= e($t) ?>"><?= icon($ic, 18) ?><b><?= sprintf('%02d', $i + 1) ?></b></button>
          <?php endforeach; ?>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- ============ STATS ============ -->
<section class="stats">
  <div class="container">
    <div class="sbar rv">
      <div class="sbar__i"><span class="sbar__ic"><?= icon('home', 22) ?></span><div><b data-count="<?= $counts['completed'] ?>">0</b><span>Completed homes</span></div></div>
      <div class="sbar__i"><span class="sbar__ic"><?= icon('pen', 22) ?></span><div><b data-count="<?= $counts['designs'] ?>">0</b><span>3D elevations designed</span></div></div>
      <div class="sbar__i"><span class="sbar__ic"><?= icon('layers', 22) ?></span><div><b data-count="<?= count(STAGES) ?>" data-plain="1">0</b><span>Photographed build stages</span></div></div>
      <div class="sbar__i"><span class="sbar__ic"><?= icon('rupee', 22) ?></span><div><b data-count="<?= starting_rate() ?>" data-prefix="₹">0</b><span>Starting per sq.ft</span></div></div>
    </div>
  </div>
</section>

<!-- ============ SUSTAINABLE BY DESIGN ============ -->
<section class="sus" id="sustainable">
  <div class="container">
    <header class="sus__head sus__head--m rv">
      <div class="sus__titlewrap">
        <span class="sus__pill"><?= icon('leaf', 16) ?> Sustainable by design</span>
        <h2><span class="l1">Homes that live <mark>greener</mark></span><span class="l2">and last longer</span></h2>
      </div>
      <div class="sus__side">
        <div class="sus__orbs" aria-hidden="true"><span><?= icon('leaf', 22) ?></span><span><?= icon('sun', 22) ?></span><span><?= icon('drop', 22) ?></span></div>
        <p>Every Sparkle design is shaped around light, air, greenery and materials that age gracefully — so your home stays comfortable and low-maintenance for decades.</p>
        <ul class="sus__chips">
          <li><?= icon('check', 14) ?> Natural light</li>
          <li><?= icon('check', 14) ?> Greener façades</li>
          <li><?= icon('check', 14) ?> Low upkeep</li>
        </ul>
      </div>
    </header>
  </div>

  <?php $sus = [
    ['leaf', 'Green walls & terraces', 'Planted façades and roof gardens that cool the home and soften the skyline.', '41%', '49%'],
    ['pen', 'Light-led layouts', 'Large openings and well-planned floor plans for natural light, airflow and vastu-friendly living.', '63%', '77%'],
    ['layers', 'Durable, warm materials', 'Timber-look and stone cladding chosen for beauty, low upkeep and long life.', '52%', '67%'],
    ['home', 'Built to be lived in', 'Cantilevered balconies and covered terraces that extend living space outdoors.', '27%', '35%'],
  ]; ?>
  <div class="susx rv" id="sus">
    <a class="susx__badge" href="#sus" aria-label="Explore our design features"><svg viewBox="0 0 120 120" aria-hidden="true"><defs><path id="susc" d="M60,60 m-47,0 a47,47 0 1,1 94,0 a47,47 0 1,1 -94,0"/></defs><text><textPath href="#susc">Explore · Explore · Explore ·</textPath></text></svg><i><?= icon('down', 20) ?></i></a>
    <div class="susx__img">
      <img src="<?= asset('img/hero-1.jpg') ?>" alt="Sustainable home design by Sparkle Constructions with green walls and terraces" loading="lazy">
      <?php foreach ($sus as $i => [$ic, $t, $d, $x, $y]): ?>
        <button type="button" class="sus__hs <?= $i === 0 ? 'is-on' : '' ?>" data-i="<?= $i ?>" style="left:<?= $x ?>;top:<?= $y ?>" aria-label="<?= e($t) ?>">
          <span class="sus__dot"><?= icon('check', 14) ?></span>
          <span class="sus__tip"><?= e($t) ?></span>
        </button>
      <?php endforeach; ?>

      <div class="susx__dock">
        <div class="susx__now">
          <small id="susNo">01 / 0<?= count($sus) ?></small>
          <b id="susTitle"><?= e($sus[0][1]) ?></b>
          <p id="susDesc"><?= e($sus[0][2]) ?></p>
        </div>
        <div class="susx__tabs" role="tablist">
          <?php foreach ($sus as $i => [$ic, $t, $d]): ?>
            <button type="button" role="tab" class="susx__tab <?= $i === 0 ? 'is-on' : '' ?>" data-i="<?= $i ?>" data-t="<?= e($t) ?>" data-d="<?= e($d) ?>">
              <span><?= icon($ic, 20) ?></span><em><?= e($t) ?></em><i class="susx__bar"></i>
            </button>
          <?php endforeach; ?>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- ============ PROJECTS (gliding rows) ============ -->
<?php $mq = $pdo->query("SELECT p.*, g.file FROM projects p JOIN gallery g ON g.id = p.gallery_id ORDER BY p.featured DESC, p.sort, p.id LIMIT 14")->fetchAll();
      $mqRows = [array_slice($mq, 0, 7), array_slice($mq, 7, 7)]; ?>
<section class="mq" id="projects-home">
  <div class="container mq__head rv">
    <span class="mq__bgword" aria-hidden="true">Projects</span>
    <i class="mq__shape mq__shape--a" aria-hidden="true"></i>
    <i class="mq__shape mq__shape--b" aria-hidden="true"></i>
    <i class="mq__shape mq__shape--c" aria-hidden="true"></i>

    <span class="mq__pill"><?= icon('grid', 16) ?> Featured projects</span>
    <h2>
      <span class="l1">Recent homes</span>
      <span class="l2">&amp; <em>designs</em>
        <svg class="mq__swoosh" viewBox="0 0 320 22" preserveAspectRatio="none" aria-hidden="true"><defs><linearGradient id="mqg" x1="0" x2="1"><stop offset="0" stop-color="#c88a1e"/><stop offset=".5" stop-color="#ffd98a"/><stop offset="1" stop-color="#e3a93b"/></linearGradient></defs><path d="M4 14C60 4 120 18 175 9S280 6 316 12" fill="none" stroke="url(#mqg)" stroke-width="4" stroke-linecap="round" pathLength="1"/></svg>
      </span>
    </h2>
    <p>Homes our team has <b>built</b> and <b>designed</b> — delivered with the same care, from first drawing to final handover.</p>
    <div class="mq__stats">
      <div><b data-count="<?= $counts['completed'] ?>">0</b><span>Completed homes</span></div>
      <i></i>
      <div><b data-count="<?= $counts['designs'] ?>">0</b><span>3D designs</span></div>
    </div>
  </div>

  <div class="mq__rows rv" aria-label="Featured projects">
    <?php $n = 0; foreach ($mqRows as $r => $row): ?>
      <div class="mq__row <?= $r ? 'mq__row--rev' : '' ?>">
        <div class="mq__track">
          <?php foreach ($row as $i => $p): $n++; $is3d = stripos($p['type'], '3d') !== false; ?>
            <a class="mq__c mq__c--<?= ['a', 'b', 'c'][($i + $r) % 3] ?> <?= ($i + $r) % 2 ? 'mq__c--lo' : '' ?> <?= $is3d ? 'is-3d' : '' ?>" href="<?= gimg($p['file']) ?>" data-lb="mq" data-cap="<?= e($p['title']) ?>" aria-label="<?= e($p['title']) ?>">
              <span class="mq__ph">
                <img src="<?= gthumb($p['file']) ?>" alt="<?= e($p['title']) ?>" draggable="false" <?= $i > 2 ? 'loading="lazy"' : '' ?>>
              </span>
            </a>
          <?php endforeach; ?>
        </div>
      </div>
    <?php endforeach; ?>
  </div>

  <div class="container mq__foot rv">
    <a class="pbtn" href="<?= url('projects.php') ?>">View all projects <span><?= icon('arrow', 18) ?></span></a>
    <span class="mq__count"><b><?= $counts['completed'] ?></b> Completed homes <i></i> <b><?= $counts['designs'] ?></b> 3D designs</span>
  </div>
</section>

<!-- ============ JOURNEY TIMELINE ============ -->
<section class="jr" id="journey-home">
  <div class="container">
    <header class="jr__head jr__head--ed rv">
      <div class="jr__left">
        <p class="jr__kick"><b>03</b><i></i><span>Our promise: transparency</span></p>
        <h2><span class="l1">From foundation to finishing</span><span class="l2">see <em>every stage</em></span></h2>
      </div>
      <div class="jr__right">
        <span class="jr__big" aria-hidden="true">08</span>
        <p>Understand exactly how your home is built — eight stages, what happens in each one, and how we check the work before moving on.</p>
        <div class="jr__dots" aria-label="8 stages"><i class="on"></i><i></i><i></i><i></i><i></i><i></i><i></i><i class="end"></i><span>8 stages</span></div>
      </div>
    </header>

    <?php
      $guide = [
        1 => ['pin',    'We mark the plot boundaries and the exact position of the building on the ground, straight from the approved plan.',
              ['Survey the plot and confirm boundaries', 'Mark column and wall centre-lines', 'Confirm setbacks and levels with you'],
              'The layout is checked against the drawing before a single trench is dug.'],
        2 => ['tool',   'Soil is dug out to the depth and size the foundation needs, down to firm ground that can carry the house.',
              ['Excavate footings to the design depth', 'Reach firm, load-bearing soil', 'Clear loose earth and level the base'],
              'Depth and soil strength are verified by the site engineer before steel goes in.'],
        3 => ['layers', 'Steel reinforcement is tied and concrete is poured to form the footings — the base that everything else stands on.',
              ['Tie steel exactly as per structural drawing', 'Cast the base layer and footing concrete', 'Cure the concrete properly'],
              'Steel spacing and cover are inspected before every pour. No inspection, no concrete.'],
        4 => ['grid',   'Columns and the plinth beam rise above ground, forming the skeleton of your home and the level for the floor.',
              ['Raise columns with tied steel cages', 'Build the plinth beam and blockwork', 'Fill and compact to floor level'],
              'Alignment, levels and verticality are measured before the next stage begins.'],
        5 => ['home',   'Walls go up and the floor and roof slabs are cast, one floor at a time, giving the house its shape.',
              ['Build masonry walls to the plan', 'Set shuttering and slab steel', 'Pour and cure each slab'],
              'A pre-pour inspection is signed off for every slab, and curing is monitored.'],
        6 => ['pen',    'Walls and ceilings are plastered to create smooth, durable surfaces ready for finishing.',
              ['Internal and external plastering', 'Waterproofing at sensitive areas', 'Ceiling and surface finishing'],
              'Surfaces are checked for level, finish and proper curing.'],
        7 => ['award',  'The home comes alive — flooring, doors, windows, woodwork, painting and fittings.',
              ['Flooring and tiling', 'Doors, windows and woodwork', 'Painting, lighting and fittings'],
              'A detailed snagging walk-through catches and fixes every small defect.'],
        8 => ['key',    'A final inspection, a thorough clean and the handover of keys — with support for your griha pravesh.',
              ['Final inspection and clean-up', 'Handover of keys and documents', 'Support for your griha pravesh'],
              'We walk through the finished home with you, room by room.'],
      ];
      $gv = $pdo->query("SELECT * FROM gallery WHERE category='journey' ORDER BY stage, sort, id")->fetchAll();
      $gvN = count($gv); $firstOf = [];
      foreach ($gv as $k => $g) if (!isset($firstOf[(int)$g['stage']])) $firstOf[(int)$g['stage']] = $k;
    ?>
    <div class="gv rv" id="gv">
      <!-- chapter chips -->
      <div class="gv__chips" role="tablist" aria-label="Construction stages">
        <?php foreach ($firstOf as $st => $k): ?>
          <button type="button" role="tab" class="gv__chip" data-stage="<?= $st ?>" data-i="<?= $k ?>"><b><?= sprintf('%02d', $st) ?></b><?= e(STAGES[$st] ?? '') ?></button>
        <?php endforeach; ?>
      </div>

      <div class="gv__main">
        <!-- big viewer -->
        <div class="gv__viewer">
          <?php foreach ($gv as $k => $g): ?>
            <figure class="gv__img <?= $k === 0 ? 'is-on' : '' ?>"><img src="<?= gimg($g['file']) ?>" alt="<?= e($g['title']) ?>" <?= $k > 1 ? 'loading="lazy"' : '' ?>></figure>
          <?php endforeach; ?>
          <div class="gv__top"><span class="gv__pill" id="gvPill"></span><span class="gv__cnt"><b id="gvNo">01</b> / <?= sprintf('%02d', $gvN) ?></span></div>
          <div class="gv__cap"><small>Real site photo</small><b id="gvCap"></b></div>
          <button type="button" class="gv__nav gv__nav--prev" id="gvPrev" aria-label="Previous photo"><?= icon('arrow', 22) ?></button>
          <button type="button" class="gv__nav gv__nav--next" id="gvNext" aria-label="Next photo"><?= icon('arrow', 22) ?></button>
          <button type="button" class="gv__open" id="gvOpen" aria-label="View full size"><?= icon('arrow', 18) ?></button>
        </div>
      </div>

      <!-- filmstrip of every photo, grouped by chapter -->
      <div class="gv__strip" id="gvStrip">
        <?php $lastSt = 0; foreach ($gv as $k => $g): $st = (int)$g['stage']; if ($st !== $lastSt): $lastSt = $st; ?>
          <span class="gv__chap"><b><?= sprintf('%02d', $st) ?></b><?= e(STAGES[$st] ?? '') ?></span>
        <?php endif; ?>
          <button type="button" class="gv__th <?= $k === 0 ? 'is-on' : '' ?>" data-i="<?= $k ?>" data-stage="<?= $st ?>" data-cap="<?= e($g['title']) ?>" aria-label="<?= e($g['title']) ?>"><img src="<?= gthumb($g['file']) ?>" alt="" loading="lazy"></button>
        <?php endforeach; ?>
      </div>
      <?php foreach ($gv as $g): ?><a class="gv__hid" href="<?= gimg($g['file']) ?>" data-lb="gv" data-cap="<?= e($g['title']) ?>" hidden></a><?php endforeach; ?>

      <div class="gv__foot"><a class="btn btn--lg" href="<?= url('gallery.php') ?>">Open the full gallery <?= icon('arrow', 18) ?></a></div>
    </div>
  </div>
</section>

<!-- ============ WHY US ============ -->
<section class="wx">
  <div class="wx__glow" aria-hidden="true"></div>
  <span class="wx__word" aria-hidden="true">TRUST</span>
  <div class="container">
    <header class="wx__head wx__head--c rv">
      <p class="wx__kick wx__kick--c"><i></i><span class="wx__dot"></span>Why Sparkle<span class="wx__dot"></span><i></i></p>
      <h2><span class="wx__h1">Why families trust us with their</span><em class="wx__h2">biggest investment<svg class="wx__sw" viewBox="0 0 520 22" preserveAspectRatio="none" aria-hidden="true"><path d="M4 15C90 4 170 4 260 10s170 4 256-4"/></svg></em></h2>
      <p class="wx__lead wx__lead--c">Honest quotes, <b>qualified engineers</b> and a check at every stage &mdash; so the home you imagine is the home you get.</p>
      <a class="btn btn--lg" href="<?= url('about.php') ?>">Meet the team <?= icon('arrow', 18) ?></a>
    </header>

    <div class="wx__grid">
      <figure class="wx__tile wx__tile--ph rv">
        <img src="<?= asset('img/hero-3.jpg') ?>" alt="A Sparkle Constructions home design" loading="lazy">
        <span class="wx__chip"><?= icon('award', 16) ?> Engineer-led, every site</span>
        <figcaption><small>Designed &amp; built by</small><b>Sparkle Constructions</b></figcaption>
      </figure>
      <article class="wx__tile wx__tile--r rv"><em class="wx__lbl"><b>01</b>People</em><span class="wx__ic"><?= icon('users', 26) ?></span><h3>Qualified on-site engineers</h3><p>No guesswork &mdash; every stage is checked and signed off.</p></article>
      <article class="wx__tile wx__tile--r rv"><em class="wx__lbl"><b>02</b>Pricing</em><span class="wx__ic"><?= icon('rupee', 26) ?></span><h3>Honest, itemised quotes</h3><p>Know exactly what you pay for, before work begins.</p></article>
      <article class="wx__tile wx__tile--r rv"><em class="wx__lbl"><b>03</b>Planet</em><span class="wx__ic"><?= icon('leaf', 26) ?></span><h3>Sustainable by design</h3><p>Efficient layouts and durable materials that cut long-term costs.</p></article>
      <article class="wx__tile wx__tile--stat rv">
        <em class="wx__lbl wx__lbl--d">By the numbers</em>
        <div class="wx__num"><b>08</b><span>stages, each checked<br>&amp; signed off</span></div>
        <div class="wx__num"><b><?= rupees(starting_rate()) ?><small>/sq.ft</small></b><span>starting price,<br>no hidden add-ons</span></div>
        <a href="<?= url('pricing.php') ?>" class="wx__go">See packages <?= icon('arrow', 16) ?></a>
      </article>
    </div>
  </div>
  <div class="wx__rib" aria-hidden="true"><div class="wx__rt">
    <?php for ($r = 0; $r < 2; $r++): foreach (['Transparent pricing', 'Engineer-led sites', 'Quality materials', 'Sustainable design', 'Every stage signed off', 'Honest quotes'] as $w): ?><span><?= $w ?></span><i></i><?php endforeach; endfor; ?>
  </div></div>
</section>

<!-- ============ PRICING ============ -->
<section class="pz pk">
  <span class="pz__word" aria-hidden="true">₹</span>
  <div class="container">
    <header class="pz2__head rv">
      <p class="pz__kick"><i></i>Pricing<i></i></p>
      <h2>Starting from <em><?= rupees(starting_rate()) ?></em> per sq.ft</h2>
      <p>Three clear packages and an instant estimator &mdash; no hidden add-ons.</p>
    </header>

    <?php $pkImg = ['essential' => 'hero-1.jpg', 'classic' => 'hero-3.jpg', 'signature' => 'hero-4.jpg']; $pi = 0; ?>
    <div class="pk__grid">
      <?php foreach (packages() as $k => $p): $pi++; $feat = $k === 'classic'; ?>
        <article class="pk__card rv <?= $feat ? 'is-feat' : '' ?>">
          <?php if ($feat): ?><span class="pk__flag"><?= icon('award', 14) ?> Most chosen</span><?php endif; ?>
          <div class="pk__ph">
            <img src="<?= asset('img/' . ($pkImg[$k] ?? 'hero-3.jpg')) ?>" alt="<?= e($p['name']) ?> package home" loading="lazy">
            <span class="pk__no">0<?= $pi ?></span>
            <div class="pk__nm"><small><?= e($p['tag']) ?></small><h3><?= e($p['name']) ?></h3></div>
          </div>
          <div class="pk__body">
            <div class="pk__price"><span class="pk__from">From</span><b><?= rupees($p['rate']) ?></b><span>/ sq.ft</span></div>
            <ul class="pk__list">
              <?php foreach (array_slice($p['items'], 0, 4) as $it): ?><li><i></i><?= e($it) ?></li><?php endforeach; ?>
            </ul>
            <a class="pk__cta" href="<?= url('pricing.php') ?>"><span>Choose <?= e($p['name']) ?></span><em><?= icon('arrow', 18) ?></em></a>
          </div>
        </article>
      <?php endforeach; ?>
    </div>
    <p class="pk__more rv"><a class="btn btn--lg" href="<?= url('pricing.php#estimate') ?>">Try the cost estimator <?= icon('arrow', 18) ?></a></p>
  </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>

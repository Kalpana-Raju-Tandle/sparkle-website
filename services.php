<?php
$active = 'services';
$title = 'Services';
$desc = 'Residential construction, 3D design, structural engineering, interiors, renovation and turnkey delivery from Sparkle Constructions.';
require __DIR__ . '/includes/header.php';

$services = [
    ['home', 'Residential Construction', 'svc-1', 'Independent houses, duplexes and triplexes — built stage by stage, with every pour and wall checked by an engineer.', ['Foundation to terrace, one accountable team', 'Stage-wise schedule and photo updates', 'Fixed, itemised pricing', 'Quality-tested materials']],
    ['pen', 'Architectural & 3D Design', 'svc-2', 'See your home before it exists. Floor plans, vastu-friendly layouts and photo-real 3D elevations tailored to your plot.', ['Custom floor plans', 'Photo-real 3D elevations', 'Vastu-friendly layouts', 'Revisions until you’re delighted']],
    ['layers', 'Structural Engineering', 'svc-3', 'Safe foundations and correctly designed structure — because the strongest facade means nothing on a weak frame.', ['Footing & column design', 'Steel and concrete quality control', 'Engineer sign-off at each stage', 'Pre-pour inspection']],
    ['grid', 'Interiors & Woodwork', 'svc-4', 'Doors, windows, cladding, ceilings and detailing that turn a built house into a lived-in home.', ['Wooden doors & frames', 'Cladding and elevation features', 'Lighting and false ceilings', 'Custom woodwork']],
    ['tool', 'Renovation & Extension', 'svc-5', 'Add a floor, reshape a facade or repair and refresh an older home without compromising its structure.', ['Structural assessment first', 'Additional floors & facade makeovers', 'Waterproofing and repairs', 'Minimal disruption']],
    ['key', 'Turnkey Delivery', 'svc-6', 'Hand us the vision and we handle the rest — design, build, finish — and give you the keys.', ['Single point of contact', 'Coordinated trades and vendors', 'Approvals support', 'Handover with griha pravesh support']],
];
$steps = [['calendar', 'Consult', 'We listen to your plot, family and budget.'], ['pen', 'Design', 'Floor plan and 3D elevation, refined with you.'], ['rupee', 'Quote', 'Itemised, written pricing — no surprises.'], ['layers', 'Build', 'Engineer-supervised construction, stage by stage.'], ['camera', 'Inspect', 'Quality checks and photo proof at each stage.'], ['key', 'Handover', 'Keys in hand, griha pravesh with a smile.']];
?>

<!-- ============ PAGE HERO ============ -->
<section class="phero has-vid" style="--bg:url('<?= asset('img/hero-4.jpg') ?>')">
  <video class="phero__vid" autoplay muted loop playsinline preload="metadata" poster="<?= asset('video/trowel_hd.jpg') ?>" aria-hidden="true"><source src="<?= asset('video/trowel_hd.mp4') ?>" type="video/mp4"></video>
  <div class="phero__shade" aria-hidden="true"></div>
  <div class="phero__frame" aria-hidden="true"></div>
  <div class="container phero__in">
    <nav class="crumbs" aria-label="Breadcrumb"><a href="<?= url('index.php') ?>">Home</a><i></i><span>Services</span></nav>
    <p class="kicker kicker--light"><span></span>Our services</p>
    <h1>Everything your home needs, <em>under one roof</em></h1>
    <p class="phero__lead">From the first sketch to the final coat of paint, one accountable team designs, engineers and builds your home — with transparent pricing at every step.</p>
    <div class="phero__chips">
      <div><b>6</b><span>Core services</span></div>
      <div><b>8</b><span>Photographed build stages</span></div>
      <div><b><?= rupees(starting_rate()) ?></b><span>Starting per sq.ft</span></div>
    </div>
  </div>
</section>

<!-- ============ SERVICES (bento) ============ -->
<section class="spage spage--bento">
  <div class="container">
    <header class="stintro rv">
      <p class="kicker"><span></span>What we offer</p>
      <h2>Six services, <em>one</em> seamless build</h2>
      <p>Everything your home needs — from the first drawing to the day you get the keys.</p>
    </header>

    <?php
      $S = $services; $n = count($S);
      $enq = fn($i) => url('contact.php?service=' . rawurlencode($S[$i][1]));
      $li = fn($i, $max = 3) => implode('', array_map(fn($p) => '<li>' . icon('check', 14) . ' ' . e($p) . '</li>', array_slice($S[$i][4], 0, $max)));
      $photo = function (int $i, string $cls, ?string $vid = null) use ($S, $n, $enq, $li) { [$ic, $t, $im, $d] = $S[$i]; ob_start(); ?>
        <article class="bt bt--photo <?= $cls ?> rv">
          <img src="<?= asset("img/$im.jpg") ?>" alt="<?= e($t) ?>" loading="lazy">
          <?php if ($vid): ?><video class="bt__vid" muted loop playsinline preload="none" data-v="<?= asset("video/{$vid}_hd.mp4") ?>" aria-hidden="true"></video><span class="bt__play"><i></i>On site</span><?php endif; ?>
          <span class="bt__no"><?= sprintf('%02d', $i + 1) ?></span>
          <div class="bt__cap">
            <div class="bt__head"><span class="bt__chip"><?= icon($ic, 22) ?></span><h3><?= e($t) ?></h3></div>
            <div class="bt__more"><div>
              <p><?= e($d) ?></p>
              <ul class="bt__ul"><?= $li($i) ?></ul>
              <a class="bt__link" href="<?= $enq($i) ?>">Enquire about this <i><?= icon('arrow', 16) ?></i></a>
            </div></div>
          </div>
        </article>
      <?php return ob_get_clean(); };
      $text = function (int $i, string $cls) use ($S, $n, $enq, $li) { [$ic, $t, $im, $d] = $S[$i]; ob_start(); ?>
        <article class="bt bt--text <?= $cls ?> rv">
          <span class="bt__no"><?= sprintf('%02d', $i + 1) ?></span>
          <span class="bt__chip"><?= icon($ic, 24) ?></span>
          <h3><?= e($t) ?></h3>
          <p><?= e($d) ?></p>
          <ul class="bt__ul"><?= $li($i, 4) ?></ul>
          <a class="bt__link" href="<?= $enq($i) ?>">Enquire about this <i><?= icon('arrow', 16) ?></i></a>
        </article>
      <?php return ob_get_clean(); };
    ?>
    <div class="bento">
      <?= $photo(0, 'bt--a', 'pour') ?>
      <?= $text(1, 'bt--b bt--maroon') ?>
      <?= $photo(2, 'bt--c', 'rebar') ?>
      <?= $photo(3, 'bt--d bt--s', 'trowel') ?>
      <?= $photo(4, 'bt--e') ?>
      <?= $text(5, 'bt--f bt--gold') ?>
    </div>
  </div>
</section>

<!-- ============ PROCESS ============ -->
<section class="proc">
  <div class="container">
    <header class="proc__head rv">
      <p class="kicker kicker--light"><span></span>How it works</p>
      <h2>Six steps from idea to <em>keys</em></h2>
    </header>
    <ol class="proc__grid">
      <?php foreach ($steps as $i => [$ic, $t, $d]): ?>
        <li class="pstep rv">
          <span class="pstep__no"><?= sprintf('%02d', $i + 1) ?></span>
          <span class="pstep__ic"><?= icon($ic, 22) ?></span>
          <h3><?= e($t) ?></h3>
          <p><?= e($d) ?></p>
        </li>
      <?php endforeach; ?>
    </ol>
  </div>
</section>

<!-- ============ FAQ ============ -->
<section class="faqs">
  <div class="container faqs__grid">
    <div class="faqs__side rv">
      <p class="kicker"><span></span>FAQ</p>
      <h2>Questions we hear <em>often</em></h2>
      <p>Can’t find what you’re looking for? Our team is happy to walk you through the process.</p>
      <a class="pbtn pbtn--dark" href="<?= url('contact.php') ?>">Ask us anything <span><?= icon('arrow', 18) ?></span></a>
    </div>
    <div class="faq2 rv">
      <?php foreach ([
        ['What is your starting price?', 'Construction starts from ' . rupees(starting_rate()) . ' per sq.ft. The exact rate depends on the package, specifications and design — use the estimator on the Pricing page for a quick ballpark.'],
        ['Do you also do the design?', 'Yes. Our designer prepares floor plans and 3D elevations so you can approve exactly what will be built.'],
        ['How will I know what’s happening on site?', 'We share stage-wise photo updates and you’re welcome to visit anytime. Our gallery shows how a typical build progresses.'],
        ['Can you build on my own plot?', 'Yes — we build on your plot after a site visit and soil/level assessment.'],
        ['How do I get started?', 'Send us an enquiry or WhatsApp us. We’ll schedule a free consultation and site visit.'],
      ] as $k => [$q, $a]): ?>
        <details <?= $k === 0 ? 'open' : '' ?>><summary><span><?= sprintf('%02d', $k + 1) ?></span><?= e($q) ?></summary><p><?= e($a) ?></p></details>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>

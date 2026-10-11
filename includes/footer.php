</main>

<footer class="ft">
  <section class="cta2">
    <div class="container">
      <div class="cta2__card rv">
        <i class="cta2__ring" aria-hidden="true"></i>
        <div class="cta2__txt">
          <p class="cta2__kick"><i></i>Let&rsquo;s build together</p>
          <h2>Ready to build your <em>dream home?</em></h2>
          <p class="cta2__p">Tell us about your plot and budget &mdash; we&rsquo;ll reply with a clear plan and a transparent quote, starting from <strong><?= rupees(starting_rate()) ?>/sq.ft</strong>.</p>
          <ul class="cta2__chips"><li><?= icon('check', 14) ?> Free consultation</li><li><?= icon('check', 14) ?> Clear plan</li><li><?= icon('check', 14) ?> Transparent quote</li></ul>
        </div>
        <div class="cta2__btns">
            <a class="btn btn--lg" href="<?= url('contact.php') ?>">Book a Free Consultation <?= icon('arrow', 18) ?></a>
            <a class="cta2__call" href="<?= e(phone_href()) ?>"><span><?= icon('phone', 18) ?></span><div><small>Or call us</small><b><?= e(setting('phone')) ?></b></div></a>
        </div>
        <figure class="cta2__ph">
          <img src="<?= asset('img/hero-3.jpg') ?>" alt="A home designed by Sparkle Constructions" loading="lazy">
          <figcaption><small>Your plot</small><small>Your budget</small><b>Our plan.</b></figcaption>
        </figure>
      </div>
    </div>
  </section>
  <div class="container">
    <div class="ft__grid">
      <div class="ft__brand">
        <span class="logo-plate"><img src="<?= asset('img/logo.png') ?>" alt="<?= e(SITE_NAME) ?>" loading="lazy" width="715" height="237"></span>
        <p>Building sustainable homes for future generations &mdash; <em>designed with care, engineered on site, delivered with honesty.</em></p>
        <a class="ft__wa" href="<?= e(wa_href('Hi Sparkle Constructions!')) ?>" target="_blank" rel="noopener"><span><?= icon('whatsapp', 17) ?></span> Chat on WhatsApp</a>
      </div>
      <nav class="ft__col" aria-label="Explore">
        <h3>Explore</h3>
        <ul>
          <li><a href="<?= url('about.php') ?>">About Us</a></li>
          <li><a href="<?= url('services.php') ?>">Services</a></li>
          <li><a href="<?= url('projects.php') ?>">Projects</a></li>
          <li><a href="<?= url('gallery.php') ?>">Gallery</a></li>
          <li><a href="<?= url('pricing.php') ?>">Pricing &amp; Estimator</a></li>
        </ul>
      </nav>
      <div class="ft__col">
        <h3>Visit us</h3>
        <div class="ft__it"><span><?= icon('pin', 18) ?></span><address><?= nl2br(e(setting('address_primary'))) ?></address></div>
        <?php if (setting('address_secondary')): ?><div class="ft__it ft__it--m"><span><?= icon('pin', 18) ?></span><address><?= nl2br(e(setting('address_secondary'))) ?></address></div><?php endif; ?>
      </div>
      <div class="ft__col">
        <h3>Talk to us</h3>
        <?php foreach (phone_numbers() as $p): ?><a class="ft__it" href="<?= e($p['href']) ?>"><span><?= icon('phone', 18) ?></span><div><?= e($p['label']) ?></div></a><?php endforeach; ?>
        <a class="ft__it" href="mailto:<?= e(setting('email')) ?>"><span><?= icon('mail', 18) ?></span><div><?= e(setting('email')) ?></div></a>
        <div class="ft__it"><span><?= icon('clock', 18) ?></span><div><?= e(setting('hours')) ?></div></div>
      </div>
    </div>
    <div class="ft__mark" aria-hidden="true">SPARKLE</div>
    <div class="ft__bar">
      <span>&copy; <?= date('Y') ?> <?= e(SITE_NAME) ?>. All rights reserved.</span>
      <div><a href="<?= url('admin/') ?>">Team login</a><button type="button" class="ft__top" aria-label="Back to top" onclick="window.scrollTo({top:0,behavior:'smooth'})"><?= icon('arrow', 18) ?></button></div>
    </div>
  </div>
</footer>

<a class="wa-float" href="<?= e(wa_href('Hi Sparkle Constructions!')) ?>" target="_blank" rel="noopener" aria-label="Chat on WhatsApp">
  <svg viewBox="0 0 32 32" width="30" height="30" aria-hidden="true"><path fill="#fff" d="M16 3C9.4 3 4 8.3 4 14.9c0 2.4.7 4.6 1.9 6.5L4 29l7.8-1.9a12 12 0 0 0 4.2.8c6.6 0 12-5.3 12-11.9S22.6 3 16 3zm0 21.800c-1.300 0-2.600-.3-3.700-.9l-.5-.3-4.600 1.100 1.200-4.400-.3-.5a9.700 9.700 0 0 1-1.500-5.200C6.600 9.700 10.800 5.600 16 5.600s9.400 4.100 9.400 9.300-4.200 9.900-9.400 9.900zm5.400-7.300c-.3-.1-1.700-.8-2-.9s-.5-.1-.7.1-.8.900-.9 1.100-.3.200-.6.100a7.800 7.800 0 0 1-3.900-3.400c-.3-.5.300-.5.800-1.500.1-.2 0-.4 0-.5l-.9-2.100c-.2-.5-.5-.5-.7-.5h-.6c-.2 0-.5.100-.8.400s-1 1-1 2.400 1 2.800 1.200 3c.1.200 2 3.100 4.900 4.300 1.800.8 2.500.8 3.400.7.600-.1 1.700-.7 2-1.400s.2-1.200.2-1.400-.2-.2-.5-.4z"/></svg>
</a>

<div class="lightbox" id="lightbox" hidden aria-modal="true" role="dialog" aria-label="Image viewer">
  <button class="lb__close" aria-label="Close">×</button>
  <button class="lb__nav lb__prev" aria-label="Previous">‹</button>
  <figure><img alt=""><video controls playsinline preload="metadata" hidden></video><figcaption></figcaption></figure>
  <button class="lb__nav lb__next" aria-label="Next">›</button>
</div>

<script src="<?= asset('js/site.js') ?>" defer></script>
<script src="<?= asset('js/fx.js') ?>" defer></script>
<?php if (!empty($extraJs)) echo $extraJs; ?>
</body>
</html>

<?php
require_once __DIR__ . '/includes/helpers.php';

$errors = [];
$old = ['name' => '', 'phone' => '', 'email' => '', 'service' => '', 'message' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach ($old as $k => $_) $old[$k] = trim((string)($_POST[$k] ?? ''));
    $source = in_array($_POST['source'] ?? '', ['contact', 'estimate'], true) ? $_POST['source'] : 'contact';
    $back = $source === 'estimate' ? 'pricing.php#estimate' : 'contact.php';

    if (!csrf_ok()) $errors[] = 'Your session expired — please try again.';
    elseif (($_POST['website'] ?? '') !== '') redirect(url($back));            // honeypot
    else {
        $recent = (int)($_SESSION['last_enquiry'] ?? 0);
        if (time() - $recent < 20) $errors[] = 'Please wait a few seconds before sending another enquiry.';
        if (mb_strlen($old['name']) < 2) $errors[] = 'Please enter your name.';
        if (!preg_match('/^[+\d][\d\s\-()]{7,18}$/', $old['phone'])) $errors[] = 'Please enter a valid phone number.';
        if ($old['email'] !== '' && !filter_var($old['email'], FILTER_VALIDATE_EMAIL)) $errors[] = 'That email address doesn’t look right.';
        if (mb_strlen($old['message']) > 3000) $errors[] = 'Message is too long.';
    }

    if (!$errors) {
        db()->prepare('INSERT INTO enquiries (name, phone, email, service, message, source) VALUES (?,?,?,?,?,?)')
            ->execute([mb_substr($old['name'], 0, 120), $old['phone'], mb_substr($old['email'], 0, 160), mb_substr($old['service'], 0, 120), $old['message'], $source]);
        $_SESSION['last_enquiry'] = time();
        if (filter_var(setting('email'), FILTER_VALIDATE_EMAIL) && setting('email') !== SETTING_DEFAULTS['email']) {
            @mail(setting('email'), 'New website enquiry — ' . $old['name'],
                "Name: {$old['name']}\nPhone: {$old['phone']}\nEmail: {$old['email']}\nInterested in: {$old['service']}\n\n{$old['message']}",
                'From: no-reply@' . ($_SERVER['HTTP_HOST'] ?? 'localhost') . "\r\nReply-To: " . ($old['email'] ?: setting('email')));
        }
        flash('Thank you, ' . $old['name'] . '! We’ve received your enquiry and will call you shortly.');
        redirect(url($back));
    }
    if ($source === 'estimate') { flash(implode(' ', $errors), 'err'); redirect(url($back)); }
}

$active = 'contact';
$title = 'Contact';
$desc = 'Visit Sparkle Constructions in Pandavapura, call us or send an enquiry for a free consultation.';
$prefill = trim((string)($_GET['service'] ?? ''));
if (!$old['service'] && $prefill) $old['service'] = $prefill;
require __DIR__ . '/includes/header.php';
?>
<section class="page-hero" style="--bg:url('<?= asset('img/hero-3.jpg') ?>')">
  <div class="container" data-w="CONTACT">
    <nav class="crumb" aria-label="Breadcrumb"><a href="<?= url('index.php') ?>">Home</a><i></i><span>Contact</span></nav>
    <span class="eyebrow eyebrow--light">Contact</span>
    <h1 data-split="hero">Let’s talk about <em>your home.</em></h1>
    <p class="lead">Free consultation. No pressure. A clear answer on cost and timeline.</p>
    <ul class="ph__facts"><li><?= icon('pin', 16) ?> Pandavapura, Karnataka</li><li><?= icon('award', 16) ?> Engineer-led sites</li><li><?= icon('rupee', 16) ?> From <?= rupees(starting_rate()) ?>/sq.ft</li></ul>
  </div>
</section>

<section class="sheet ct">
  <div class="container contact">
    <div class="contact__info reveal">
      <p class="pgk"><i></i>Find us</p><h2>Visit our <em>office</em></h2>
      <div class="infocard infocard--primary">
        <span class="pill">Primary address</span>
        <address><?= nl2br(e(setting('address_primary'))) ?></address>
        <a class="link" target="_blank" rel="noopener" href="<?= e(maps_href(setting('address_primary'))) ?>">Get directions →</a>
      </div>
      <?php if (setting('address_secondary')): ?>
        <div class="infocard"><span class="pill pill--alt">Other office</span><address><?= nl2br(e(setting('address_secondary'))) ?></address><a class="link" target="_blank" rel="noopener" href="<?= e(maps_href(setting('address_secondary'))) ?>">Get directions →</a></div>
      <?php endif; ?>
      <ul class="reach">
        <li><span class="ri"><?= icon('phone', 22) ?></span><div><small>Call</small><?php foreach (phone_numbers() as $i => $p): ?><?= $i ? '<br>' : '' ?><a href="<?= e($p['href']) ?>"><?= e($p['label']) ?></a><?php endforeach; ?></div></li>
        <li><span class="ri ri--wa"><?= icon('whatsapp', 24) ?></span><div><small>WhatsApp</small><a target="_blank" rel="noopener" href="<?= e(wa_href('Hi Sparkle Constructions!')) ?>">Chat with us</a></div></li>
        <li><span class="ri"><?= icon('mail', 22) ?></span><div><small>Email</small><a href="mailto:<?= e(setting('email')) ?>"><?= e(setting('email')) ?></a></div></li>
        <li><span class="ri"><?= icon('clock', 22) ?></span><div><small>Hours</small><?= e(setting('hours')) ?></div></li>
      </ul>
    </div>

    <form class="form reveal" method="post" action="<?= url('contact.php') ?>" novalidate>
      <p class="pgk"><i></i>Free consultation</p><h2>Send an <em>enquiry</em></h2>
      <?php if ($errors): ?><ul class="form__errors"><?php foreach ($errors as $er): ?><li><?= e($er) ?></li><?php endforeach; ?></ul><?php endif; ?>
      <?= csrf_field() ?>
      <input type="hidden" name="source" value="contact">
      <input class="hp" type="text" name="website" tabindex="-1" autocomplete="off" aria-hidden="true">
      <label>Your name *<input name="name" required value="<?= e($old['name']) ?>" autocomplete="name"></label>
      <div class="form__row">
        <label>Phone *<input name="phone" type="tel" required value="<?= e($old['phone']) ?>" autocomplete="tel"></label>
        <label>Email<input name="email" type="email" value="<?= e($old['email']) ?>" autocomplete="email"></label>
      </div>
      <label>I’m interested in
        <select name="service">
          <?php foreach (['', 'New home construction', 'Architectural & 3D design', 'Renovation / extension', 'Interiors & woodwork', 'Just exploring'] as $o): ?>
            <option value="<?= e($o) ?>" <?= $old['service'] === $o ? 'selected' : '' ?>><?= $o ?: 'Select a service' ?></option>
          <?php endforeach; ?>
        </select>
      </label>
      <label>Tell us about your plot &amp; plans<textarea name="message" rows="5" placeholder="Plot size, number of floors, budget, timeline…"><?= e($old['message']) ?></textarea></label>
      <button class="btn btn--primary btn--lg" type="submit">Send enquiry</button>
    </form>
  </div>
</section>

<section class="sheet ct-map">
  <iframe title="Map of Pandavapura" loading="lazy" referrerpolicy="no-referrer-when-downgrade"
    src="https://maps.google.com/maps?q=Pandavapura%2C%20Karnataka&amp;z=13&amp;output=embed"></iframe>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>

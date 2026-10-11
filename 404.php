<?php
http_response_code(404);
$title = 'Page not found';
require __DIR__ . '/includes/header.php';
?>
<section class="section"><div class="container center narrow">
  <span class="eyebrow">404</span>
  <h1 style="font-size:clamp(2.4rem,6vw,4.5rem)">This page isn’t built yet.</h1>
  <p class="lead" style="margin-inline:auto;color:var(--muted)">The link may be old or mistyped. Let’s get you back to solid ground.</p>
  <p class="mt"><a class="btn btn--primary btn--lg" href="<?= url('index.php') ?>">Back to home</a></p>
</div></section>
<?php require __DIR__ . '/includes/footer.php'; ?>

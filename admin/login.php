<?php
require_once __DIR__ . '/../includes/helpers.php';
header('X-Robots-Tag: noindex, nofollow');
if (is_admin()) redirect(url('admin/'));

$err = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pdo = db();
    $ip = $_SERVER['REMOTE_ADDR'] ?? '?';
    $pdo->prepare('DELETE FROM login_attempts WHERE at < ?')->execute([time() - 900]);
    $tries = $pdo->prepare('SELECT COUNT(*) FROM login_attempts WHERE ip = ?'); $tries->execute([$ip]);

    if (!csrf_ok()) $err = 'Session expired — please try again.';
    elseif ((int)$tries->fetchColumn() >= 6) $err = 'Too many attempts. Please wait 15 minutes.';
    else {
        $st = $pdo->prepare('SELECT * FROM admins WHERE username = ?');
        $st->execute([trim((string)($_POST['username'] ?? ''))]);
        $u = $st->fetch();
        if ($u && password_verify((string)($_POST['password'] ?? ''), $u['password_hash'])) {
            session_regenerate_id(true);
            $_SESSION['admin_id'] = (int)$u['id'];
            $pdo->prepare('DELETE FROM login_attempts WHERE ip = ?')->execute([$ip]);
            redirect(url('admin/'));
        }
        $pdo->prepare('INSERT INTO login_attempts (ip, at) VALUES (?, ?)')->execute([$ip, time()]);
        $err = 'Incorrect username or password.';
    }
}
?><!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<meta name="robots" content="noindex,nofollow"><title>Team login · Sparkle Constructions</title>
<link rel="icon" href="<?= asset('img/icon.png') ?>">
<link rel="preconnect" href="https://fonts.googleapis.com"><link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@1,500&family=Inter:wght@400;500;600&family=Outfit:wght@500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?= asset('css/admin.css') ?>"></head>
<body class="login">
<main class="auth">
  <aside class="auth__art" style="background-image:url('<?= asset('img/hero-2.jpg') ?>')" aria-hidden="true"></aside>
  <section class="auth__panel">
  <div class="lcard">
    <form class="login__box" method="post">
      <header class="login__head">
        <span class="login__badge"><img src="<?= asset('img/logo.png') ?>" alt="Sparkle Constructions"></span>
        <p class="login__kick"><i></i>Team area<i></i></p>
        <h1>Welcome <em>back</em></h1>
        <p class="login__lead">Sign in to manage your website.</p>
      </header>
      <div class="login__body">
        <?php if ($err): ?><div class="note note--err"><?= icon('x', 18) ?><span><?= e($err) ?></span></div><?php endif; ?>
        <?= csrf_field() ?>
        <div class="ffield">
          <span class="ffield__ic"><?= icon('users', 18) ?></span>
          <input id="lgUser" name="username" autocomplete="username" required autofocus placeholder=" ">
          <label for="lgUser">Username</label>
        </div>
        <div class="ffield">
          <span class="ffield__ic"><?= icon('key', 18) ?></span>
          <input id="pw" name="password" type="password" autocomplete="current-password" required placeholder=" ">
          <label for="pw">Password</label>
          <button type="button" class="pweye" aria-label="Show password" title="Show password"><?= icon('eye', 18) ?><?= icon('eyeoff', 18) ?></button>
        </div>
        <button class="btn login__btn" type="submit"><span>Sign in</span><i><?= icon('arrow', 18) ?></i></button>
        <a class="login__back" href="<?= url('index.php') ?>">← Back to website</a>
      </div>
    </form>
  </div>
  </section>
</main>
<script src="<?= asset('js/admin.js') ?>" defer></script>
</body></html>

<?php
require_once __DIR__ . '/../includes/helpers.php';
header('X-Robots-Tag: noindex, nofollow');
if (is_admin()) redirect(url('admin/index.php'));

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
            redirect(url('admin/index.php'));
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
  <aside class="auth__art" style="background-image:url('<?= asset('img/hero-2.jpg') ?>')">
    <div class="auth__art-in">
      <p class="auth__kick"><i></i>Sparkle Constructions</p>
      <h2>Building sustainable homes <em>for future generations.</em></h2>
      <ul class="auth__pts">
        <li><?= icon('camera', 16) ?> Upload site photos &amp; videos</li>
        <li><?= icon('home', 16) ?> Showcase finished homes</li>
        <li><?= icon('mail', 16) ?> Reply to new enquiries</li>
      </ul>
    </div>
  </aside>
  <form class="login__box" method="post">
    <img src="<?= asset('img/logo.png') ?>" alt="Sparkle Constructions">
    <p class="login__kick"><i></i>Team area<i></i></p>
    <h1>Welcome <em>back</em></h1>
    <p>Sign in to manage your website.</p>
    <?php if ($err): ?><div class="note note--err"><?= e($err) ?></div><?php endif; ?>
    <?= csrf_field() ?>
    <label>Username<span class="field"><?= icon('users', 18) ?><input name="username" autocomplete="username" required autofocus placeholder="Your username"></span></label>
    <label>Password<span class="field"><?= icon('key', 18) ?><input name="password" id="pw" type="password" autocomplete="current-password" required placeholder="Your password"><button type="button" class="field__eye" id="pwEye" aria-label="Show password">Show</button></span></label>
    <button class="btn" type="submit">Sign in <?= icon('arrow', 18) ?></button>
    <a class="back" href="<?= url('index.php') ?>">← Back to website</a>
  </form>
</main>
<script>
const pw = document.getElementById('pw'), eye = document.getElementById('pwEye');
eye.onclick = () => { const show = pw.type === 'password'; pw.type = show ? 'text' : 'password'; eye.textContent = show ? 'Hide' : 'Show'; eye.setAttribute('aria-label', show ? 'Hide password' : 'Show password'); pw.focus(); };
</script>
</body></html>

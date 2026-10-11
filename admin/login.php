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
<link rel="icon" href="<?= asset('img/icon.png') ?>"><link rel="stylesheet" href="<?= asset('css/admin.css') ?>"></head>
<body class="login">
<form class="login__box" method="post">
  <img src="<?= asset('img/logo.png') ?>" alt="Sparkle Constructions">
  <h1>Team login</h1>
  <p>Sign in to upload photos, update projects and read enquiries.</p>
  <?php if ($err): ?><div class="note note--err"><?= e($err) ?></div><?php endif; ?>
  <?= csrf_field() ?>
  <label>Username<input name="username" autocomplete="username" required autofocus></label>
  <label>Password<input name="password" type="password" autocomplete="current-password" required></label>
  <button class="btn" type="submit">Sign in</button>
  <a class="back" href="<?= url('index.php') ?>">← Back to website</a>
</form>
</body></html>

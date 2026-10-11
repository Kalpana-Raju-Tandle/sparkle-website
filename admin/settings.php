<?php
require __DIR__ . '/_layout.php';
$pdo = db();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_ok()) { flash('Session expired — try again.', 'err'); redirect('settings.php'); }

    if (($_POST['do'] ?? '') === 'password') {
        $u = $pdo->prepare('SELECT * FROM admins WHERE id=?'); $u->execute([$_SESSION['admin_id']]); $u = $u->fetch();
        $new = (string)($_POST['new'] ?? '');
        if (!$u || !password_verify((string)($_POST['current'] ?? ''), $u['password_hash'])) flash('Current password is incorrect.', 'err');
        elseif (strlen($new) < 10) flash('New password must be at least 10 characters.', 'err');
        elseif ($new !== ($_POST['confirm'] ?? '')) flash('New passwords do not match.', 'err');
        else {
            $pdo->prepare('UPDATE admins SET password_hash=? WHERE id=?')->execute([password_hash($new, PASSWORD_DEFAULT), $u['id']]);
            $pdo->prepare("UPDATE settings SET v='1' WHERE k='pw_changed'")->execute();
            session_regenerate_id(true);
            flash('Password updated.');
        }
        redirect('settings.php');
    }

    $keys = array_diff(array_keys(SETTING_DEFAULTS), ['pw_changed']);
    $up = $pdo->prepare('INSERT INTO settings (k,v) VALUES (?,?) ON CONFLICT(k) DO UPDATE SET v=excluded.v');
    foreach ($keys as $k) {
        if (!isset($_POST[$k])) continue;
        $v = trim((string)$_POST[$k]);
        if (str_starts_with($k, 'price_')) $v = (string)max(0, (int)preg_replace('/\D/', '', $v));
        $up->execute([$k, mb_substr($v, 0, 4000)]);
    }
    flash('Settings saved — the website is updated.');
    redirect('settings.php');
}

$S = fn($k) => e(setting($k));
admin_head('Settings', 'settings');
?>
<form method="post" class="panel form2"><?= csrf_field() ?>
  <h2>Contact details</h2>
  <div class="g2">
    <label>Phone (shown on website)<input name="phone" value="<?= $S('phone') ?>"></label>
    <label>Other phone numbers (optional, separate with / or ,)<input name="phone_alt" value="<?= $S('phone_alt') ?>"></label>
    <label>WhatsApp number (digits with country code, e.g. 919876543210)<input name="whatsapp" value="<?= $S('whatsapp') ?>"></label>
    <label>Email (enquiries are also emailed here)<input name="email" type="email" value="<?= $S('email') ?>"></label>
    <label>Opening hours<input name="hours" value="<?= $S('hours') ?>"></label>
  </div>
  <label>Primary address — Pandavapura <small>(first line = name, last line is shown in the top bar)</small><textarea name="address_primary" rows="3"><?= $S('address_primary') ?></textarea></label>
  <label>Secondary address (optional)<textarea name="address_secondary" rows="2"><?= $S('address_secondary') ?></textarea></label>

  <h2>Pricing (₹ per sq.ft)</h2>
  <div class="g3">
    <label>Essential — starting rate<input name="price_essential" inputmode="numeric" value="<?= $S('price_essential') ?>"></label>
    <label>Classic<input name="price_classic" inputmode="numeric" value="<?= $S('price_classic') ?>"></label>
    <label>Signature<input name="price_signature" inputmode="numeric" value="<?= $S('price_signature') ?>"></label>
  </div>

  <h2>Founder profile <small>(About page)</small></h2>
  <div class="g2">
    <label>Name<input name="client_name" value="<?= $S('client_name') ?>"></label>
    <label>Role<input name="client_role" value="<?= $S('client_role') ?>"></label>
  </div>
  <label>About the founder (blank line = new paragraph)<textarea name="client_bio" rows="7"><?= $S('client_bio') ?></textarea></label>

  <h2>Designer profile <small>(On-site engineering section)</small></h2>
  <div class="g2">
    <label>Name<input name="designer_name" value="<?= $S('designer_name') ?>"></label>
    <label>Role<input name="designer_role" value="<?= $S('designer_role') ?>"></label>
  </div>
  <label>About the designer<textarea name="designer_bio" rows="5"><?= $S('designer_bio') ?></textarea></label>
  <button class="btn">Save settings</button>
</form>

<form method="post" class="panel form2"><?= csrf_field() ?><input type="hidden" name="do" value="password">
  <h2>Change admin password</h2>
  <div class="g3">
    <label>Current password<input type="password" name="current" autocomplete="current-password" required></label>
    <label>New password (10+ characters)<input type="password" name="new" autocomplete="new-password" minlength="10" required></label>
    <label>Confirm new password<input type="password" name="confirm" autocomplete="new-password" required></label>
  </div>
  <button class="btn">Update password</button>
</form>
<?php admin_foot();

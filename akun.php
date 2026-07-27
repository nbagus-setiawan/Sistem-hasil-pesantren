<?php
require_once __DIR__ . '/includes/auth.php';
require_login();

$activePage = 'akun';
$user = current_user();
$errors = [];
$success = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $lama = $_POST['password_lama'] ?? '';
    $baru = $_POST['password_baru'] ?? '';
    $ulangi = $_POST['password_ulangi'] ?? '';

    $stmt = db()->prepare("SELECT password_hash FROM users WHERE id = ?");
    $stmt->execute([$user['id']]);
    $hashLama = $stmt->fetch()['password_hash'];

    if (!password_verify($lama, $hashLama)) {
        $errors[] = 'Password lama yang Anda masukkan salah.';
    } elseif (strlen($baru) < 8) {
        $errors[] = 'Password baru minimal 8 karakter.';
    } elseif ($baru !== $ulangi) {
        $errors[] = 'Konfirmasi password baru tidak cocok.';
    }

    if (empty($errors)) {
        $hashBaru = password_hash($baru, PASSWORD_DEFAULT);
        db()->prepare("UPDATE users SET password_hash = ? WHERE id = ?")->execute([$hashBaru, $user['id']]);
        $success = 'Password berhasil diubah. Gunakan password baru saat login berikutnya.';
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Pengaturan Akun — Al-Falah Putak</title>
<link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
<div class="app">
  <?php include __DIR__ . '/includes/sidebar.php'; ?>

  <main class="main">
    <div class="topbar reveal">
      <div class="greeting">
        <h1 class="font-display">Pengaturan Akun</h1>
        <p>Kelola informasi login Anda</p>
      </div>
    </div>

    <div class="form-card reveal" style="margin-bottom:20px;">
      <div class="form-card-head">
        <h2 class="font-display" style="font-size:17px;">Profil</h2>
      </div>
      <div class="form-card-body">
        <div class="form-row">
          <div>
            <div style="font-size:11px; font-weight:800; text-transform:uppercase; color:var(--slate-400); margin-bottom:4px;">Nama</div>
            <div style="font-weight:700;"><?= e($user['nama_lengkap']) ?></div>
          </div>
          <div>
            <div style="font-size:11px; font-weight:800; text-transform:uppercase; color:var(--slate-400); margin-bottom:4px;">Username</div>
            <div style="font-weight:700;"><?= e($user['username']) ?></div>
          </div>
        </div>
        <div class="form-row">
          <div>
            <div style="font-size:11px; font-weight:800; text-transform:uppercase; color:var(--slate-400); margin-bottom:4px;">Jabatan</div>
            <div style="font-weight:700;"><?= e($user['jabatan']) ?></div>
          </div>
          <div>
            <div style="font-size:11px; font-weight:800; text-transform:uppercase; color:var(--slate-400); margin-bottom:4px;">Peran</div>
            <div style="font-weight:700;"><?= $user['role'] === 'admin' ? 'Admin' : 'Pengurus' ?></div>
          </div>
        </div>
      </div>
    </div>

    <div class="form-card reveal">
      <div class="form-card-head">
        <h2 class="font-display" style="font-size:17px;">Ubah Password</h2>
      </div>

      <?php if (!empty($errors)): ?>
        <div style="padding:0 26px;">
          <div class="alert alert-error">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M12 8v4M12 16h.01"/></svg>
            <span><?= implode(' ', array_map('e', $errors)) ?></span>
          </div>
        </div>
      <?php endif; ?>
      <?php if ($success): ?>
        <div style="padding:0 26px;">
          <div class="alert alert-success">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 6L9 17l-5-5"/></svg>
            <span><?= e($success) ?></span>
          </div>
        </div>
      <?php endif; ?>

      <form method="post">
        <div class="form-card-body">
          <div class="form-group">
            <label>Password Lama</label>
            <input type="password" name="password_lama" required>
          </div>
          <div class="form-row">
            <div class="form-group">
              <label>Password Baru</label>
              <input type="password" name="password_baru" required minlength="8">
            </div>
            <div class="form-group">
              <label>Ulangi Password Baru</label>
              <input type="password" name="password_ulangi" required minlength="8">
            </div>
          </div>
          <p style="font-size:12px; color:var(--slate-400);">Minimal 8 karakter.</p>
        </div>
        <div class="form-card-foot">
          <button type="submit" class="btn btn-primary">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M20 6L9 17l-5-5"/></svg>
            Simpan Password Baru
          </button>
        </div>
      </form>
    </div>
  </main>
</div>
<script src="assets/js/app.js"></script>
</body>
</html>

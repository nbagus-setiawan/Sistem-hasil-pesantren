<?php
require_once __DIR__ . '/includes/auth.php';
require_admin();

$activePage = 'pengguna';
$user = current_user();
$errors = [];
$success = flash_get('success');

// Toggle aktif/nonaktif
if (isset($_GET['toggle'])) {
    $tid = (int)$_GET['toggle'];
    if ($tid !== (int)$user['id']) { // tidak bisa menonaktifkan diri sendiri
        db()->prepare("UPDATE users SET aktif = NOT aktif WHERE id = ?")->execute([$tid]);
        flash_set('success', 'Status pengguna berhasil diperbarui.');
    } else {
        flash_set('success', 'Anda tidak bisa menonaktifkan akun Anda sendiri.');
    }
    redirect('users_list.php');
}

// Reset password ke default
if (isset($_GET['reset'])) {
    $rid = (int)$_GET['reset'];
    $hash = password_hash('alfalah2026', PASSWORD_DEFAULT);
    db()->prepare("UPDATE users SET password_hash = ? WHERE id = ?")->execute([$hash, $rid]);
    flash_set('success', 'Password pengguna direset ke default: alfalah2026');
    redirect('users_list.php');
}

// Tambah pengguna baru
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $nama = trim($_POST['nama_lengkap'] ?? '');
    $jabatan = trim($_POST['jabatan'] ?? '');
    $role = $_POST['role'] ?? 'pengurus';
    $password = $_POST['password'] ?? '';

    if ($username === '' || $nama === '' || $password === '') {
        $errors[] = 'Username, nama, dan password wajib diisi.';
    } elseif (strlen($password) < 8) {
        $errors[] = 'Password minimal 8 karakter.';
    } elseif (!in_array($role, ['admin','pengurus'], true)) {
        $errors[] = 'Peran tidak valid.';
    } else {
        $cek = db()->prepare("SELECT id FROM users WHERE username = ?");
        $cek->execute([$username]);
        if ($cek->fetch()) {
            $errors[] = 'Username sudah dipakai, pilih username lain.';
        }
    }

    if (empty($errors)) {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        db()->prepare("INSERT INTO users (username, password_hash, nama_lengkap, jabatan, role) VALUES (?,?,?,?,?)")
            ->execute([$username, $hash, $nama, $jabatan ?: 'Pengurus', $role]);
        flash_set('success', 'Pengguna baru "' . $nama . '" berhasil ditambahkan.');
        redirect('users_list.php');
    }
}

$daftarUser = db()->query("SELECT * FROM users ORDER BY role DESC, nama_lengkap ASC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Kelola Pengguna — Al-Falah Putak</title>
<link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
<div class="app">
  <?php include __DIR__ . '/includes/sidebar.php'; ?>

  <main class="main">
    <div class="topbar reveal">
      <div class="greeting">
        <h1 class="font-display">Kelola Pengguna</h1>
        <p>Atur siapa saja yang bisa login ke sistem ini</p>
      </div>
    </div>

    <?php if ($success): ?>
      <div class="alert alert-success reveal"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 6L9 17l-5-5"/></svg><?= e($success) ?></div>
    <?php endif; ?>

    <div class="form-card reveal" style="max-width:100%; margin-bottom:24px;">
      <div class="form-card-head"><h2 class="font-display" style="font-size:17px;">Daftar Pengguna</h2></div>
      <div class="form-card-body" style="overflow-x:auto;">
        <?php foreach ($daftarUser as $u): ?>
          <div class="lampiran-item" style="align-items:center;">
            <div class="user-avatar" style="width:32px;height:32px;font-size:11px; flex-shrink:0;"><?= e(inisial($u['nama_lengkap'])) ?></div>
            <div style="flex:1; min-width:0;">
              <div style="font-weight:700;"><?= e($u['nama_lengkap']) ?> <span style="font-weight:400; color:var(--slate-400); font-size:12px;">@<?= e($u['username']) ?></span></div>
              <div style="font-size:12px; color:var(--ink-600);"><?= e($u['jabatan']) ?> · <?= $u['role']==='admin' ? 'Admin' : 'Pengurus' ?></div>
            </div>
            <span class="status-pill <?= $u['aktif'] ? 'st-selesai' : 'st-tertunda' ?>"><span class="dot"></span><?= $u['aktif'] ? 'Aktif' : 'Nonaktif' ?></span>
            <a href="users_list.php?reset=<?= (int)$u['id'] ?>" class="btn btn-ghost btn-sm" onclick="return confirm('Reset password @<?= e($u['username']) ?> ke default (alfalah2026)?')">Reset PW</a>
            <?php if ((int)$u['id'] !== (int)$user['id']): ?>
              <a href="users_list.php?toggle=<?= (int)$u['id'] ?>" class="btn <?= $u['aktif'] ? 'btn-danger' : 'btn-primary' ?> btn-sm" onclick="return confirm('<?= $u['aktif'] ? 'Nonaktifkan' : 'Aktifkan' ?> akun @<?= e($u['username']) ?>?')">
                <?= $u['aktif'] ? 'Nonaktifkan' : 'Aktifkan' ?>
              </a>
            <?php endif; ?>
          </div>
        <?php endforeach; ?>
      </div>
    </div>

    <div class="form-card reveal">
      <div class="form-card-head"><h2 class="font-display" style="font-size:17px;">Tambah Pengguna Baru</h2></div>
      <?php if (!empty($errors)): ?>
        <div style="padding:0 26px;">
          <div class="alert alert-error"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M12 8v4M12 16h.01"/></svg><span><?= implode(' ', array_map('e', $errors)) ?></span></div>
        </div>
      <?php endif; ?>
      <form method="post">
        <div class="form-card-body">
          <div class="form-row">
            <div class="form-group"><label>Nama Lengkap</label><input type="text" name="nama_lengkap" required value="<?= e($_POST['nama_lengkap'] ?? '') ?>"></div>
            <div class="form-group"><label>Jabatan</label><input type="text" name="jabatan" placeholder="cth: Bendahara" value="<?= e($_POST['jabatan'] ?? '') ?>"></div>
          </div>
          <div class="form-row">
            <div class="form-group"><label>Username</label><input type="text" name="username" required value="<?= e($_POST['username'] ?? '') ?>"></div>
            <div class="form-group"><label>Peran</label>
              <select name="role">
                <option value="pengurus">Pengurus (biasa)</option>
                <option value="admin">Admin (bisa kelola pengguna)</option>
              </select>
            </div>
          </div>
          <div class="form-group">
            <label>Password Awal</label>
            <input type="password" name="password" required minlength="8" placeholder="Minimal 8 karakter">
          </div>
        </div>
        <div class="form-card-foot">
          <button type="submit" class="btn btn-primary">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 5v14M5 12h14"/></svg>
            Tambah Pengguna
          </button>
        </div>
      </form>
    </div>
  </main>
</div>
<script src="assets/js/app.js"></script>
</body>
</html>

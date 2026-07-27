<?php
require_once __DIR__ . '/includes/auth.php';

if (!empty($_SESSION['user_id'])) {
    redirect('index.php');
}

$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username === '' || $password === '') {
        $error = 'Username dan password wajib diisi.';
    } elseif (attempt_login($username, $password)) {
        redirect('index.php');
    } else {
        $error = 'Username atau password salah. Silakan coba lagi.';
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Masuk — Sistem Musyawarah Al-Falah Putak</title>
<link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
<div class="login-wrap">
  <svg class="star-svg" width="380" height="380" style="top:-100px; left:-120px; opacity:.08;" viewBox="0 0 100 100"><path d="M50 2 L61 39 L98 39 L68 61 L79 98 L50 75 L21 98 L32 61 L2 39 L39 39 Z" fill="none" stroke="#237059" stroke-width="0.5"/></svg>
  <svg class="star-svg" width="260" height="260" style="bottom:-90px; right:-70px; opacity:.08;" viewBox="0 0 100 100"><path d="M50 2 L61 39 L98 39 L68 61 L79 98 L50 75 L21 98 L32 61 L2 39 L39 39 Z" fill="none" stroke="#C9A227" stroke-width="0.5"/></svg>

  <div class="login-card">
    <div class="login-brand">
      <div class="brand-mark">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2 L14.5 9 L22 9 L16 13.5 L18 21 L12 16.5 L6 21 L8 13.5 L2 9 L9.5 9 Z"/></svg>
      </div>
      <h1>Sistem Musyawarah</h1>
      <p>Ponpes Al-Falah Putak — Khusus Pengurus</p>
    </div>

    <?php if ($error): ?>
      <div class="alert alert-error">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M12 8v4M12 16h.01"/></svg>
        <?= e($error) ?>
      </div>
    <?php endif; ?>

    <form method="post" novalidate>
      <div class="form-group">
        <label for="username">Username</label>
        <input type="text" id="username" name="username" placeholder="cth: sekretaris" required autofocus value="<?= e($_POST['username'] ?? '') ?>">
      </div>
      <div class="form-group">
        <label for="password">Password</label>
        <input type="password" id="password" name="password" placeholder="Masukkan password" required>
      </div>
      <button type="submit" class="btn btn-primary" style="width:100%; justify-content:center; margin-top:8px;">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M15 3h4a2 2 0 012 2v14a2 2 0 01-2 2h-4"/><path d="M10 17l5-5-5-5"/><path d="M15 12H3"/></svg>
        Masuk
      </button>
    </form>
  </div>
</div>
</body>
</html>

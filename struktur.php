<?php
require_once __DIR__ . '/includes/auth.php';
require_login();

$activePage = 'struktur';
$user = current_user();

$daftarPengurus = db()->query("
    SELECT p.*, b.nama_bidang, b.kode_warna
    FROM pengurus p
    LEFT JOIN bidang b ON b.id = p.bidang_id
    ORDER BY p.urutan ASC, p.nama ASC
")->fetchAll();

$flashSuccess = flash_get('success');
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Struktur Kepengurusan — Al-Falah Putak</title>
<link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
<div class="app">
  <?php include __DIR__ . '/includes/sidebar.php'; ?>

  <main class="main">
    <div class="topbar reveal">
      <div class="greeting">
        <h1 class="font-display">Struktur Kepengurusan</h1>
        <p>Referensi jabatan &amp; penanggung jawab tiap bidang</p>
      </div>
      <?php if ($user['role'] === 'admin'): ?>
        <a href="struktur_form.php" class="btn btn-primary">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 5v14M5 12h14"/></svg>
          <span class="full">Tambah Pengurus</span>
        </a>
      <?php endif; ?>
    </div>

    <?php if ($flashSuccess): ?>
      <div class="alert alert-success reveal"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 6L9 17l-5-5"/></svg><?= e($flashSuccess) ?></div>
    <?php endif; ?>

    <div class="rapat-grid">
      <?php if (empty($daftarPengurus)): ?>
        <div class="empty-state" style="grid-column:1/-1;">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
          <h3>Belum ada data pengurus</h3>
        </div>
      <?php else: foreach ($daftarPengurus as $p): ?>
        <div class="rapat-card reveal" style="cursor:default;">
          <div class="rapat-top">
            <div style="display:flex; align-items:center; gap:12px;">
              <?php if (!empty($p['foto']) && is_file(__DIR__ . '/' . $p['foto'])): ?>
                <img src="<?= e($p['foto']) ?>" alt="<?= e($p['nama']) ?>" class="pengurus-photo" style="width:44px;height:44px;">
              <?php else: ?>
                <div class="user-avatar" style="width:44px;height:44px;font-size:14px;"><?= e(inisial($p['nama'])) ?></div>
              <?php endif; ?>
              <div>
                <div class="rapat-title" style="font-size:15px;"><?= e($p['nama']) ?></div>
                <div style="font-size:12.5px; color:var(--ink-600); font-weight:600;"><?= e($p['jabatan']) ?></div>
              </div>
            </div>
            <?php if ($user['role'] === 'admin'): ?>
              <a href="struktur_form.php?id=<?= (int)$p['id'] ?>" class="btn btn-ghost btn-sm" style="flex-shrink:0;">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/><path d="M18.5 2.5a2.12 2.12 0 013 3L12 15l-4 1 1-4z"/></svg>
              </a>
            <?php endif; ?>
          </div>
          <div class="rapat-meta">
            <?php if ($p['nama_bidang']): ?>
              <span class="bidang-tag <?= bidang_tag_class($p['kode_warna']) ?>"><?= e($p['nama_bidang']) ?></span>
            <?php endif; ?>
          </div>
          <?php if ($p['kontak'] && $p['kontak'] !== '-'): ?>
            <div class="rapat-foot">
              <span class="pj-text"><svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align:-2px; margin-right:3px;"><path d="M22 16.92v3a2 2 0 01-2.18 2 19.79 19.79 0 01-8.63-3.07 19.5 19.5 0 01-6-6 19.79 19.79 0 01-3.07-8.67A2 2 0 014.11 2h3a2 2 0 012 1.72c.127.96.361 1.903.7 2.81a2 2 0 01-.45 2.11L8.09 9.91a16 16 0 006 6l1.27-1.27a2 2 0 012.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0122 16.92z"/></svg><?= e($p['kontak']) ?></span>
            </div>
          <?php endif; ?>
        </div>
      <?php endforeach; endif; ?>
    </div>
  </main>
</div>
<script src="assets/js/app.js"></script>
</body>
</html>
<?php
require_once __DIR__ . '/includes/auth.php';
require_login();

$activePage = 'dashboard';
$user = current_user();

// ==== Statistik ====
$bulanIni = date('Y-m');
$stmt = db()->prepare("SELECT COUNT(*) AS n FROM musyawarah WHERE DATE_FORMAT(tanggal, '%Y-%m') = ?");
$stmt->execute([$bulanIni]);
$rapatBulanIni = $stmt->fetch()['n'];

$totalSelesai = db()->query("SELECT COUNT(*) AS n FROM musyawarah WHERE status='selesai'")->fetch()['n'];
$totalProses = db()->query("SELECT COUNT(*) AS n FROM musyawarah WHERE status='proses'")->fetch()['n'];
$totalTertunda = db()->query("SELECT COUNT(*) AS n FROM musyawarah WHERE status='tertunda'")->fetch()['n'];

// ==== Rapat terbaru (5 teratas) ====
$rapatTerbaru = db()->query("
    SELECT m.*, b.nama_bidang, b.kode_warna
    FROM musyawarah m
    JOIN bidang b ON b.id = m.bidang_id
    ORDER BY m.tanggal DESC, m.id DESC
    LIMIT 6
")->fetchAll();

$flashSuccess = flash_get('success');
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Dashboard — Sistem Musyawarah Al-Falah Putak</title>
<link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
<div class="app">
  <?php include __DIR__ . '/includes/sidebar.php'; ?>

  <main class="main">
    <div class="topbar reveal">
      <div class="greeting">
        <h1 class="font-display">Assalamu'alaikum, <?= e(explode(' ', $user['nama_lengkap'])[0]) ?></h1>
        <p>
          <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/></svg>
          <?= tgl_indo(date('Y-m-d')) ?> &nbsp;•&nbsp; <?= (int)$totalTertunda ?> keputusan menunggu tindak lanjut
        </p>
      </div>
      <div class="topbar-actions">
        <form action="rapat_list.php" method="get" class="search-box">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><path d="M21 21l-4.3-4.3"/></svg>
          <input type="text" name="q" placeholder="Cari notulen, bidang, tanggal…">
        </form>
        <a href="rapat_form.php" class="btn btn-primary">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 5v14M5 12h14"/></svg>
          <span class="full">Catat Rapat</span>
        </a>
      </div>
    </div>

    <?php if ($flashSuccess): ?>
      <div class="alert alert-success reveal">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 6L9 17l-5-5"/></svg>
        <?= e($flashSuccess) ?>
      </div>
    <?php endif; ?>

    <div class="stat-grid">
      <div class="stat-card reveal">
        <div class="stat-top"><div class="stat-icon i1"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><path d="M14 2v6h6"/></svg></div></div>
        <div class="stat-value" data-count="<?= (int)$rapatBulanIni ?>">0</div>
        <div class="stat-label">Rapat bulan ini</div>
      </div>
      <div class="stat-card reveal">
        <div class="stat-top"><div class="stat-icon i3"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 6L9 17l-5-5"/></svg></div></div>
        <div class="stat-value" data-count="<?= (int)$totalSelesai ?>">0</div>
        <div class="stat-label">Keputusan selesai</div>
      </div>
      <div class="stat-card reveal">
        <div class="stat-top"><div class="stat-icon i2"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/></svg></div></div>
        <div class="stat-value" data-count="<?= (int)$totalProses ?>">0</div>
        <div class="stat-label">Sedang diproses</div>
      </div>
      <div class="stat-card reveal">
        <div class="stat-top"><div class="stat-icon i4"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 9v4M12 17h.01"/><circle cx="12" cy="12" r="10"/></svg></div></div>
        <div class="stat-value" data-count="<?= (int)$totalTertunda ?>">0</div>
        <div class="stat-label">Perlu tindak lanjut</div>
      </div>
    </div>

    <div class="section-head reveal">
      <h2 class="font-display">Hasil Musyawarah Terbaru</h2>
      <a class="link-more" href="rapat_list.php">Lihat semua <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M9 18l6-6-6-6"/></svg></a>
    </div>

    <div class="rapat-grid">
      <?php if (empty($rapatTerbaru)): ?>
        <div class="empty-state" style="grid-column:1/-1;">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><path d="M14 2v6h6"/></svg>
          <h3>Belum ada hasil musyawarah</h3>
          <p>Mulai catat hasil rapat pertama Anda.</p>
        </div>
      <?php else: foreach ($rapatTerbaru as $r): ?>
        <a href="rapat_detail.php?id=<?= (int)$r['id'] ?>" class="rapat-card reveal">
          <div class="rapat-top">
            <span class="bidang-tag <?= bidang_tag_class($r['kode_warna']) ?>"><?= e($r['nama_bidang']) ?></span>
            <span class="status-pill <?= status_class($r['status']) ?>"><span class="dot"></span><?= status_label($r['status']) ?></span>
          </div>
          <div class="rapat-title"><?= e($r['judul']) ?></div>
          <div class="rapat-meta">
            <span><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/></svg><?= tgl_pendek($r['tanggal']) ?></span>
          </div>
          <div class="rapat-foot">
            <span class="pj-text">PJ: <?= e($r['penanggung_jawab'] ?: '-') ?></span>
          </div>
        </a>
      <?php endforeach; endif; ?>
    </div>
  </main>
</div>
<script src="assets/js/app.js"></script>
</body>
</html>

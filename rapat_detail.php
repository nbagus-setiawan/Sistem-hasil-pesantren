<?php
require_once __DIR__ . '/includes/auth.php';
require_login();

$activePage = 'rapat';
$user = current_user();
$id = (int)($_GET['id'] ?? 0);

$stmt = db()->prepare("SELECT m.*, b.nama_bidang, b.kode_warna FROM musyawarah m JOIN bidang b ON b.id=m.bidang_id WHERE m.id=?");
$stmt->execute([$id]);
$rapat = $stmt->fetch();

if (!$rapat) {
    redirect('rapat_list.php');
}

$stmtL = db()->prepare("SELECT * FROM lampiran WHERE musyawarah_id=? ORDER BY uploaded_at DESC");
$stmtL->execute([$id]);
$lampiranList = $stmtL->fetchAll();

$flashSuccess = flash_get('success');
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= e($rapat['judul']) ?> — Al-Falah Putak</title>
<link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
<div class="app">
  <?php include __DIR__ . '/includes/sidebar.php'; ?>

  <main class="main">
    <div class="topbar reveal no-print">
      <div class="greeting">
        <h1 class="font-display" style="font-size:20px;">Detail Hasil Musyawarah</h1>
      </div>
      <div class="topbar-actions">
        <a href="rapat_list.php" class="btn btn-ghost">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 12H5M12 19l-7-7 7-7"/></svg>
          Kembali
        </a>
        <button onclick="window.print()" class="btn btn-ghost">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 9V2h12v7"/><path d="M6 18H4a2 2 0 01-2-2v-5a2 2 0 012-2h16a2 2 0 012 2v5a2 2 0 01-2 2h-2"/><path d="M6 14h12v8H6z"/></svg>
          Cetak
        </button>
        <a href="rapat_form.php?id=<?= (int)$rapat['id'] ?>" class="btn btn-primary">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/><path d="M18.5 2.5a2.12 2.12 0 013 3L12 15l-4 1 1-4z"/></svg>
          <span class="full">Ubah</span>
        </a>
      </div>
    </div>

    <?php if ($flashSuccess): ?>
      <div class="alert alert-success reveal no-print">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 6L9 17l-5-5"/></svg>
        <?= e($flashSuccess) ?>
      </div>
    <?php endif; ?>

    <div class="form-card reveal">
      <div class="form-card-head" style="display:flex; align-items:flex-start; justify-content:space-between; gap:12px; flex-wrap:wrap;">
        <div>
          <div style="display:flex; gap:8px; margin-bottom:10px; flex-wrap:wrap;">
            <span class="bidang-tag <?= bidang_tag_class($rapat['kode_warna']) ?>"><?= e($rapat['nama_bidang']) ?></span>
            <span class="status-pill <?= status_class($rapat['status']) ?>"><span class="dot"></span><?= status_label($rapat['status']) ?></span>
          </div>
          <h2 class="font-display" style="font-size:20px;"><?= e($rapat['judul']) ?></h2>
        </div>
      </div>

      <div class="form-card-body">
        <div class="form-row" style="margin-bottom:18px;">
          <div>
            <div style="font-size:11px; font-weight:800; text-transform:uppercase; color:var(--slate-400); margin-bottom:4px;">Tanggal</div>
            <div style="font-weight:700;"><?= tgl_indo($rapat['tanggal']) ?></div>
          </div>
          <div>
            <div style="font-size:11px; font-weight:800; text-transform:uppercase; color:var(--slate-400); margin-bottom:4px;">Penanggung Jawab</div>
            <div style="font-weight:700;"><?= e($rapat['penanggung_jawab'] ?: '-') ?></div>
          </div>
        </div>

        <div class="form-section">
          <div class="form-section-label">Peserta Rapat</div>
          <p style="font-size:14px; color:var(--ink-600); line-height:1.6;"><?= nl2br(e($rapat['peserta'] ?: '-')) ?></p>
        </div>

        <div class="form-section">
          <div class="form-section-label">Agenda &amp; Keputusan</div>
          <p style="font-size:14px; line-height:1.7; white-space:pre-line;"><?= e($rapat['agenda_keputusan']) ?></p>
        </div>

        <div class="form-section no-print">
          <div class="form-section-label">Lampiran</div>
          <?php if (empty($lampiranList)): ?>
            <p style="font-size:13px; color:var(--slate-400);">Belum ada lampiran diunggah.</p>
          <?php else: foreach ($lampiranList as $l): ?>
            <div class="lampiran-item">
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="color:var(--emerald-700); flex-shrink:0;"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><path d="M14 2v6h6"/></svg>
              <a href="<?= e($l['path_file']) ?>" target="_blank"><?= e($l['nama_file']) ?></a>
              <span style="margin-left:auto; font-size:11.5px; color:var(--slate-400);"><?= tgl_pendek($l['uploaded_at']) ?></span>
            </div>
          <?php endforeach; endif; ?>
        </div>

        <div class="form-section no-print" style="border-top:1px solid var(--line); padding-top:16px; margin-top:24px;">
          <form action="rapat_delete.php" method="post" class="confirm-delete">
            <input type="hidden" name="id" value="<?= (int)$rapat['id'] ?>">
            <button type="submit" class="btn btn-danger btn-sm">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 6h18M8 6V4a2 2 0 012-2h4a2 2 0 012 2v2m3 0v14a2 2 0 01-2 2H7a2 2 0 01-2-2V6h14z"/></svg>
              Hapus Hasil Rapat Ini
            </button>
          </form>
        </div>
      </div>
    </div>
  </main>
</div>
<script src="assets/js/app.js"></script>
</body>
</html>

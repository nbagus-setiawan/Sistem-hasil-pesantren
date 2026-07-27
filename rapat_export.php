<?php
require_once __DIR__ . '/includes/auth.php';
require_login();

// Terima daftar ID lewat POST (dari pilihan checkbox) atau lewat filter GET (export semua hasil filter)
$ids = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['ids'])) {
    $ids = array_map('intval', $_POST['ids']);
}

if (!empty($ids)) {
    $placeholders = implode(',', array_fill(0, count($ids), '?'));
    $stmt = db()->prepare("
        SELECT m.*, b.nama_bidang, b.kode_warna
        FROM musyawarah m JOIN bidang b ON b.id = m.bidang_id
        WHERE m.id IN ($placeholders)
        ORDER BY m.tanggal ASC, m.id ASC
    ");
    $stmt->execute($ids);
} else {
    // Export berdasarkan filter yang sedang aktif di rapat_list.php
    $q = trim($_GET['q'] ?? '');
    $bidangFilter = isset($_GET['bidang']) ? (int)$_GET['bidang'] : 0;
    $statusFilter = $_GET['status'] ?? '';

    $sql = "SELECT m.*, b.nama_bidang, b.kode_warna FROM musyawarah m JOIN bidang b ON b.id=m.bidang_id WHERE 1=1";
    $params = [];
    if ($q !== '') { $sql .= " AND (m.judul LIKE ? OR m.agenda_keputusan LIKE ? OR m.penanggung_jawab LIKE ?)"; $like='%'.$q.'%'; $params[]=$like;$params[]=$like;$params[]=$like; }
    if ($bidangFilter > 0) { $sql .= " AND m.bidang_id = ?"; $params[] = $bidangFilter; }
    if (in_array($statusFilter, ['proses','selesai','tertunda'], true)) { $sql .= " AND m.status = ?"; $params[] = $statusFilter; }
    $sql .= " ORDER BY m.tanggal ASC, m.id ASC";
    $stmt = db()->prepare($sql);
    $stmt->execute($params);
}

$daftar = $stmt->fetchAll();

$warnaBidangMap = [
    'pendidikan' => ['#DCEAE3', '#1B5E4A'],
    'sarana'     => ['#E7EEFB', '#2E5AAC'],
    'kesantrian' => ['#F3E8FB', '#7A3FB0'],
    'keuangan'   => ['#FBF0DD', '#96660D'],
];
$warnaStatusMap = [
    'selesai'  => ['#E4F3E9', '#1E7A46'],
    'proses'   => ['#FBF0DD', '#96660D'],
    'tertunda' => ['#FBE7E7', '#A32C2C'],
];
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Export Hasil Musyawarah — Al-Falah Putak</title>
<style>
  @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@600;700&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap');
  *{box-sizing:border-box;}
  body{font-family:'Plus Jakarta Sans', sans-serif; color:#1C2620; max-width:800px; margin:0 auto; padding:36px 28px; line-height:1.55; background:#FAF8F3;}
  .toolbar{display:flex; justify-content:space-between; align-items:center; margin-bottom:24px; position:sticky; top:0; background:#FAF8F3; padding:10px 0; z-index:5;}
  .toolbar button{font-family:inherit; font-weight:700; font-size:13.5px; background:linear-gradient(135deg,#237059,#1B5E4A); color:#fff; border:none; padding:10px 18px; border-radius:10px; cursor:pointer; box-shadow:0 4px 14px rgba(27,94,74,0.28); transition:filter .2s ease, transform .2s ease;}
  .toolbar button:hover{filter:brightness(1.06); transform:translateY(-1px);}
  .toolbar a{font-family:inherit; font-weight:700; font-size:13.5px; color:#4A554E; text-decoration:none; display:inline-flex; align-items:center; gap:6px;}
  .toolbar a:hover{color:#1B5E4A;}
  .letterhead{text-align:center; border-bottom:3px double #1B5E4A; padding-bottom:16px; margin-bottom:26px; background:#fff; border-radius:16px 16px 0 0; padding-top:20px;}
  .letterhead h1{font-family:'Poppins',sans-serif; font-size:19px; color:#0B2E22; margin-bottom:2px;}
  .letterhead p{font-size:12px; color:#4A554E;}
  .meta-export{font-size:11.5px; color:#6E786F; text-align:right; margin-bottom:20px;}
  .sheet{background:#fff; border:1px solid #E4E0D3; border-radius:16px; padding:0 26px 8px; box-shadow:0 8px 24px rgba(11,46,34,0.06);}
  .entry{margin-bottom:26px; padding-bottom:22px; border-bottom:1px solid #E4E0D3; page-break-inside:avoid;}
  .entry:last-child{border-bottom:none; margin-bottom:0;}
  .entry-head{display:flex; justify-content:space-between; align-items:flex-start; gap:10px; margin-bottom:8px;}
  .entry-title{font-family:'Poppins',sans-serif; font-size:15px; font-weight:700;}
  .badge{font-size:10.5px; font-weight:800; padding:3px 9px; border-radius:12px; text-transform:uppercase; white-space:nowrap;}
  .entry-meta{font-size:12px; color:#4A554E; margin-bottom:8px;}
  .entry-body{font-size:13px; white-space:pre-line;}
  .entry-label{font-size:10.5px; font-weight:800; text-transform:uppercase; color:#C9A227; margin-bottom:4px; margin-top:10px;}
  .empty{text-align:center; padding:60px 0; color:#6E786F;}
  @media print{
    .toolbar{display:none;}
    body{padding:0; background:#fff;}
    .sheet{box-shadow:none; border:none; border-radius:0;}
    .letterhead{border-radius:0;}
  }
</style>
</head>
<body>
  <div class="toolbar">
    <a href="rapat_list.php">&larr; Kembali ke Arsip</a>
    <button onclick="window.print()">Simpan sebagai PDF / Cetak</button>
  </div>

  <div class="sheet">
    <div class="letterhead">
      <h1>Rekap Hasil Musyawarah Kepengurusan</h1>
      <p>Pondok Pesantren Al-Falah Putak</p>
    </div>

    <div class="meta-export">
      Diekspor pada <?= tgl_indo(date('Y-m-d')) ?> · <?= count($daftar) ?> hasil musyawarah
    </div>

    <?php if (empty($daftar)): ?>
      <div class="empty">Tidak ada data untuk diekspor.</div>
    <?php else: foreach ($daftar as $i => $r):
      [$bidangBg, $bidangFg] = $warnaBidangMap[$r['kode_warna']] ?? $warnaBidangMap['pendidikan'];
      [$statusBg, $statusFg] = $warnaStatusMap[$r['status']] ?? $warnaStatusMap['proses'];
    ?>
      <div class="entry">
        <div class="entry-head">
          <div class="entry-title"><?= ($i+1) ?>. <?= e($r['judul']) ?></div>
          <div style="display:flex; gap:6px; flex-shrink:0;">
            <span class="badge" style="background:<?= $bidangBg ?>; color:<?= $bidangFg ?>;"><?= e($r['nama_bidang']) ?></span>
            <span class="badge" style="background:<?= $statusBg ?>; color:<?= $statusFg ?>;"><?= status_label($r['status']) ?></span>
          </div>
        </div>
        <div class="entry-meta">
          <?= tgl_indo($r['tanggal']) ?> &nbsp;·&nbsp; PJ: <?= e($r['penanggung_jawab'] ?: '-') ?>
          <?php if ($r['peserta']): ?>&nbsp;·&nbsp; Peserta: <?= e($r['peserta']) ?><?php endif; ?>
        </div>
        <div class="entry-label">Agenda &amp; Keputusan</div>
        <div class="entry-body"><?= e($r['agenda_keputusan']) ?></div>
      </div>
    <?php endforeach; endif; ?>
  </div>

</body>
</html>
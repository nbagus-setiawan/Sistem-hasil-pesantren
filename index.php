<?php
require_once __DIR__ . '/includes/auth.php';
require_login();

$activePage = 'dashboard';
$user = current_user();

// ==== Statistik ringkasan ====
$bulanIni = date('Y-m');
$stmt = db()->prepare("SELECT COUNT(*) AS n FROM musyawarah WHERE DATE_FORMAT(tanggal, '%Y-%m') = ?");
$stmt->execute([$bulanIni]);
$rapatBulanIni = $stmt->fetch()['n'];

$totalSelesai = db()->query("SELECT COUNT(*) AS n FROM musyawarah WHERE status='selesai'")->fetch()['n'];
$totalProses = db()->query("SELECT COUNT(*) AS n FROM musyawarah WHERE status='proses'")->fetch()['n'];
$totalTertunda = db()->query("SELECT COUNT(*) AS n FROM musyawarah WHERE status='tertunda'")->fetch()['n'];

// ==== Grafik: jumlah rapat per bulan (6 bulan terakhir) ====
$stmtBulan = db()->prepare("
    SELECT DATE_FORMAT(tanggal, '%Y-%m') AS ym, COUNT(*) AS n
    FROM musyawarah
    WHERE tanggal >= DATE_SUB(DATE_FORMAT(CURDATE(), '%Y-%m-01'), INTERVAL 5 MONTH)
    GROUP BY ym
");
$stmtBulan->execute();
$rawBulan = [];
foreach ($stmtBulan->fetchAll() as $row) {
    $rawBulan[$row['ym']] = (int)$row['n'];
}

$namaBulanSingkat = ['', 'Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des'];
$labelBulan = [];
$dataBulan = [];
for ($i = 5; $i >= 0; $i--) {
    $ts = strtotime("-{$i} months", strtotime(date('Y-m-01')));
    $key = date('Y-m', $ts);
    $labelBulan[] = $namaBulanSingkat[(int)date('n', $ts)] . ' ' . date('y', $ts);
    $dataBulan[] = $rawBulan[$key] ?? 0;
}

// ==== Grafik: jumlah rapat per bidang ====
$daftarBidangStat = db()->query("
    SELECT b.nama_bidang, b.kode_warna, COUNT(m.id) AS n
    FROM bidang b
    LEFT JOIN musyawarah m ON m.bidang_id = b.id
    GROUP BY b.id
    ORDER BY b.id
")->fetchAll();

$warnaBidangMap = [
    'pendidikan' => '#1B5E4A',
    'sarana'     => '#2E5AAC',
    'kesantrian' => '#7A3FB0',
    'keuangan'   => '#96660D',
];
$labelBidang = [];
$dataBidang = [];
$warnaBidang = [];
foreach ($daftarBidangStat as $b) {
    $labelBidang[] = $b['nama_bidang'];
    $dataBidang[] = (int)$b['n'];
    $warnaBidang[] = $warnaBidangMap[$b['kode_warna']] ?? '#1B5E4A';
}
$adaDataBidang = array_sum($dataBidang) > 0;

// ==== Rapat terbaru (6 teratas) ====
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
      <h2 class="font-display">Statistik Musyawarah</h2>
    </div>
    <div class="chart-grid reveal">
      <div class="chart-card">
        <div class="chart-card-title">Jumlah Rapat per Bulan</div>
        <div class="chart-card-wrap"><canvas id="chartBulan"></canvas></div>
      </div>
      <div class="chart-card">
        <div class="chart-card-title">Sebaran Rapat per Bidang</div>
        <div class="chart-card-wrap">
          <?php if ($adaDataBidang): ?>
            <canvas id="chartBidang"></canvas>
          <?php else: ?>
            <div class="empty-state" style="padding:24px 10px;">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="12" cy="12" r="9"/><path d="M12 8v4M12 16h.01"/></svg>
              <h3 style="font-size:13.5px;">Belum ada data rapat</h3>
            </div>
          <?php endif; ?>
        </div>
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
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.4/chart.umd.min.js"></script>
<script>
(function(){
  const labelBulan = <?= json_encode($labelBulan, JSON_UNESCAPED_UNICODE) ?>;
  const dataBulan = <?= json_encode($dataBulan) ?>;

  const elBulan = document.getElementById('chartBulan');
  if (elBulan && window.Chart) {
    new Chart(elBulan, {
      type: 'bar',
      data: {
        labels: labelBulan,
        datasets: [{
          label: 'Jumlah Rapat',
          data: dataBulan,
          backgroundColor: '#1B5E4A',
          borderRadius: 8,
          maxBarThickness: 34
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: { legend: { display: false } },
        scales: {
          y: { beginAtZero: true, ticks: { stepSize: 1, precision: 0 }, grid: { color: '#E4E0D3' } },
          x: { grid: { display: false } }
        }
      }
    });
  }

  <?php if ($adaDataBidang): ?>
  const labelBidang = <?= json_encode($labelBidang, JSON_UNESCAPED_UNICODE) ?>;
  const dataBidang = <?= json_encode($dataBidang) ?>;
  const warnaBidang = <?= json_encode($warnaBidang) ?>;

  const elBidang = document.getElementById('chartBidang');
  if (elBidang && window.Chart) {
    new Chart(elBidang, {
      type: 'doughnut',
      data: {
        labels: labelBidang,
        datasets: [{ data: dataBidang, backgroundColor: warnaBidang, borderWidth: 0 }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        cutout: '62%',
        plugins: { legend: { position: 'bottom', labels: { boxWidth: 10, font: { size: 11 }, padding: 14 } } }
      }
    });
  }
  <?php endif; ?>
})();
</script>
</body>
</html>
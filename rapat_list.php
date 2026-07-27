<?php
require_once __DIR__ . '/includes/auth.php';
require_login();

$activePage = 'rapat';
$user = current_user();

$q = trim($_GET['q'] ?? '');
$bidangFilter = isset($_GET['bidang']) ? (int)$_GET['bidang'] : 0;
$statusFilter = $_GET['status'] ?? '';

$bidangList = db()->query("SELECT * FROM bidang ORDER BY id")->fetchAll();

$sql = "SELECT m.*, b.nama_bidang, b.kode_warna
        FROM musyawarah m JOIN bidang b ON b.id = m.bidang_id
        WHERE 1=1";
$params = [];

if ($q !== '') {
    $sql .= " AND (m.judul LIKE ? OR m.agenda_keputusan LIKE ? OR m.penanggung_jawab LIKE ?)";
    $like = '%' . $q . '%';
    $params[] = $like; $params[] = $like; $params[] = $like;
}
if ($bidangFilter > 0) {
    $sql .= " AND m.bidang_id = ?";
    $params[] = $bidangFilter;
}
if (in_array($statusFilter, ['proses','selesai','tertunda'], true)) {
    $sql .= " AND m.status = ?";
    $params[] = $statusFilter;
}
$sql .= " ORDER BY m.tanggal DESC, m.id DESC";

$stmt = db()->prepare($sql);
$stmt->execute($params);
$daftar = $stmt->fetchAll();

function qs(array $override): string {
    $base = ['q' => $_GET['q'] ?? '', 'bidang' => $_GET['bidang'] ?? '', 'status' => $_GET['status'] ?? ''];
    return http_build_query(array_merge($base, $override));
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Arsip Hasil Musyawarah — Al-Falah Putak</title>
<link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
<div class="app">
  <?php include __DIR__ . '/includes/sidebar.php'; ?>

  <main class="main">
    <div class="topbar reveal">
      <div class="greeting">
        <h1 class="font-display">Arsip Hasil Musyawarah</h1>
        <p><?= count($daftar) ?> hasil ditemukan</p>
      </div>
      <div class="topbar-actions">
        <form action="rapat_list.php" method="get" class="search-box">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><path d="M21 21l-4.3-4.3"/></svg>
          <input type="text" name="q" value="<?= e($q) ?>" placeholder="Cari notulen, penanggung jawab…">
          <?php if ($bidangFilter): ?><input type="hidden" name="bidang" value="<?= (int)$bidangFilter ?>"><?php endif; ?>
          <?php if ($statusFilter): ?><input type="hidden" name="status" value="<?= e($statusFilter) ?>"><?php endif; ?>
        </form>
        <a href="rapat_export.php?<?= qs([]) ?>" class="btn btn-ghost" title="Export semua hasil yang tampil saat ini">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/><path d="M7 10l5 5 5-5"/><path d="M12 15V3"/></svg>
          <span class="full">Export PDF</span>
        </a>
        <button type="button" class="btn btn-ghost" id="toggleSelectBtn" onclick="toggleSelectMode()">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 11l3 3L22 4"/><path d="M21 12v7a2 2 0 01-2 2H5a2 2 0 01-2-2V5a2 2 0 012-2h11"/></svg>
          <span class="full">Pilih Beberapa</span>
        </button>
        <a href="rapat_form.php" class="btn btn-primary">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 5v14M5 12h14"/></svg>
          <span class="full">Catat Rapat</span>
        </a>
      </div>
    </div>

    <div class="filter-row reveal">
      <a href="rapat_list.php?<?= qs(['bidang'=>'']) ?>" class="chip <?= $bidangFilter===0?'active':'' ?>">Semua Bidang</a>
      <?php foreach ($bidangList as $b): ?>
        <a href="rapat_list.php?<?= qs(['bidang'=>$b['id']]) ?>" class="chip <?= $bidangFilter===(int)$b['id']?'active':'' ?>">
          <?= e($b['nama_bidang']) ?>
        </a>
      <?php endforeach; ?>
    </div>
    <div class="filter-row reveal">
      <a href="rapat_list.php?<?= qs(['status'=>'']) ?>" class="chip <?= $statusFilter===''?'active':'' ?>">Semua Status</a>
      <a href="rapat_list.php?<?= qs(['status'=>'proses']) ?>" class="chip <?= $statusFilter==='proses'?'active':'' ?>"><span class="dot" style="background:#B7791F;"></span>Proses</a>
      <a href="rapat_list.php?<?= qs(['status'=>'selesai']) ?>" class="chip <?= $statusFilter==='selesai'?'active':'' ?>"><span class="dot" style="background:#2F855A;"></span>Selesai</a>
      <a href="rapat_list.php?<?= qs(['status'=>'tertunda']) ?>" class="chip <?= $statusFilter==='tertunda'?'active':'' ?>"><span class="dot" style="background:#C53030;"></span>Tertunda</a>
    </div>

    <form method="post" action="rapat_export.php" id="exportForm">
    <div class="rapat-grid" id="rapatGrid">
      <?php if (empty($daftar)): ?>
        <div class="empty-state" style="grid-column:1/-1;">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="11" cy="11" r="8"/><path d="M21 21l-4.3-4.3"/></svg>
          <h3>Tidak ada hasil yang cocok</h3>
          <p>Coba ubah kata kunci atau filter pencarian.</p>
        </div>
      <?php else: foreach ($daftar as $r): ?>
        <div class="rapat-card reveal" onclick="cardClick(event, <?= (int)$r['id'] ?>)" data-id="<?= (int)$r['id'] ?>">
          <input type="checkbox" name="ids[]" value="<?= (int)$r['id'] ?>" class="rapat-check" onclick="event.stopPropagation(); updateExportBar();">
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
        </div>
      <?php endforeach; endif; ?>
    </div>

    <div class="export-bar" id="exportBar">
      <span id="exportCount">0 dipilih</span>
      <button type="submit" class="btn btn-primary btn-sm">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/><path d="M7 10l5 5 5-5"/><path d="M12 15V3"/></svg>
        Export Terpilih ke PDF
      </button>
    </div>
    </form>
  </main>
</div>
<script src="assets/js/app.js"></script>
<script>
  let selectMode = false;
  function toggleSelectMode(){
    selectMode = !selectMode;
    document.getElementById('rapatGrid').classList.toggle('select-mode', selectMode);
    document.getElementById('toggleSelectBtn').classList.toggle('active-toggle', selectMode);
    if(!selectMode){
      document.querySelectorAll('.rapat-check').forEach(c => c.checked = false);
      updateExportBar();
    }
  }
  function cardClick(e, id){
    if(selectMode){
      const cb = e.currentTarget.querySelector('.rapat-check');
      cb.checked = !cb.checked;
      updateExportBar();
    } else {
      window.location.href = 'rapat_detail.php?id=' + id;
    }
  }
  function updateExportBar(){
    const n = document.querySelectorAll('.rapat-check:checked').length;
    document.getElementById('exportCount').textContent = n + ' dipilih';
    document.getElementById('exportBar').classList.toggle('show', n > 0);
  }
</script>
</body>
</html>

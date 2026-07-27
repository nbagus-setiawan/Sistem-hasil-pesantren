<?php
require_once __DIR__ . '/includes/auth.php';
require_admin();

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$isEdit = $id > 0;
$bidangList = db()->query("SELECT * FROM bidang ORDER BY id")->fetchAll();

$data = ['nama' => '', 'jabatan' => '', 'bidang_id' => '', 'kontak' => '', 'urutan' => 0];

if ($isEdit) {
    $stmt = db()->prepare("SELECT * FROM pengurus WHERE id = ?");
    $stmt->execute([$id]);
    $existing = $stmt->fetch();
    if (!$existing) redirect('struktur.php');
    $data = $existing;
}

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data['nama'] = trim($_POST['nama'] ?? '');
    $data['jabatan'] = trim($_POST['jabatan'] ?? '');
    $data['bidang_id'] = $_POST['bidang_id'] !== '' ? (int)$_POST['bidang_id'] : null;
    $data['kontak'] = trim($_POST['kontak'] ?? '');
    $data['urutan'] = (int)($_POST['urutan'] ?? 0);

    if ($data['nama'] === '') $errors[] = 'Nama wajib diisi.';
    if ($data['jabatan'] === '') $errors[] = 'Jabatan wajib diisi.';

    if (empty($errors)) {
        if ($isEdit) {
            db()->prepare("UPDATE pengurus SET nama=?, jabatan=?, bidang_id=?, kontak=?, urutan=? WHERE id=?")
                ->execute([$data['nama'], $data['jabatan'], $data['bidang_id'], $data['kontak'], $data['urutan'], $id]);
        } else {
            db()->prepare("INSERT INTO pengurus (nama, jabatan, bidang_id, kontak, urutan) VALUES (?,?,?,?,?)")
                ->execute([$data['nama'], $data['jabatan'], $data['bidang_id'], $data['kontak'], $data['urutan']]);
        }
        flash_set('success', 'Data pengurus berhasil disimpan.');
        redirect('struktur.php');
    }
}

$activePage = 'struktur';
$user = current_user();
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= $isEdit ? 'Ubah' : 'Tambah' ?> Pengurus — Al-Falah Putak</title>
<link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
<div class="app">
  <?php include __DIR__ . '/includes/sidebar.php'; ?>

  <main class="main">
    <div class="topbar reveal">
      <div class="greeting"><h1 class="font-display"><?= $isEdit ? 'Ubah' : 'Tambah' ?> Data Pengurus</h1></div>
      <a href="struktur.php" class="btn btn-ghost">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 12H5M12 19l-7-7 7-7"/></svg>
        Kembali
      </a>
    </div>

    <div class="form-card reveal">
      <?php if (!empty($errors)): ?>
        <div style="padding:20px 26px 0;">
          <div class="alert alert-error"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M12 8v4M12 16h.01"/></svg><span><?= implode(' ', array_map('e', $errors)) ?></span></div>
        </div>
      <?php endif; ?>
      <form method="post">
        <div class="form-card-body">
          <div class="form-row">
            <div class="form-group"><label>Nama</label><input type="text" name="nama" required value="<?= e($data['nama']) ?>"></div>
            <div class="form-group"><label>Jabatan</label><input type="text" name="jabatan" required placeholder="cth: Kepala Bidang Pendidikan" value="<?= e($data['jabatan']) ?>"></div>
          </div>
          <div class="form-row">
            <div class="form-group">
              <label>Bidang (opsional)</label>
              <select name="bidang_id">
                <option value="">— Tidak terikat bidang —</option>
                <?php foreach ($bidangList as $b): ?>
                  <option value="<?= (int)$b['id'] ?>" <?= (int)($data['bidang_id'] ?? 0) === (int)$b['id'] ? 'selected' : '' ?>><?= e($b['nama_bidang']) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="form-group"><label>Kontak (opsional)</label><input type="text" name="kontak" placeholder="No. WA/telepon" value="<?= e($data['kontak']) ?>"></div>
          </div>
          <div class="form-group" style="max-width:160px;">
            <label>Urutan Tampil</label>
            <input type="number" name="urutan" value="<?= (int)$data['urutan'] ?>">
          </div>
        </div>
        <div class="form-card-foot">
          <a href="struktur.php" class="btn btn-ghost">Batal</a>
          <button type="submit" class="btn btn-primary">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M20 6L9 17l-5-5"/></svg>
            Simpan
          </button>
        </div>
      </form>
    </div>
  </main>
</div>
<script src="assets/js/app.js"></script>
</body>
</html>

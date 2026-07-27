<?php
require_once __DIR__ . '/includes/auth.php';
require_admin();

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$isEdit = $id > 0;
$bidangList = db()->query("SELECT * FROM bidang ORDER BY id")->fetchAll();

$data = ['nama' => '', 'jabatan' => '', 'bidang_id' => '', 'kontak' => '', 'urutan' => 0, 'foto' => null];

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
            $pengurusId = $id;
        } else {
            db()->prepare("INSERT INTO pengurus (nama, jabatan, bidang_id, kontak, urutan) VALUES (?,?,?,?,?)")
                ->execute([$data['nama'], $data['jabatan'], $data['bidang_id'], $data['kontak'], $data['urutan']]);
            $pengurusId = db()->lastInsertId();
        }

        // Hapus foto lama jika diminta (checkbox "hapus foto saat ini")
        if (!empty($_POST['hapus_foto']) && !empty($data['foto'])) {
            $fileLama = __DIR__ . '/' . $data['foto'];
            if (is_file($fileLama)) @unlink($fileLama);
            db()->prepare("UPDATE pengurus SET foto = NULL WHERE id = ?")->execute([$pengurusId]);
            $data['foto'] = null;
        }

        // Upload foto baru (opsional)
        if (!empty($_FILES['foto']['name'])) {
            $allowedExt = ['jpg', 'jpeg', 'png'];
            $maxSize = 2 * 1024 * 1024; // 2MB
            $ext = strtolower(pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION));
            if (in_array($ext, $allowedExt, true) && $_FILES['foto']['size'] <= $maxSize && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
                if (!is_dir(__DIR__ . '/uploads/pengurus')) {
                    @mkdir(__DIR__ . '/uploads/pengurus', 0755, true);
                }
                // Hapus foto lama (kalau ada & belum dihapus di atas) supaya tidak menumpuk file
                if (!empty($data['foto'])) {
                    $fileLama = __DIR__ . '/' . $data['foto'];
                    if (is_file($fileLama)) @unlink($fileLama);
                }
                $namaAman = 'pengurus_' . $pengurusId . '_' . time() . '.' . $ext;
                $tujuan = __DIR__ . '/uploads/pengurus/' . $namaAman;
                if (move_uploaded_file($_FILES['foto']['tmp_name'], $tujuan)) {
                    $pathRelatif = 'uploads/pengurus/' . $namaAman;
                    db()->prepare("UPDATE pengurus SET foto = ? WHERE id = ?")->execute([$pathRelatif, $pengurusId]);
                    $data['foto'] = $pathRelatif;
                }
            } else {
                $errors[] = 'Foto gagal diunggah — pastikan format JPG/PNG dan ukuran maksimal 2MB.';
            }
        }

        if (empty($errors)) {
            flash_set('success', 'Data pengurus berhasil disimpan.');
            redirect('struktur.php');
        }
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
      <form method="post" enctype="multipart/form-data">
        <div class="form-card-body">

          <div class="form-section">
            <div class="form-section-label">Foto Profil (opsional)</div>
            <div style="display:flex; align-items:center; gap:16px; flex-wrap:wrap;">
              <?php if (!empty($data['foto']) && is_file(__DIR__ . '/' . $data['foto'])): ?>
                <img src="<?= e($data['foto']) ?>?v=<?= time() ?>" alt="Foto <?= e($data['nama']) ?>" class="pengurus-photo" style="width:64px;height:64px;">
              <?php else: ?>
                <div class="user-avatar" style="width:64px;height:64px;font-size:18px;"><?= e(inisial($data['nama'] ?: '?')) ?></div>
              <?php endif; ?>
              <div style="flex:1; min-width:200px;">
                <label class="upload-zone" style="display:block; padding:12px;">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="18" height="18"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/><path d="M17 8l-5-5-5 5M12 3v12"/></svg>
                  <div><b>Klik untuk pilih foto</b> — JPG atau PNG, maks 2MB</div>
                  <input type="file" name="foto" accept=".jpg,.jpeg,.png" style="display:none;" onchange="this.parentElement.querySelector('b').textContent = this.files[0]?.name || 'Klik untuk pilih foto'">
                </label>
                <?php if (!empty($data['foto'])): ?>
                  <label style="display:flex; align-items:center; gap:6px; font-size:12.5px; color:var(--ink-600); margin-top:8px; font-weight:600; cursor:pointer;">
                    <input type="checkbox" name="hapus_foto" value="1"> Hapus foto saat ini
                  </label>
                <?php endif; ?>
              </div>
            </div>
          </div>

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
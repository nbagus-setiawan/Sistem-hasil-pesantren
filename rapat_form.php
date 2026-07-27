<?php
require_once __DIR__ . '/includes/auth.php';
require_login();

$activePage = 'rapat';
$user = current_user();
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$isEdit = $id > 0;

$bidangList = db()->query("SELECT * FROM bidang ORDER BY id")->fetchAll();

$data = [
    'judul' => '', 'tanggal' => date('Y-m-d'), 'bidang_id' => $bidangList[0]['id'] ?? 1,
    'status' => 'proses', 'peserta' => '', 'penanggung_jawab' => '', 'agenda_keputusan' => '',
];

if ($isEdit) {
    $stmt = db()->prepare("SELECT * FROM musyawarah WHERE id = ?");
    $stmt->execute([$id]);
    $existing = $stmt->fetch();
    if (!$existing) {
        flash_set('success', '');
        redirect('rapat_list.php');
    }
    $data = $existing;
}

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data['judul'] = trim($_POST['judul'] ?? '');
    $data['tanggal'] = $_POST['tanggal'] ?? '';
    $data['bidang_id'] = (int)($_POST['bidang_id'] ?? 0);
    $data['status'] = $_POST['status'] ?? 'proses';
    $data['peserta'] = trim($_POST['peserta'] ?? '');
    $data['penanggung_jawab'] = trim($_POST['penanggung_jawab'] ?? '');
    $data['agenda_keputusan'] = trim($_POST['agenda_keputusan'] ?? '');

    if ($data['judul'] === '') $errors[] = 'Judul rapat wajib diisi.';
    if ($data['tanggal'] === '') $errors[] = 'Tanggal rapat wajib diisi.';
    if ($data['agenda_keputusan'] === '') $errors[] = 'Agenda & keputusan wajib diisi.';
    if (!in_array($data['status'], ['proses','selesai','tertunda'], true)) $errors[] = 'Status tidak valid.';

    if (empty($errors)) {
        if ($isEdit) {
            $stmt = db()->prepare("UPDATE musyawarah SET judul=?, tanggal=?, bidang_id=?, status=?, peserta=?, penanggung_jawab=?, agenda_keputusan=? WHERE id=?");
            $stmt->execute([$data['judul'], $data['tanggal'], $data['bidang_id'], $data['status'], $data['peserta'], $data['penanggung_jawab'], $data['agenda_keputusan'], $id]);
            $rapatId = $id;
            db()->prepare("INSERT INTO log_perubahan (musyawarah_id, user_id, aksi, keterangan) VALUES (?,?,?,?)")
                ->execute([$rapatId, $user['id'], 'update', 'Memperbarui hasil musyawarah']);
        } else {
            $stmt = db()->prepare("INSERT INTO musyawarah (judul, tanggal, bidang_id, status, peserta, penanggung_jawab, agenda_keputusan, dibuat_oleh) VALUES (?,?,?,?,?,?,?,?)");
            $stmt->execute([$data['judul'], $data['tanggal'], $data['bidang_id'], $data['status'], $data['peserta'], $data['penanggung_jawab'], $data['agenda_keputusan'], $user['id']]);
            $rapatId = db()->lastInsertId();
            db()->prepare("INSERT INTO log_perubahan (musyawarah_id, user_id, aksi, keterangan) VALUES (?,?,?,?)")
                ->execute([$rapatId, $user['id'], 'create', 'Mencatat hasil musyawarah baru']);
        }

        // Upload lampiran (opsional)
        if (!empty($_FILES['lampiran']['name'])) {
            $allowedExt = ['pdf','jpg','jpeg','png'];
            $maxSize = 5 * 1024 * 1024; // 5MB
            $ext = strtolower(pathinfo($_FILES['lampiran']['name'], PATHINFO_EXTENSION));
            if (in_array($ext, $allowedExt, true) && $_FILES['lampiran']['size'] <= $maxSize && $_FILES['lampiran']['error'] === UPLOAD_ERR_OK) {
                $namaAman = 'rapat_' . $rapatId . '_' . time() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '_', $_FILES['lampiran']['name']);
                $tujuan = __DIR__ . '/uploads/' . $namaAman;
                if (move_uploaded_file($_FILES['lampiran']['tmp_name'], $tujuan)) {
                    db()->prepare("INSERT INTO lampiran (musyawarah_id, nama_file, path_file) VALUES (?,?,?)")
                        ->execute([$rapatId, $_FILES['lampiran']['name'], 'uploads/' . $namaAman]);
                }
            } else {
                $errors[] = 'Lampiran gagal diunggah — pastikan format PDF/JPG/PNG dan ukuran maksimal 5MB.';
            }
        }

        if (empty($errors)) {
            flash_set('success', $isEdit ? 'Hasil musyawarah berhasil diperbarui.' : 'Hasil musyawarah berhasil disimpan.');
            redirect('rapat_detail.php?id=' . $rapatId);
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= $isEdit ? 'Ubah' : 'Catat' ?> Hasil Musyawarah — Al-Falah Putak</title>
<link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
<div class="app">
  <?php include __DIR__ . '/includes/sidebar.php'; ?>

  <main class="main">
    <div class="topbar reveal">
      <div class="greeting">
        <h1 class="font-display"><?= $isEdit ? 'Ubah Hasil Musyawarah' : 'Catat Hasil Musyawarah' ?></h1>
        <p>Isi selengkap mungkin agar mudah dicari kembali nanti</p>
      </div>
      <a href="rapat_list.php" class="btn btn-ghost">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 12H5M12 19l-7-7 7-7"/></svg>
        Kembali
      </a>
    </div>

    <div class="form-card reveal">
      <div class="form-card-head">
        <h2 class="font-display">Formulir Notulen Rapat</h2>
      </div>

      <?php if (!empty($errors)): ?>
        <div style="padding:0 26px;">
          <div class="alert alert-error">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M12 8v4M12 16h.01"/></svg>
            <span><?= implode(' ', array_map('e', $errors)) ?></span>
          </div>
        </div>
      <?php endif; ?>

      <form method="post" enctype="multipart/form-data">
        <div class="form-card-body">

          <div class="form-section">
            <div class="form-section-label">Info Rapat</div>
            <div class="form-row">
              <div class="form-group">
                <label>Judul Rapat</label>
                <input type="text" name="judul" required placeholder="cth: Evaluasi Kurikulum Tahfidz" value="<?= e($data['judul']) ?>">
              </div>
              <div class="form-group">
                <label>Tanggal</label>
                <input type="date" name="tanggal" required value="<?= e($data['tanggal']) ?>">
              </div>
            </div>
            <div class="form-row">
              <div class="form-group">
                <label>Bidang</label>
                <select name="bidang_id">
                  <?php foreach ($bidangList as $b): ?>
                    <option value="<?= (int)$b['id'] ?>" <?= (int)$data['bidang_id'] === (int)$b['id'] ? 'selected' : '' ?>><?= e($b['nama_bidang']) ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
              <div class="form-group">
                <label>Status</label>
                <select name="status">
                  <option value="proses" <?= $data['status']==='proses'?'selected':'' ?>>Proses</option>
                  <option value="selesai" <?= $data['status']==='selesai'?'selected':'' ?>>Selesai</option>
                  <option value="tertunda" <?= $data['status']==='tertunda'?'selected':'' ?>>Tertunda</option>
                </select>
              </div>
            </div>
          </div>

          <div class="form-section">
            <div class="form-section-label">Peserta &amp; Penanggung Jawab</div>
            <div class="form-row">
              <div class="form-group">
                <label>Peserta Rapat</label>
                <input type="text" name="peserta" placeholder="cth: Ust. Fauzi, Ustzh. Nur, ..." value="<?= e($data['peserta']) ?>">
              </div>
              <div class="form-group">
                <label>Penanggung Jawab</label>
                <input type="text" name="penanggung_jawab" placeholder="cth: Ust. Fauzi" value="<?= e($data['penanggung_jawab']) ?>">
              </div>
            </div>
          </div>

          <div class="form-section">
            <div class="form-section-label">Agenda &amp; Keputusan</div>
            <div class="form-group">
              <textarea name="agenda_keputusan" rows="6" required placeholder="Tuliskan poin-poin keputusan hasil musyawarah…"><?= e($data['agenda_keputusan']) ?></textarea>
            </div>
          </div>

          <div class="form-section">
            <div class="form-section-label">Lampiran (opsional)</div>
            <label class="upload-zone" style="display:block;">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/><path d="M17 8l-5-5-5 5M12 3v12"/></svg>
              <div><b>Klik untuk pilih file</b> — PDF, JPG, atau PNG</div>
              <div style="font-size:11px; margin-top:2px;">Maksimal 5MB</div>
              <input type="file" name="lampiran" accept=".pdf,.jpg,.jpeg,.png" style="display:none;" onchange="this.parentElement.querySelector('b').textContent = this.files[0]?.name || 'Klik untuk pilih file'">
            </label>
          </div>

        </div>
        <div class="form-card-foot">
          <a href="rapat_list.php" class="btn btn-ghost">Batal</a>
          <button type="submit" class="btn btn-primary">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M20 6L9 17l-5-5"/></svg>
            Simpan Hasil Rapat
          </button>
        </div>
      </form>
    </div>
  </main>
</div>
<script src="assets/js/app.js"></script>
</body>
</html>

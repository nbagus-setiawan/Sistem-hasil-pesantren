<?php
require_once __DIR__ . '/includes/auth.php';
require_login();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('rapat_list.php');
}

$id = (int)($_POST['id'] ?? 0);
$user = current_user();

if ($id > 0) {
    // Hapus file lampiran fisik dulu
    $stmt = db()->prepare("SELECT path_file FROM lampiran WHERE musyawarah_id = ?");
    $stmt->execute([$id]);
    foreach ($stmt->fetchAll() as $l) {
        $filePath = __DIR__ . '/' . $l['path_file'];
        if (is_file($filePath)) {
            @unlink($filePath);
        }
    }

    db()->prepare("INSERT INTO log_perubahan (musyawarah_id, user_id, aksi, keterangan) VALUES (?,?,?,?)")
        ->execute([$id, $user['id'], 'delete', 'Menghapus hasil musyawarah']);

    // lampiran akan ikut terhapus otomatis lewat ON DELETE CASCADE
    db()->prepare("DELETE FROM musyawarah WHERE id = ?")->execute([$id]);

    flash_set('success', 'Hasil musyawarah berhasil dihapus.');
}

redirect('rapat_list.php');

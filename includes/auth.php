<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/functions.php';

/** Wajib login untuk mengakses halaman ini, redirect ke login.php kalau belum */
function require_login(): void {
    if (empty($_SESSION['user_id'])) {
        redirect('login.php');
    }
}

/** Wajib admin untuk mengakses halaman ini (kelola pengguna, struktur pengurus) */
function require_admin(): void {
    require_login();
    $user = current_user();
    if (!$user || $user['role'] !== 'admin') {
        flash_set('error_akses', 'Halaman ini hanya bisa diakses oleh admin.');
        redirect('index.php');
    }
}

/** Ambil data user yang sedang login (array) atau null */
function current_user(): ?array {
    if (empty($_SESSION['user_id'])) {
        return null;
    }
    static $cache = null;
    if ($cache === null) {
        $stmt = db()->prepare('SELECT id, username, nama_lengkap, jabatan, role, aktif FROM users WHERE id = ?');
        $stmt->execute([$_SESSION['user_id']]);
        $cache = $stmt->fetch() ?: null;
    }
    return $cache;
}

function attempt_login(string $username, string $password): bool {
    $stmt = db()->prepare('SELECT id, password_hash, aktif FROM users WHERE username = ?');
    $stmt->execute([$username]);
    $user = $stmt->fetch();
    if ($user && (int)$user['aktif'] === 1 && password_verify($password, $user['password_hash'])) {
        session_regenerate_id(true);
        $_SESSION['user_id'] = $user['id'];
        return true;
    }
    return false;
}

function do_logout(): void {
    $_SESSION = [];
    session_destroy();
}

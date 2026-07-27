<?php
/**
 * Konfigurasi koneksi database.
 * Isi ke-4 nilai di bawah sesuai data dari cPanel InfinityFree Anda:
 * cPanel > MySQL Databases > catat: Host, Nama Database, Username, Password
 *
 * Contoh nilai InfinityFree pada umumnya:
 *   DB_HOST = sqlXXX.infinityfree.com
 *   DB_NAME = if0_XXXXXXX_alfalah
 *   DB_USER = if0_XXXXXXX
 *   DB_PASS = (password yang Anda buat saat membuat database)
 */

define('DB_HOST', 'sqlXXX.infinityfree.com');
define('DB_NAME', 'if0_XXXXXXX_alfalah');
define('DB_USER', 'if0_XXXXXXX');
define('DB_PASS', 'ISI_PASSWORD_DATABASE_ANDA');

function db(): PDO {
    static $pdo = null;
    if ($pdo === null) {
        try {
            $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4';
            $pdo = new PDO($dsn, DB_USER, DB_PASS, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]);
        } catch (PDOException $e) {
            http_response_code(500);
            die('Koneksi database gagal. Periksa kembali data di config/database.php. (' . htmlspecialchars($e->getMessage()) . ')');
        }
    }
    return $pdo;
}

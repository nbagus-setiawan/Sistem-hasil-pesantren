<?php
/** Escape output aman untuk HTML */
function e(?string $str): string {
    return htmlspecialchars($str ?? '', ENT_QUOTES, 'UTF-8');
}

/** Format tanggal ke gaya Indonesia: 27 Juli 2026 */
function tgl_indo(string $tanggal): string {
    $bulan = ['', 'Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
    $ts = strtotime($tanggal);
    return date('d', $ts) . ' ' . $bulan[(int)date('n', $ts)] . ' ' . date('Y', $ts);
}

/** Format tanggal pendek: 27 Jul 2026 */
function tgl_pendek(string $tanggal): string {
    $bulan = ['', 'Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des'];
    $ts = strtotime($tanggal);
    return date('d', $ts) . ' ' . $bulan[(int)date('n', $ts)] . ' ' . date('Y', $ts);
}

/** Kelas CSS badge status */
function status_class(string $status): string {
    return match ($status) {
        'selesai' => 'st-selesai',
        'proses' => 'st-proses',
        'tertunda' => 'st-tertunda',
        default => 'st-proses',
    };
}

function status_label(string $status): string {
    return match ($status) {
        'selesai' => 'Selesai',
        'proses' => 'Proses',
        'tertunda' => 'Tertunda',
        default => ucfirst($status),
    };
}

/** Kelas CSS tag warna bidang berdasarkan kode_warna di tabel bidang */
function bidang_tag_class(string $kodeWarna): string {
    $allowed = ['pendidikan','sarana','kesantrian','keuangan'];
    return in_array($kodeWarna, $allowed, true) ? 'tag-' . $kodeWarna : 'tag-pendidikan';
}

/** Inisial nama untuk avatar bulat, contoh: "Siti Nurjannah" -> "SN" */
function inisial(string $nama): string {
    $parts = preg_split('/\s+/', trim($nama));
    $initials = '';
    foreach (array_slice($parts, 0, 2) as $p) {
        if ($p !== '') {
            $initials .= strtoupper(substr($p, 0, 1));
        }
    }
    return $initials !== '' ? $initials : '??';
}

/** Redirect helper */
function redirect(string $path): void {
    header('Location: ' . $path);
    exit;
}

/** Ambil pesan flash sekali-tampil dari session */
function flash_get(string $key): ?string {
    if (!empty($_SESSION[$key])) {
        $val = $_SESSION[$key];
        unset($_SESSION[$key]);
        return $val;
    }
    return null;
}

function flash_set(string $key, string $value): void {
    $_SESSION[$key] = $value;
}

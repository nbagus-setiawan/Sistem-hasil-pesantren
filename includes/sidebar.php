<?php
/** Membutuhkan variabel $activePage (string) dari halaman pemanggil */
$user = current_user();
$activePage = $activePage ?? '';
function navlink(string $key, string $active): string {
    return $key === $active ? ' active' : '';
}
$isAdmin = ($user['role'] ?? '') === 'admin';
?>
<aside class="sidebar">
  <div class="sidebar-brand">
    <div class="brand-mark">
      <img src="assets/img/logo.webp" alt="Logo Al-Falah Putak">
    </div>
    <div>
      <div class="brand-name">Al-Falah Putak</div>
      <div class="brand-sub">SISTEM MUSYAWARAH</div>
    </div>
  </div>

  <nav>
    <a href="index.php" class="nav-item<?= navlink('dashboard', $activePage) ?>">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="9" rx="2"/><rect x="14" y="3" width="7" height="5" rx="2"/><rect x="14" y="12" width="7" height="9" rx="2"/><rect x="3" y="16" width="7" height="5" rx="2"/></svg>
      Dashboard
    </a>
    <a href="rapat_list.php" class="nav-item<?= navlink('rapat', $activePage) ?>">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><path d="M14 2v6h6"/></svg>
      Hasil Musyawarah
    </a>
    <a href="struktur.php" class="nav-item<?= navlink('struktur', $activePage) ?>">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 00-3-3.87"/><path d="M16 3.13a4 4 0 010 7.75"/></svg>
      Struktur Pengurus
    </a>
    <a href="akun.php" class="nav-item<?= navlink('akun', $activePage) ?>">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 00.33 1.82l.06.06a2 2 0 11-2.83 2.83l-.06-.06a1.65 1.65 0 00-1.82-.33 1.65 1.65 0 00-1 1.51V21a2 2 0 01-4 0v-.09A1.65 1.65 0 009 19.4a1.65 1.65 0 00-1.82.33l-.06.06a2 2 0 11-2.83-2.83l.06-.06A1.65 1.65 0 004.6 15a1.65 1.65 0 00-1.51-1H3a2 2 0 010-4h.09A1.65 1.65 0 004.6 9a1.65 1.65 0 00-.33-1.82l-.06-.06a2 2 0 112.83-2.83l.06.06A1.65 1.65 0 009 4.6a1.65 1.65 0 001-1.51V3a2 2 0 014 0v.09a1.65 1.65 0 001 1.51 1.65 1.65 0 001.82-.33l.06-.06a2 2 0 112.83 2.83l-.06.06A1.65 1.65 0 0019.4 9a1.65 1.65 0 001.51 1H21a2 2 0 010 4h-.09a1.65 1.65 0 00-1.51 1z"/></svg>
      Pengaturan Akun
    </a>
    <?php if ($isAdmin): ?>
    <a href="users_list.php" class="nav-item<?= navlink('pengguna', $activePage) ?>">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M16 21v-2a4 4 0 00-4-4H6a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 00-3-3.87"/><path d="M16 3.13a4 4 0 010 7.75"/><path d="M19 8v6M22 11h-6"/></svg>
      Kelola Pengguna
    </a>
    <?php endif; ?>
  </nav>

  <div class="sidebar-footer">
    <div class="user-chip">
      <div class="user-avatar"><?= e(inisial($user['nama_lengkap'] ?? '?')) ?></div>
      <div class="user-meta">
        <div class="name"><?= e($user['nama_lengkap'] ?? '') ?></div>
        <div class="role"><?= e($user['jabatan'] ?? '') ?></div>
      </div>
      <a href="logout.php" class="logout-link" title="Keluar">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 21H5a2 2 0 01-2-2V5a2 2 0 012-2h4"/><path d="M16 17l5-5-5-5"/><path d="M21 12H9"/></svg>
      </a>
    </div>
  </div>
</aside>

<div class="drawer-overlay" id="drawerOverlay"></div>
<div class="drawer" id="drawer">
  <div class="sidebar-brand">
    <div class="brand-mark"><img src="assets/img/logo.webp" alt="Logo Al-Falah Putak"></div>
    <div><div class="brand-name">Al-Falah Putak</div><div class="brand-sub">SISTEM MUSYAWARAH</div></div>
  </div>
  <nav>
    <a href="index.php" class="nav-item<?= navlink('dashboard', $activePage) ?>">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="9" rx="2"/><rect x="14" y="3" width="7" height="5" rx="2"/><rect x="14" y="12" width="7" height="9" rx="2"/><rect x="3" y="16" width="7" height="5" rx="2"/></svg>
      Dashboard
    </a>
    <a href="rapat_list.php" class="nav-item<?= navlink('rapat', $activePage) ?>">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><path d="M14 2v6h6"/></svg>
      Hasil Musyawarah
    </a>
    <a href="struktur.php" class="nav-item<?= navlink('struktur', $activePage) ?>">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
      Struktur Pengurus
    </a>
    <a href="akun.php" class="nav-item<?= navlink('akun', $activePage) ?>">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="3"/></svg>
      Pengaturan Akun
    </a>
    <?php if ($isAdmin): ?>
    <a href="users_list.php" class="nav-item<?= navlink('pengguna', $activePage) ?>">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M16 21v-2a4 4 0 00-4-4H6a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
      Kelola Pengguna
    </a>
    <?php endif; ?>
    <a href="logout.php" class="nav-item">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 21H5a2 2 0 01-2-2V5a2 2 0 012-2h4"/><path d="M16 17l5-5-5-5"/><path d="M21 12H9"/></svg>
      Keluar
    </a>
  </nav>
</div>

<div class="mobile-topbar">
  <button class="hamburger" id="hamburgerBtn"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="18" height="18"><path d="M3 12h18M3 6h18M3 18h18"/></svg></button>
  <div class="brand">
    <div class="brand-mark" style="width:32px;height:32px;"><img src="assets/img/logo.webp" alt="Logo Al-Falah Putak"></div>
    <span class="font-display" style="font-weight:700; font-size:14.5px;">Al-Falah Putak</span>
  </div>
  <div class="user-avatar" style="width:30px;height:30px;font-size:10.5px;"><?= e(inisial($user['nama_lengkap'] ?? '?')) ?></div>
</div>

<a href="rapat_form.php" class="fab" aria-label="Catat rapat baru"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 5v14M5 12h14"/></svg></a>

<div class="bottom-nav">
  <div class="bn-inner">
    <a href="index.php" class="bn-item<?= navlink('dashboard', $activePage) ?>"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="9" rx="2"/><rect x="14" y="3" width="7" height="5" rx="2"/><rect x="14" y="12" width="7" height="9" rx="2"/><rect x="3" y="16" width="7" height="5" rx="2"/></svg>Beranda</a>
    <a href="rapat_list.php" class="bn-item<?= navlink('rapat', $activePage) ?>"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><path d="M14 2v6h6"/></svg>Rapat</a>
    <a href="struktur.php" class="bn-item<?= navlink('struktur', $activePage) ?>"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>Pengurus</a>
    <a href="akun.php" class="bn-item<?= navlink('akun', $activePage) ?>"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="3"/></svg>Akun</a>
  </div>
</div>
# Sistem Musyawarah — Ponpes Al-Falah Putak

Aplikasi internal untuk sekretaris pesantren mencatat, mengarsipkan, dan mencari hasil musyawarah kepengurusan. Dibangun dengan PHP native + MySQL agar kompatibel dengan hosting gratis InfinityFree.

## Isi Folder

```
config/database.php     -> konfigurasi koneksi database (WAJIB diisi)
includes/                -> kode bersama (auth, fungsi, sidebar)
assets/css/style.css     -> tampilan
assets/js/app.js         -> interaksi & animasi
uploads/                 -> tempat penyimpanan file lampiran
sql/schema.sql           -> struktur database + akun awal (untuk install BARU)
sql/migration_v2.sql     -> untuk yang SUDAH PERNAH install versi awal, lihat bagian Migrasi di bawah
login.php, logout.php    -> autentikasi
akun.php                 -> ubah password akun sendiri
users_list.php            -> kelola pengguna (khusus admin)
struktur.php               -> struktur kepengurusan
struktur_form.php           -> tambah/ubah data pengurus (khusus admin)
index.php                -> dashboard
rapat_list.php            -> arsip, pencarian, & pilih untuk export
rapat_form.php             -> tambah/ubah hasil rapat
rapat_detail.php            -> detail + cetak + hapus
rapat_delete.php             -> proses hapus
rapat_export.php              -> export gabungan banyak rapat ke PDF (cetak)
```

## Kalau Anda Sudah Install Versi Sebelumnya (Migrasi)

Kalau database Anda sudah pernah dibuat dari `schema.sql` versi awal (sebelum ada Struktur Pengurus & Kelola Pengguna), **jangan** import ulang `schema.sql` — itu akan mengulang dari kosong. Sebagai gantinya:

1. Buka phpMyAdmin > pilih database Anda > tab **SQL**
2. Tempel isi `sql/migration_v2.sql` > klik **Go**
3. Ini menambahkan kolom `aktif` ke tabel `users` dan tabel baru `pengurus` (dengan beberapa contoh data) tanpa menghapus data hasil musyawarah yang sudah ada
4. Upload semua file PHP baru (timpa yang lama)

Kalau ini instalasi **baru sama sekali**, cukup ikuti langkah instalasi di bawah seperti biasa — `schema.sql` sudah termasuk semuanya.

## Langkah Instalasi ke InfinityFree

**1. Buat akun & website di InfinityFree**
   Daftar di infinityfree.com, buat website baru (dapat subdomain gratis, misal `alfalahputak.rf.gd` atau domain sendiri).

**2. Buat database MySQL**
   - Masuk ke panel kontrol (klien area) > MySQL Databases
   - Buat database baru, catat: **Host**, **Nama Database**, **Username**, **Password**
   - Biasanya hostnya berbentuk `sqlXXX.infinityfree.com` (bukan `localhost`)

**3. Import struktur database**
   - Buka phpMyAdmin dari panel kontrol
   - Pilih database yang tadi dibuat > tab **Import**
   - Upload file `sql/schema.sql` > klik **Go**
   - Ini otomatis membuat semua tabel + 1 akun login awal

**4. Isi file konfigurasi**
   Buka `config/database.php`, ganti 4 baris ini dengan data dari langkah 2:
   ```php
   define('DB_HOST', 'sqlXXX.infinityfree.com');
   define('DB_NAME', 'if0_XXXXXXX_alfalah');
   define('DB_USER', 'if0_XXXXXXX');
   define('DB_PASS', 'password_database_anda');
   ```

**5. Upload semua file**
   - Lewat **File Manager** di panel kontrol, atau FTP (FileZilla)
   - Upload SEMUA isi folder ini ke dalam folder `htdocs`
   - Pastikan folder `uploads/` ikut ter-upload dan permission-nya **755**

**6. Login pertama kali**
   - Buka `https://domainanda.com/login.php`
   - Username: `sekretaris`
   - Password: `alfalah2026`
   - **Segera ganti password ini** (lihat catatan keamanan di bawah)

## Catatan Penting

- **Ganti password default** setelah login pertama. Cara paling gampang sementara ini: buat hash baru dengan PHP `password_hash('password_baru', PASSWORD_DEFAULT)` lalu update manual lewat phpMyAdmin di tabel `users`. (Kalau mau, saya bisa buatkan halaman "Ubah Password" khusus — tinggal minta.)
- **InfinityFree tidak mendukung cron job yang andal**, jadi tidak ada pengingat otomatis lewat email. Notifikasi "perlu tindak lanjut" cukup muncul di dashboard setiap kali sekretaris login.
- **Ukuran upload dibatasi 5MB per file** dari sisi aplikasi. InfinityFree sendiri biasanya membatasi ~10-60MB tergantung paket, jadi 5MB aman.
- Folder `uploads/` sudah dilindungi `.htaccess` supaya file yang diunggah tidak bisa dieksekusi sebagai script (keamanan dasar).
- Kalau nanti ingin menambah pengurus lain yang bisa login, tinggal INSERT manual ke tabel `users` lewat phpMyAdmin (atau minta saya buatkan halaman kelola pengguna).

## Fitur yang Sudah Ada

- Login/logout dengan sesi aman (password di-hash, bukan plain text)
- Dashboard ringkasan (jumlah rapat, status keputusan)
- Catat hasil musyawarah: judul, tanggal, bidang, status, peserta, PJ, agenda & keputusan
- Upload lampiran PDF/JPG/PNG per hasil rapat
- Pencarian & filter (kata kunci, bidang, status)
- Cetak 1 hasil rapat (tombol Cetak di halaman detail)
- **Export gabungan banyak rapat sekaligus ke PDF** — di halaman Arsip, klik "Pilih Beberapa", centang rapat yang diinginkan, lalu "Export Terpilih ke PDF". Bisa juga export semua hasil yang sedang difilter lewat tombol "Export PDF" di pojok atas. Diproses lewat tampilan cetak khusus (tombol "Simpan sebagai PDF" memakai fitur print-to-PDF bawaan browser — tidak butuh library tambahan di server)
- Hapus hasil rapat (dengan konfirmasi)
- Log jejak perubahan sederhana (siapa mengubah apa)
- **Ubah password akun sendiri** (menu Pengaturan Akun)
- **Kelola pengguna** (khusus role admin): tambah pengurus baru yang bisa login, nonaktifkan akun, reset password ke default
- **Struktur Kepengurusan**: daftar pengurus & jabatan sebagai referensi, bisa ditambah/diubah oleh admin

## Catatan Peran Pengguna

- **Admin**: bisa semua hal di atas, termasuk kelola pengguna & struktur pengurus
- **Pengurus (biasa)**: bisa catat/ubah/hapus hasil rapat, export PDF, ubah password sendiri — tapi tidak bisa mengakses halaman Kelola Pengguna atau mengubah Struktur Kepengurusan
- Akun pertama (`sekretaris`) otomatis berperan **admin**

## Pengembangan Lanjutan (Kalau Dibutuhkan)

- Statistik grafik rapat per bulan/bidang
- Notifikasi email pengingat tindak lanjut (butuh layanan email pihak ketiga karena InfinityFree tidak punya cron job andal)
- Foto profil untuk tiap pengurus di halaman Struktur Kepengurusan

-- =====================================================
-- MIGRASI v2 — jalankan ini HANYA jika database Anda
-- sebelumnya sudah dibuat dari schema.sql versi awal
-- (yang belum punya tabel `pengurus` / kolom `aktif`).
--
-- Kalau baru install dari nol, TIDAK PERLU jalankan file
-- ini — cukup import sql/schema.sql yang sudah terbaru.
--
-- Cara pakai: phpMyAdmin > pilih database > tab SQL >
-- tempel isi file ini > Go
-- =====================================================

SET NAMES utf8mb4;

-- Tambah kolom status aktif/nonaktif pada akun pengguna
-- (Kalau muncul error "Duplicate column", berarti kolom ini sudah ada — abaikan saja dan lanjut ke bagian bawah)
ALTER TABLE users ADD COLUMN aktif TINYINT(1) DEFAULT 1 AFTER role;

-- Tabel struktur kepengurusan
CREATE TABLE IF NOT EXISTS pengurus (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nama VARCHAR(150) NOT NULL,
  jabatan VARCHAR(150) NOT NULL,
  bidang_id INT NULL,
  kontak VARCHAR(100),
  urutan INT DEFAULT 0,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (bidang_id) REFERENCES bidang(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO pengurus (nama, jabatan, bidang_id, kontak, urutan) VALUES
  ('KH. Ahmad Dahlan', 'Pengasuh / Ketua Yayasan', NULL, '-', 1),
  ('Ust. Fauzi', 'Kepala Bidang Pendidikan', 1, '0812-xxxx-xxxx', 2),
  ('Bpk. Abdullah', 'Kepala Bidang Sarana & Prasarana', 2, '0813-xxxx-xxxx', 3),
  ('Ustzh. Nur Halimah', 'Kepala Bidang Kesantrian', 3, '0821-xxxx-xxxx', 4),
  ('Bendahara Pesantren', 'Kepala Bidang Keuangan', 4, '0822-xxxx-xxxx', 5);

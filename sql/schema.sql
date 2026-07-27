-- =====================================================
-- Skema Database: Sistem Musyawarah Ponpes Al-Falah Putak
-- Import file ini lewat phpMyAdmin di cPanel InfinityFree
-- =====================================================

SET NAMES utf8mb4;

-- Tabel pengguna (sekretaris / pengurus yang bisa login)
CREATE TABLE IF NOT EXISTS users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(50) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  nama_lengkap VARCHAR(100) NOT NULL,
  jabatan VARCHAR(100) DEFAULT 'Sekretaris',
  role ENUM('admin','pengurus') DEFAULT 'pengurus',
  aktif TINYINT(1) DEFAULT 1,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabel bidang musyawarah
CREATE TABLE IF NOT EXISTS bidang (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nama_bidang VARCHAR(100) NOT NULL,
  kode_warna VARCHAR(20) DEFAULT 'pendidikan'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO bidang (nama_bidang, kode_warna) VALUES
  ('Pendidikan', 'pendidikan'),
  ('Sarana & Prasarana', 'sarana'),
  ('Ubudiyah', 'ubudiyah'),
  ('Kebersihan', 'kebersihan');

-- Tabel utama: hasil musyawarah
CREATE TABLE IF NOT EXISTS musyawarah (
  id INT AUTO_INCREMENT PRIMARY KEY,
  judul VARCHAR(255) NOT NULL,
  tanggal DATE NOT NULL,
  bidang_id INT NOT NULL,
  status ENUM('proses','selesai','tertunda') DEFAULT 'proses',
  peserta TEXT,
  penanggung_jawab VARCHAR(150),
  agenda_keputusan TEXT NOT NULL,
  dibuat_oleh INT,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (bidang_id) REFERENCES bidang(id),
  FOREIGN KEY (dibuat_oleh) REFERENCES users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabel lampiran (PDF/foto notulen)
CREATE TABLE IF NOT EXISTS lampiran (
  id INT AUTO_INCREMENT PRIMARY KEY,
  musyawarah_id INT NOT NULL,
  nama_file VARCHAR(255) NOT NULL,
  path_file VARCHAR(255) NOT NULL,
  uploaded_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (musyawarah_id) REFERENCES musyawarah(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabel log perubahan (jejak audit sederhana)
CREATE TABLE IF NOT EXISTS log_perubahan (
  id INT AUTO_INCREMENT PRIMARY KEY,
  musyawarah_id INT,
  user_id INT,
  aksi VARCHAR(50),
  keterangan VARCHAR(255),
  waktu DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

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
  ('KH. Mursyidi', 'Pengasuh / Ketua Yayasan', NULL, '-', 1),

-- Akun awal (username: sekretaris / password: alfalah2026)
-- PENTING: Ganti password ini setelah login pertama kali lewat menu Pengaturan Akun!
INSERT INTO users (username, password_hash, nama_lengkap, jabatan, role) VALUES
  ('sekretaris', '$2b$10$86vSDPc/GPLN/4tIJSr5neR8EPcnnBItZ5jk/lfrcsGT2uOXcK/qm', 'Nanda bagus setiawan', 'Sekretaris Pesantren', 'admin');

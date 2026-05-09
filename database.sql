-- database.sql untuk Sistem Informasi Monitoring Magang (SIMMAG)

CREATE DATABASE IF NOT EXISTS `db_magang` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `db_magang`;

-- 1. Tabel Users (Untuk login semua role)
CREATE TABLE IF NOT EXISTS `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(100) NOT NULL, -- Bisa berupa NIM/NIP/Email
  `password` varchar(255) NOT NULL,
  `role` enum('admin','kaprodi','dosen','mahasiswa','perusahaan') NOT NULL,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 2. Tabel Periode Magang
CREATE TABLE IF NOT EXISTS `periode_magang` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nama_periode` varchar(100) NOT NULL, -- cth: Semester Ganjil 2026/2027
  `tgl_mulai` date NOT NULL,
  `tgl_selesai` date NOT NULL,
  `is_active` tinyint(1) DEFAULT 0,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 3. Tabel Program Studi
CREATE TABLE IF NOT EXISTS `prodi` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `kode_prodi` varchar(20) NOT NULL,
  `nama_prodi` varchar(100) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 4. Tabel Perusahaan (Tempat Magang)
CREATE TABLE IF NOT EXISTS `perusahaan` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL, -- Relasi ke tabel users untuk login mentor
  `nama_perusahaan` varchar(150) NOT NULL,
  `alamat` text NOT NULL,
  `provinsi` varchar(100) DEFAULT NULL,
  `kota` varchar(100) DEFAULT NULL,
  `kontak_person` varchar(100) DEFAULT NULL,
  `no_telp` varchar(20) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `kuota_magang` int(11) DEFAULT 0,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 5. Tabel Kaprodi
CREATE TABLE IF NOT EXISTS `kaprodi` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `prodi_id` int(11) NOT NULL,
  `nip` varchar(50) NOT NULL,
  `nama_lengkap` varchar(150) NOT NULL,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`prodi_id`) REFERENCES `prodi`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 6. Tabel Dosen
CREATE TABLE IF NOT EXISTS `dosen` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `nip` varchar(50) NOT NULL,
  `nama_lengkap` varchar(150) NOT NULL,
  `no_telp` varchar(20) DEFAULT NULL,
  `bidang_keahlian` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 7. Tabel Mahasiswa
CREATE TABLE IF NOT EXISTS `mahasiswa` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `prodi_id` int(11) NOT NULL,
  `nim` varchar(20) NOT NULL,
  `nama_lengkap` varchar(150) NOT NULL,
  `no_telp` varchar(20) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`prodi_id`) REFERENCES `prodi`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 8. Tabel Pengajuan Magang
CREATE TABLE IF NOT EXISTS `pengajuan_magang` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `kode_pengajuan` varchar(50) NOT NULL, -- cth: PGJ-001
  `mahasiswa_id` int(11) NOT NULL,
  `perusahaan_id` int(11) NOT NULL,
  `periode_id` int(11) NOT NULL,
  `dosen_id` int(11) DEFAULT NULL, -- Diset / di-plot oleh Kaprodi atau Admin
  `judul_proposal` varchar(255) NOT NULL,
  `bidang_magang` varchar(150) DEFAULT NULL,
  `tgl_mulai` date NOT NULL,
  `tgl_selesai` date NOT NULL,
  `catatan_mahasiswa` text DEFAULT NULL,
  `berkas_proposal` varchar(255) DEFAULT NULL,
  `status` enum('Menunggu','Disetujui','Ditolak') DEFAULT 'Menunggu',
  `tgl_pengajuan` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`mahasiswa_id`) REFERENCES `mahasiswa`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`perusahaan_id`) REFERENCES `perusahaan`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`periode_id`) REFERENCES `periode_magang`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`dosen_id`) REFERENCES `dosen`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 9. Tabel Logbook
CREATE TABLE IF NOT EXISTS `logbook` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `pengajuan_id` int(11) NOT NULL,
  `tanggal` date NOT NULL,
  `kegiatan` text NOT NULL,
  `bukti_foto` varchar(255) DEFAULT NULL,
  `status_validasi` enum('Menunggu','Disetujui','Revisi') DEFAULT 'Menunggu',
  `catatan_mentor` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`pengajuan_id`) REFERENCES `pengajuan_magang`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 10. Tabel Laporan Akhir
CREATE TABLE IF NOT EXISTS `laporan_akhir` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `pengajuan_id` int(11) NOT NULL,
  `file_laporan` varchar(255) NOT NULL,
  `tgl_submit` timestamp DEFAULT CURRENT_TIMESTAMP,
  `status` enum('Menunggu','Dinilai','Revisi') DEFAULT 'Menunggu',
  PRIMARY KEY (`id`),
  FOREIGN KEY (`pengajuan_id`) REFERENCES `pengajuan_magang`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 11. Tabel Nilai
CREATE TABLE IF NOT EXISTS `nilai` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `pengajuan_id` int(11) NOT NULL,
  `nilai_pembimbing_lapangan` decimal(5,2) DEFAULT NULL, -- Nilai dari perusahaan
  `nilai_dosen_pembimbing` decimal(5,2) DEFAULT NULL, -- Nilai dari dosen
  `catatan_dosen` text DEFAULT NULL,
  `catatan_mentor` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`pengajuan_id`) REFERENCES `pengajuan_magang`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- INSERT DUMMY DATA UNTUK PENGUJIAN

-- Insert Users (Password: 123)
-- hash untuk '123' memakai md5 atau bycrypt? Di PHP sistem lama mungkin belum pake hash, kita pakai plain atau password_hash
-- Untuk amannya, kita masukkan password yg di hash pakai password_hash('123', PASSWORD_DEFAULT) jika diperlukan,
-- tapi karena ini mock, mari kita pakai MD5('123') = '202cb962ac59075b964b07152d234b70' atau plain. Kita asumsikan MD5.
INSERT INTO `users` (`username`, `password`, `role`) VALUES
('admin', MD5('123'), 'admin'),
('kaprodi_ti', MD5('123'), 'kaprodi'),
('suyanto', MD5('123'), 'dosen'),
('10123001', MD5('123'), 'mahasiswa'),
('telkom', MD5('123'), 'perusahaan');

-- Insert Prodi
INSERT INTO `prodi` (`kode_prodi`, `nama_prodi`) VALUES
('TI', 'Teknik Informatika'),
('SI', 'Sistem Informasi');

-- Insert Periode
INSERT INTO `periode_magang` (`nama_periode`, `tgl_mulai`, `tgl_selesai`, `is_active`) VALUES
('Ganjil 2026/2027', '2026-08-01', '2026-12-31', 1);

-- Insert Admin (Tidak ada tabel khusus admin, cukup di users)

-- Insert Kaprodi
INSERT INTO `kaprodi` (`user_id`, `prodi_id`, `nip`, `nama_lengkap`) VALUES
((SELECT id FROM users WHERE username='kaprodi_ti'), 1, '198001012005011001', 'Dr. Budi Darmawan, M.Kom.');

-- Insert Dosen
INSERT INTO `dosen` (`user_id`, `nip`, `nama_lengkap`, `no_telp`, `bidang_keahlian`) VALUES
((SELECT id FROM users WHERE username='suyanto'), '197508232005011002', 'Dr. Ir. Suyanto, M.T.', '081122334455', 'Software Engineering');

-- Insert Mahasiswa
INSERT INTO `mahasiswa` (`user_id`, `prodi_id`, `nim`, `nama_lengkap`, `no_telp`, `email`) VALUES
((SELECT id FROM users WHERE username='10123001'), 1, '10123001', 'Ahmad Fauzi', '081234567890', 'ahmad.fauzi@student.ac.id');

-- Insert Perusahaan
INSERT INTO `perusahaan` (`user_id`, `nama_perusahaan`, `alamat`, `provinsi`, `kota`, `kontak_person`, `no_telp`, `email`, `kuota_magang`) VALUES
((SELECT id FROM users WHERE username='telkom'), 'PT Telkom Indonesia', 'Jl. Japati No.1', 'Jawa Barat', 'Bandung', 'Bapak HRD', '022123456', 'hrd@telkom.co.id', 5);

-- Insert Pengajuan Magang
INSERT INTO `pengajuan_magang` (`kode_pengajuan`, `mahasiswa_id`, `perusahaan_id`, `periode_id`, `dosen_id`, `judul_proposal`, `bidang_magang`, `tgl_mulai`, `tgl_selesai`, `status`) VALUES
('PGJ-001', 1, 1, 1, 1, 'Pengembangan Sistem Informasi Berbasis Web', 'Software Development', '2026-05-01', '2026-07-31', 'Menunggu');


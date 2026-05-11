-- Migration: SSO / Google OAuth support
-- Date: 2026-05-10
-- Target DB: db_magang

USE `db_magang`;

-- 1) Tambah kolom email & metadata OAuth pada users
-- (Aman dijalankan berulang: cek dulu apakah kolom sudah ada sebelum ALTER jika diperlukan)

ALTER TABLE `users`
  ADD COLUMN `email` VARCHAR(150) NULL AFTER `username`,
  ADD COLUMN `oauth_provider` VARCHAR(20) NULL AFTER `role`,
  ADD COLUMN `oauth_sub` VARCHAR(80) NULL AFTER `oauth_provider`,
  ADD COLUMN `oauth_picture` TEXT NULL AFTER `oauth_sub`;

-- Unique email (boleh multiple NULL di MySQL)
CREATE UNIQUE INDEX `uq_users_email` ON `users` (`email`);

-- Optional: index untuk sub (lebih stabil daripada email)
CREATE INDEX `idx_users_oauth` ON `users` (`oauth_provider`, `oauth_sub`);

-- 2) (Opsional) Tabel kunjungan dosen (sebelumnya dibuat otomatis di halaman)
CREATE TABLE IF NOT EXISTS `kunjungan_dosen` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `dosen_id` INT NOT NULL,
  `perusahaan_id` INT NOT NULL,
  `tanggal` DATE NOT NULL,
  `catatan` TEXT,
  `dokumentasi` VARCHAR(255),
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT `fk_kunjungan_dosen_dosen` FOREIGN KEY (`dosen_id`) REFERENCES `dosen`(`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_kunjungan_dosen_perusahaan` FOREIGN KEY (`perusahaan_id`) REFERENCES `perusahaan`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 3) Contoh mapping email untuk user yang SUDAH ADA (silakan sesuaikan)
-- UPDATE users SET email='nama@polije.ac.id' WHERE role='dosen' AND username='suyanto';
-- UPDATE users SET email='kaprodi.ti@polije.ac.id' WHERE role='kaprodi' AND username='kaprodi_ti';
-- UPDATE users SET email='10123001@student.polije.ac.id' WHERE role='mahasiswa' AND username='10123001';

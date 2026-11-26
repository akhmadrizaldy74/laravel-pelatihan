-- ============================================================
--  DATABASE: db_pelatihan
--  Dashboard Daftar Pelatihan Tahun 2026
--  Generated: 2026-11-18
-- ============================================================

CREATE DATABASE IF NOT EXISTS `db_pelatihan`
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE `db_pelatihan`;

-- ------------------------------------------------------------
--  TABLE: migrations (required by Laravel)
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `migrations` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `migrations` (`migration`, `batch`) VALUES
('2024_01_01_000000_create_pelatihans_table', 1);

-- ------------------------------------------------------------
--  TABLE: pelatihans
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `pelatihans` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `nama_pelatihan` varchar(255) NOT NULL,
  `jumlah_peserta` int(11) NOT NULL,
  `tanggal_mulai` date NOT NULL,
  `tanggal_selesai` date NOT NULL,
  `status` enum('Belum Dimulai','Sedang Berlangsung','Tahap Seleksi','Selesai') NOT NULL DEFAULT 'Belum Dimulai',
  `auto_status` tinyint(1) NOT NULL DEFAULT 1 COMMENT 'If true, status is auto-calculated from dates',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
--  DATA: 20 Pelatihan Tahun 2026
-- ------------------------------------------------------------
INSERT INTO `pelatihans`
  (`nama_pelatihan`, `jumlah_peserta`, `tanggal_mulai`, `tanggal_selesai`, `status`, `auto_status`, `created_at`, `updated_at`)
VALUES
  ('Pelatihan Pembentukan JF Pengawas Ketenagakerjaan A. 111',         30,  '2026-10-07', '2026-11-11', 'Belum Dimulai', 1, NOW(), NOW()),
  ('Pelatihan Pembentukan JF Pengawas Ketenagakerjaan A. 112',         30,  '2026-10-27', '2026-12-03', 'Belum Dimulai', 1, NOW(), NOW()),
  ('Pelatihan Pembentukan JF Mediator Hubungan Industrial A. 103',     30,  '2026-09-07', '2026-10-09', 'Belum Dimulai', 1, NOW(), NOW()),
  ('Pelatihan Pembentukan JF Mediator Hubungan Industrial A. 104',     30,  '2026-09-23', '2026-10-27', 'Belum Dimulai', 1, NOW(), NOW()),
  ('Pelatihan Pembentukan JF Pengantar Kerja A. 69',                   30,  '2026-09-28', '2026-10-29', 'Belum Dimulai', 1, NOW(), NOW()),
  ('Pelatihan Pembentukan JF Pengantar Kerja A. 70',                   30,  '2026-10-15', '2026-11-12', 'Belum Dimulai', 1, NOW(), NOW()),
  ('Pelatihan Pelat. Pembentukan JF Penguji K3 A. 5',                  40,  '2026-10-19', '2026-11-24', 'Belum Dimulai', 1, NOW(), NOW()),
  ('Pelatihan Pembentukan JF Instruktur A. 5',                         40,  '2026-08-26', '2026-09-29', 'Belum Dimulai', 1, NOW(), NOW()),
  ('Pelatihan Pembentukan JF Instruktur A. 6',                         40,  '2026-09-02', '2026-10-08', 'Belum Dimulai', 1, NOW(), NOW()),
  ('Pelatihan Pembentukan JF Instruktur A. 7',                         40,  '2026-09-09', '2026-10-15', 'Belum Dimulai', 1, NOW(), NOW()),
  ('Pelatihan Pembentukan JF Instruktur PTKJ',                         15,  '2026-09-14', '2026-10-20', 'Belum Dimulai', 1, NOW(), NOW()),
  ('TOT Pengampu Materi WAS/UJI K3',                                   14,  '2026-02-02', '2026-02-05', 'Belum Dimulai', 1, NOW(), NOW()),
  ('TOT Pengampu Materi',                                              33,  '2026-04-06', '2026-04-10', 'Belum Dimulai', 1, NOW(), NOW()),
  ('Pelatihan PPK Tipe C',                                             30,  '2026-04-13', '2026-04-17', 'Belum Dimulai', 1, NOW(), NOW()),
  ('Pelatihan PBJ Level 1 A. 4',                                       30,  '2026-10-22', '2026-11-06', 'Belum Dimulai', 1, NOW(), NOW()),
  ('Pelatihan Pengelolaan Arsip Elektronik',                           32,  '2026-05-04', '2026-05-09', 'Belum Dimulai', 1, NOW(), NOW()),
  ('Pelatihan Kepemimpinan Administrator A. 23',                       40,  '2026-04-13', '2026-08-28', 'Belum Dimulai', 1, NOW(), NOW()),
  ('Pelatihan Kepemimpinan Pengawas A. 37',                            35,  '2026-05-11', '2026-09-24', 'Belum Dimulai', 1, NOW(), NOW()),
  ('Pelatihan Dasar CPNS Gelombang I (A. 25, A. 26, A. 78, A.79)',    141, '2026-01-20', '2026-04-20', 'Belum Dimulai', 1, NOW(), NOW()),
  ('Pelatihan Dasar CPNS Gelombang II (A. 80, A. 81, A. 82, A.83)',   140, '2026-01-27', '2026-04-29', 'Belum Dimulai', 1, NOW(), NOW());

-- ============================================================
--  SELESAI - Import berhasil!
--  Buka http://localhost:8000 setelah php artisan serve
-- ============================================================

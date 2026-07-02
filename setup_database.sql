-- ============================================================
-- Setup SQL Database untuk LitSpace (Perpustakaan Digital)
-- Import file ini lewat phpMyAdmin atau: mysql -u root -p < setup_database.sql
-- Catatan: db.php sudah otomatis membuat tabel ini juga saat
-- aplikasi pertama kali diakses, jadi file ini bersifat opsional
-- (berguna kalau ingin setup manual / lihat struktur databasenya).
-- ============================================================

CREATE DATABASE IF NOT EXISTS `uts_perpus` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `uts_perpus`;

-- ------------------------------------------------------------
-- Tabel users: menyimpan akun admin & user (untuk login)
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `users` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `username` VARCHAR(50) NOT NULL UNIQUE,
  `password` VARCHAR(255) NOT NULL,
  `full_name` VARCHAR(100) DEFAULT NULL,
  `hp` VARCHAR(20) DEFAULT NULL,
  `role` ENUM('admin','user') NOT NULL DEFAULT 'user',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- Tabel buku: menyimpan data buku, termasuk foto cover (cover_image)
-- dan file PDF (file_path). Dipakai oleh tambah_buku.php,
-- daftar_buku.php, edit_buku.php, dan hapus_buku.php.
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `buku` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `judul` VARCHAR(150) NOT NULL,
  `penulis` VARCHAR(100) NOT NULL,
  `penerbit` VARCHAR(100) DEFAULT NULL,
  `tahun_terbit` YEAR DEFAULT NULL,
  `isbn` VARCHAR(30) DEFAULT NULL,
  `kategori` VARCHAR(50) DEFAULT NULL,
  `status` ENUM('dibaca','belum') NOT NULL DEFAULT 'belum',
  `stok` INT UNSIGNED NOT NULL DEFAULT 1,
  `deskripsi` TEXT DEFAULT NULL,
  `cover_image` VARCHAR(255) DEFAULT NULL COMMENT 'Nama file foto cover, disimpan di folder profiles/books/',
  `file_path` VARCHAR(255) DEFAULT NULL COMMENT 'Path file PDF buku, disimpan di folder Buku/',
  `ditambahkan_oleh` VARCHAR(50) DEFAULT NULL COMMENT 'username yang menambahkan buku',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- Akun default (password akan otomatis di-hash oleh db.php saat
-- aplikasi pertama kali dijalankan). Jika ingin insert manual,
-- ganti '$2y$...' dengan hasil password_hash() dari PHP.
-- Login default setelah db.php dijalankan:
--   admin / admin123  -> masuk ke Panel Admin (perpustakaan.php)
--   user  / user123   -> masuk ke Panel User  (dashboard_user.php)
-- ------------------------------------------------------------
-- INSERT INTO users (username, password, full_name, role) VALUES ('admin', '$2y$...','Administrator','admin');
-- INSERT INTO users (username, password, full_name, role) VALUES ('user', '$2y$...','User Biasa','user');

-- Contoh data buku (opsional)
-- INSERT INTO buku (judul, penulis, kategori, status, stok, ditambahkan_oleh)
-- VALUES ('Aku dan Mimpiku', 'Anonim', 'Motivasi', 'belum', 1, 'admin');

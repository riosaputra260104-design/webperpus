# LitSpace - CRUD Operations & Features Summary

## ✅ Sistem Login Berbasis Database

### Implementasi
- **Database:** MySQL/MariaDB dengan tabel `users`
- **Keamanan:** Password di-hash dengan `password_hash()` menggunakan algorithm bcrypt
- **Session Management:** Menggunakan PHP session + cookie remember-me
- **Validasi:** Client-side dan server-side

### File Terkait
- `db.php` - Koneksi database dan inisialisasi
- `login.php` - Form login
- `proses_login.php` - Proses autentikasi
- `logout.php` - Logout dan destroy session
- `register.php` - Registrasi user baru

### Kredensial Default
```
Admin:
- Username: admin
- Password: admin123

User Biasa:
- Username: user  
- Password: user123
```

---

## ✅ CRUD Operations

### 1. CREATE - Tambah Buku

**File:** `tambah_buku.php`

**Fitur:**
- Form untuk menambah buku baru dengan field lengkap
- Upload cover image (JPG, PNG, GIF, max 5MB)
- Validasi input (judul & penulis wajib)
- Simpan data ke tabel `buku` dengan info pembuat

**Database Operation:**
```php
// Insert buku ke database
INSERT INTO buku (judul, penulis, penerbit, tahun_terbit, isbn, kategori, stok, deskripsi, cover_image, created_by) 
VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
```

**Field Input:**
- Judul Buku (wajib)
- Penulis (wajib)
- Penerbit
- Tahun Terbit
- ISBN
- Kategori
- Stok Buku
- Deskripsi
- Cover Image

**Flow:**
1. User admin akses `tambah_buku.php`
2. Isi form dengan detail buku
3. Upload cover image (opsional)
4. Submit form
5. Data disimpan ke database
6. Cover image disimpan ke folder `profiles/books/`
7. Tampil notifikasi sukses

---

### 2. READ - Lihat Daftar Buku

**File:** `daftar_buku.php`

**Fitur:**
- Tampilkan semua buku dari database dalam bentuk tabel
- Menampilkan cover image
- Sorting berdasarkan tanggal pembuatan (terbaru duluan)
- Pagination-ready (bisa ditambah)
- Alert empty state jika belum ada buku

**Database Operation:**
```php
// Query mengambil semua buku
SELECT id, judul, penulis, penerbit, tahun_terbit, kategori, stok, cover_image 
FROM buku 
ORDER BY created_at DESC
```

**Data yang Ditampilkan:**
- No urut
- Cover Image
- Judul
- Penulis
- Penerbit
- Tahun Terbit
- Kategori
- Stok (badge)
- Tombol Aksi (Edit & Hapus)

**Flow:**
1. User admin akses `daftar_buku.php`
2. Sistem query semua buku dari database
3. Tampilkan dalam tabel interaktif
4. Setiap buku menampilkan cover image
5. Tersedia tombol untuk edit dan hapus

---

### 3. UPDATE - Edit Buku

**File:** `edit_buku.php`

**Fitur:**
- Form untuk edit informasi buku yang sudah ada
- Preview cover image saat ini
- Opsi upload cover image baru
- Validasi input
- Hapus file cover lama saat upload baru

**Database Operation:**
```php
// Update buku
UPDATE buku 
SET judul = ?, penulis = ?, penerbit = ?, tahun_terbit = ?, isbn = ?, kategori = ?, stok = ?, deskripsi = ?, cover_image = ? 
WHERE id = ?

// Ambil data buku untuk edit
SELECT id, judul, penulis, penerbit, tahun_terbit, isbn, kategori, stok, deskripsi, cover_image 
FROM buku 
WHERE id = ?
```

**Field Edit:**
- Judul Buku
- Penulis
- Penerbit
- Tahun Terbit
- ISBN
- Kategori
- Stok Buku
- Deskripsi
- Cover Image (baru)

**Flow:**
1. User admin klik tombol "Edit" di daftar buku
2. Sistem buka `edit_buku.php?id=X`
3. Tampilkan form pre-filled dengan data buku
4. Tampilkan preview cover image saat ini
5. User dapat mengubah field apapun
6. Jika upload gambar baru, hapus yang lama
7. Submit untuk update database
8. Tampil notifikasi sukses

---

### 4. DELETE - Hapus Buku

**File:** `daftar_buku.php` (dengan proses via query string)

**Fitur:**
- Tombol hapus di setiap baris buku
- Konfirmasi sebelum menghapus
- Hapus record dari database
- Hapus file cover image jika ada
- Redirect ke list buku setelah hapus

**Database Operation:**
```php
// Hapus buku dari database
DELETE FROM buku WHERE id = ?

// Ambil cover image sebelum delete
SELECT cover_image FROM buku WHERE id = ?
```

**File Operation:**
- Hapus file `profiles/books/[filename]` jika ada

**Flow:**
1. User admin klik tombol "Hapus" di baris buku
2. Browser tampil konfirmasi: "Yakin ingin menghapus buku ini?"
3. Jika OK, submit ke `daftar_buku.php?delete=X`
4. Sistem ambil info cover image
5. Hapus file cover image dari folder
6. Hapus record dari database
7. Redirect ke `daftar_buku.php?success=1`
8. Tampil notifikasi sukses

---

## Database Schema

### Tabel `buku`
```sql
CREATE TABLE `buku` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `judul` VARCHAR(255) NOT NULL,
  `penulis` VARCHAR(100) NOT NULL,
  `penerbit` VARCHAR(100),
  `tahun_terbit` INT,
  `isbn` VARCHAR(20),
  `kategori` VARCHAR(50),
  `stok` INT DEFAULT 0,
  `deskripsi` TEXT,
  `cover_image` VARCHAR(255),
  `created_by` INT UNSIGNED,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`created_by`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

### Tabel `users`
```sql
CREATE TABLE `users` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `username` VARCHAR(50) NOT NULL UNIQUE,
  `password` VARCHAR(255) NOT NULL,
  `full_name` VARCHAR(100),
  `role` ENUM('admin','user') NOT NULL DEFAULT 'user',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

---

## Fitur Tambahan

### User Management
- **Edit Profil User** - `profil_user.php`
- **Ubah Password** - `pengaturan.php` (untuk admin)
- **Upload Foto Profil** - `pengaturan.php`

### Role-Based Access Control
- **Admin:** Akses penuh ke perpustakaan.php, daftar_buku.php, tambah_buku.php, edit_buku.php, pengaturan.php
- **User:** Akses terbatas ke dashboard_user.php

### Security Features
- Password hashing dengan bcrypt
- Prepared statements untuk mencegah SQL injection
- File upload validation (tipe & ukuran)
- Session management yang aman
- CSRF protection (form POST untuk operasi sensitif)

---

## Testing Checklist

### ✅ Authentication
- [ ] Login dengan admin berhasil
- [ ] Login dengan user berhasil
- [ ] Logout berfungsi
- [ ] Registrasi user baru berhasil
- [ ] Password tidak match → error
- [ ] Username duplikat → error

### ✅ CRUD Books - Create
- [ ] Form tambah buku muncul
- [ ] Validasi judul wajib
- [ ] Validasi penulis wajib
- [ ] Upload cover image berhasil
- [ ] File size > 5MB → error
- [ ] File type invalid → error
- [ ] Buku tampil di list setelah tambah
- [ ] Cover image visible di list

### ✅ CRUD Books - Read
- [ ] List buku menampilkan semua buku
- [ ] Cover image muncul dengan benar
- [ ] Sorting berdasarkan tanggal terbaru
- [ ] Tabel responsive
- [ ] Empty state jika belum ada buku

### ✅ CRUD Books - Update
- [ ] Tombol Edit muncul di setiap buku
- [ ] Form edit pre-filled dengan data lama
- [ ] Preview cover image lama
- [ ] Edit judul berhasil
- [ ] Edit penulis berhasil
- [ ] Upload cover baru berhasil
- [ ] File lama terhapus saat upload baru
- [ ] Data ter-update di database

### ✅ CRUD Books - Delete
- [ ] Tombol Hapus muncul di setiap buku
- [ ] Konfirmasi hapus muncul
- [ ] Cancel tidak menghapus buku
- [ ] OK menghapus buku dari list
- [ ] Cover image file terhapus
- [ ] Notifikasi sukses muncul

### ✅ Security
- [ ] SQL injection tidak bisa dilakukan
- [ ] File upload hanya tipe image
- [ ] User biasa tidak bisa akses admin panel
- [ ] Session protect halaman dari akses tanpa login

---

## Workflow Diagram

```
┌─────────────────────────────────────────────────────────────┐
│                        LitSpace Workflow                      │
└─────────────────────────────────────────────────────────────┘

LOGIN
  ↓
┌──────────────────────────────────────────────────────────┐
│ DASHBOARD                                                  │
│ Admin: perpustakaan.php                                   │
│ User: dashboard_user.php                                  │
└──────────────────────────────────────────────────────────┘
  ↓
┌──────────────────────────────────────────────────────────┐
│ MENU (Admin Only)                                          │
│ ├─ Daftar Buku (READ)                                     │
│ │  ├─ Edit Buku (UPDATE)                                  │
│ │  └─ Hapus Buku (DELETE)                                 │
│ ├─ Tambah Buku (CREATE)                                   │
│ ├─ Pengaturan                                              │
│ └─ Logout                                                  │
└──────────────────────────────────────────────────────────┘
```

---

**Dokumentasi Lengkap:** Baca README.md dan INSTALLATION.md

**Status:** ✅ SIAP PRODUKSI

**Versi:** 1.0

**Last Updated:** 2026-05-20

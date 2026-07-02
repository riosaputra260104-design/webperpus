# Panduan Instalasi LitSpace

## Prasyarat

1. **XAMPP/WAMP/LAMP** - Web server lokal dengan Apache dan MySQL
2. **PHP 7.2+** - PHP harus sudah installed
3. **MySQL 5.5+** atau **MariaDB**
4. **Browser Modern** - Chrome, Firefox, Safari, Edge, dll

## Langkah-langkah Instalasi

### 1. Persiapan Folder

```
C:\xampp\htdocs\
├── uts1/                    # Buat folder baru
│   ├── db.php
│   ├── login.php
│   ├── ... (semua file aplikasi)
│   └── profiles/
│       └── books/           # Akan dibuat otomatis
```

- Extract/copy semua file aplikasi ke folder `C:\xampp\htdocs\uts1\` (Windows)
- Atau `/var/www/html/uts1/` (Linux)

### 2. Jalankan Web Server

**Windows (XAMPP):**
- Buka XAMPP Control Panel
- Klik "Start" untuk Apache dan MySQL

**Linux (Terminal):**
```bash
sudo service apache2 start
sudo service mysql start
```

### 3. Akses Aplikasi

Buka browser dan akses:
```
http://localhost/uts1
```

Sistem akan otomatis:
- ✅ Membuat database `uts_perpus`
- ✅ Membuat tabel `users` dan `buku`
- ✅ Membuat user default (admin & user)
- ✅ Membuat folder `profiles/books/` untuk upload cover

### 4. Login Pertama

Gunakan kredensial berikut untuk login:

**Akun Admin (Full Access):**
- Username: `admin`
- Password: `admin123`

**Akun User Biasa (Limited Access):**
- Username: `user`
- Password: `user123`

**Jangan lupa:** Centang checkbox "Ingat Saya" sebelum login

## Konfigurasi Database

Jika ingin mengubah kredensial database, edit file `db.php`:

```php
$DB_HOST = 'localhost';      // Host MySQL
$DB_USER = 'root';           // Username MySQL
$DB_PASS = '';               // Password MySQL
$DB_NAME = 'uts_perpus';     // Nama database
```

## Struktur Folder yang Dibutuhkan

```
uts1/
├── bg/                       # Background images (sudah ada)
├── profiles/                 # Folder profil user (sudah ada)
│   └── books/                # Folder cover buku (auto-created)
├── plugins/                  # AdminLTE plugins (pastikan ada)
├── dist/                     # AdminLTE CSS/JS (pastikan ada)
├── db.php
├── login.php
├── register.php
├── proses_login.php
├── logout.php
├── perpustakaan.php
├── dashboard_user.php
├── tambah_buku.php
├── daftar_buku.php
├── edit_buku.php
├── profil_user.php
├── pengaturan.php
├── setup_database.sql        # Script SQL (opsional)
└── README.md                 # Dokumentasi
```

## Troubleshooting Setup

### Error: "Database connection failed"

**Solusi:**
1. Pastikan MySQL service running
2. Cek username & password di `db.php` sesuai dengan instalasi MySQL Anda
3. Cek port MySQL (default 3306)

```bash
# Test koneksi MySQL
mysql -h localhost -u root -p
```

### Error: "Permission denied" (upload gambar)

**Solusi:**
```bash
# Linux: Ubah permission folder
chmod 755 profiles/books/

# Windows: Right-click folder > Properties > Security > Edit
# Berikan Full Control untuk user Anda
```

### Error: "Table creation failed"

**Solusi:**
1. Pastikan user MySQL punya privilege CREATE
2. Hapus database `uts_perpus` dan coba lagi
3. Atau jalankan manual query dari `setup_database.sql`

```bash
# Manual setup via MySQL
mysql -h localhost -u root -p < setup_database.sql
```

### Folder `profiles/books` tidak terbuat

**Solusi:**
- Buat manual folder di `profiles/books/`
- Atau upload pertama kali akan auto-create folder tersebut

## Testing Aplikasi

### Test Login
1. Buka http://localhost/uts1
2. Login dengan admin/admin123
3. Seharusnya redirect ke perpustakaan.php

### Test Registrasi
1. Klik "Daftar di sini"
2. Isi form registrasi
3. Login dengan akun baru

### Test CRUD

#### Create (Tambah Buku)
1. Login sebagai admin
2. Klik "Tambah Buku"
3. Isi form buku
4. Upload cover image
5. Klik "Simpan Buku"
✅ Buku seharusnya muncul di daftar

#### Read (Lihat Daftar)
1. Klik "Daftar Buku"
2. Lihat semua buku dalam tabel
3. Verifikasi cover image tampil

#### Update (Edit Buku)
1. Di "Daftar Buku", klik tombol "Edit"
2. Ubah beberapa field
3. Klik "Simpan Perubahan"
✅ Data seharusnya ter-update

#### Delete (Hapus Buku)
1. Di "Daftar Buku", klik tombol "Hapus"
2. Konfirmasi hapus
✅ Buku seharusnya terhapus dari list

## Operasi Database Manual

Jika perlu akses langsung ke database:

```bash
# Login ke MySQL
mysql -h localhost -u root -p

# Pilih database
USE uts_perpus;

# Lihat semua buku
SELECT * FROM buku;

# Lihat semua user
SELECT * FROM users;

# Hapus database (jika perlu reset total)
DROP DATABASE uts_perpus;
```

## Upgrade Password Default

**Untuk keamanan, ubah password default:**

### Via Database
```sql
UPDATE users SET password = PASSWORD('password_baru') WHERE username = 'admin';
```

### Via Aplikasi
1. Login sebagai admin
2. Klik "Pengaturan" (jika fitur ada)
3. Ubah password Anda

## Security Checklist

- [ ] Ubah password user default (admin & user)
- [ ] Update kredensial database di db.php
- [ ] Pastikan file `db.php` tidak bisa diakses public
- [ ] Set permission folder yang tepat (755 atau 775)
- [ ] Disable directory listing di web server
- [ ] Update library & framework ke versi terbaru
- [ ] Setup SSL/HTTPS untuk production

## Performance Tips

1. **Database Index:**
   ```sql
   CREATE INDEX idx_username ON users(username);
   CREATE INDEX idx_judul ON buku(judul);
   CREATE INDEX idx_kategori ON buku(kategori);
   ```

2. **Optimize Images:**
   - Compress cover image sebelum upload
   - Gunakan format WebP jika browser support

3. **Caching:**
   - Setup server-side caching untuk query CRUD

## Dukungan & Help

Jika ada masalah:
1. Baca README.md untuk dokumentasi lengkap
2. Cek error log di browser console (F12)
3. Cek PHP error log di `php_error.log`
4. Cek MySQL error log di folder MySQL

## Update & Maintenance

- Backup database secara rutin
- Backup folder `profiles/books/` (cover images)
- Update AdminLTE dan library lainnya
- Monitor disk space untuk uploads

---

**Selamat menggunakan LitSpace! Happy coding! 📚**

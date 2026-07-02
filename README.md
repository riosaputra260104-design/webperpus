# LitSpace - Perpustakaan Digital

Sistem manajemen perpustakaan digital berbasis web dengan fitur login berbasis database dan CRUD lengkap.

## Fitur Utama

- ✅ **Login Berbasis Database** - Autentikasi user dengan password terenkripsi
- ✅ **Sistem Registrasi** - User baru dapat mendaftar
- ✅ **CRUD Buku Lengkap**:
  - **C**reate (Tambah Buku) - Admin dapat menambah buku baru
  - **R**ead (Lihat Daftar) - Admin dapat melihat semua buku
  - **U**pdate (Edit Buku) - Admin dapat mengubah informasi buku
  - **D**elete (Hapus Buku) - Admin dapat menghapus buku
- ✅ **Role-based Access** - Akses berbeda untuk Admin dan User
- ✅ **Upload Cover Gambar** - Setiap buku dapat memiliki cover image
- ✅ **Responsive Design** - Antarmuka yang responsif dan user-friendly

## Kredensial Default

### Admin
- **Username:** admin
- **Password:** admin123
- **Role:** Admin (akses penuh CRUD)

### User Biasa
- **Username:** user
- **Password:** user123
- **Role:** User (akses terbatas)

## Struktur Database

### Tabel `users`
```sql
- id (INT PRIMARY KEY)
- username (VARCHAR 50, UNIQUE)
- password (VARCHAR 255, hashed)
- full_name (VARCHAR 100)
- role (ENUM: admin, user)
- created_at (TIMESTAMP)
```

### Tabel `buku`
```sql
- id (INT PRIMARY KEY)
- judul (VARCHAR 255)
- penulis (VARCHAR 100)
- penerbit (VARCHAR 100)
- tahun_terbit (INT)
- isbn (VARCHAR 20)
- kategori (VARCHAR 50)
- stok (INT)
- deskripsi (TEXT)
- cover_image (VARCHAR 255)
- created_by (INT FOREIGN KEY)
- created_at (TIMESTAMP)
- updated_at (TIMESTAMP)
```

## Cara Menggunakan

### Setup Awal

1. **Pastikan XAMPP/Web Server berjalan**
   - Jalankan Apache dan MySQL

2. **Akses Aplikasi**
   - Buka browser: `http://localhost/uts1`
   - Database akan otomatis dibuat saat pertama kali diakses (melalui db.php)
   - User default akan otomatis dibuat

3. **Login**
   - Masukkan username dan password
   - Centang checkbox "Ingat Saya"
   - Klik tombol Login

### Workflow Admin

#### Menambah Buku
1. Login dengan akun admin
2. Pilih menu "Tambah Buku"
3. Isi form dengan detail buku:
   - Judul (wajib)
   - Penulis (wajib)
   - Penerbit (opsional)
   - Tahun Terbit
   - ISBN
   - Kategori
   - Stok
   - Deskripsi
   - Cover Gambar (JPG, PNG, GIF, max 5MB)
4. Klik "Simpan Buku"

#### Melihat Daftar Buku
1. Pilih menu "Daftar Buku"
2. Sistem menampilkan semua buku dalam tabel
3. Setiap buku menampilkan:
   - Cover image
   - Judul, Penulis, Penerbit
   - Tahun terbit, Kategori, Stok

#### Mengedit Buku
1. Di halaman "Daftar Buku", klik tombol "Edit" pada buku yang ingin diubah
2. Ubah informasi yang diperlukan
3. Jika ingin mengubah cover, upload gambar baru
4. Klik "Simpan Perubahan"

#### Menghapus Buku
1. Di halaman "Daftar Buku", klik tombol "Hapus" pada buku yang ingin dihapus
2. Konfirmasi penghapusan
3. Sistem akan menghapus buku dan cover image-nya

## File-file Penting

```
uts1/
├── db.php                 # Konfigurasi database & inisialisasi
├── login.php              # Halaman login
├── register.php           # Halaman registrasi
├── proses_login.php       # Proses autentikasi login
├── logout.php             # Logout & destroy session
├── perpustakaan.php       # Dashboard admin
├── dashboard_user.php     # Dashboard user biasa
├── tambah_buku.php        # Form tambah buku (CRUD Create)
├── daftar_buku.php        # List buku & delete (CRUD Read/Delete)
├── edit_buku.php          # Form edit buku (CRUD Update)
├── profil_user.php        # Profil user
├── pengaturan.php         # Halaman pengaturan admin
├── setup_database.sql     # Script SQL untuk setup manual
└── profiles/
    └── books/             # Folder upload cover buku (auto-created)
```

## Fitur Keamanan

✅ **Password Hashing** - Password di-hash menggunakan `password_hash()` dengan algorithm bcrypt
✅ **Session Management** - Menggunakan session PHP dengan cookie remember-me
✅ **Input Validation** - Validasi input pada form client & server side
✅ **SQL Injection Prevention** - Menggunakan prepared statement dengan parameterized queries
✅ **File Upload Security** - Validasi tipe & ukuran file sebelum upload
✅ **Role-based Access Control** - Proteksi halaman berdasarkan role user
✅ **CSRF Protection** - Form POST untuk operasi sensitif

## Troubleshooting

### Database tidak terbuat
- Pastikan MySQL running
- Cek kredensial database di `db.php`
- Default: host=localhost, user=root, password=(kosong)

### Gagal upload gambar
- Pastikan folder `profiles/books/` memiliki permission write (755 atau 777)
- Ukuran gambar < 5MB
- Format: JPG, PNG, atau GIF

### Buku tidak muncul di list
- Cek apakah ada data di tabel `buku`
- Verifikasi query SQL di `daftar_buku.php`

### Login gagal
- Pastikan checkbox "Ingat Saya" dicentang
- Username dan password harus sesuai (case-sensitive)
- Database sudah berisi user default

## Developer Notes

- Framework: PHP Native (No Framework)
- Database: MySQL
- UI Framework: AdminLTE 3
- Styling: Bootstrap 4 + Custom CSS
- Icons: Font Awesome 5

## Lisensi

Gratis untuk keperluan pembelajaran & akademik.

---

**LitSpace © 2026** - Perpustakaan Digital untuk Semua

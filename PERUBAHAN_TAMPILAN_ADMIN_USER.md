# Ringkasan Perubahan

## 1. Login berbeda untuk Admin dan User
- `proses_login.php` sekarang mengarahkan (redirect) sesuai `role` dari tabel `users`:
  - **admin** → `perpustakaan.php` (Panel Admin: sidebar lengkap, statistik, kelola semua buku, kelola user)
  - **user** → `dashboard_user.php` (Panel User: tampilan profil sederhana + akses lihat/tambah buku)
- Kedua halaman diberi badge visual "👑 Panel Admin" / "🙂 Panel User" agar terlihat jelas beda.
- `pengaturan.php` (kelola user) sekarang **hanya bisa diakses admin**; user otomatis dilempar ke panel-nya.
- `edit_buku.php` dan `hapus_buku.php` tetap khusus admin (hapus_buku.php sebelumnya tidak ada proteksi sama sekali — sudah diperbaiki).
- `daftar_buku.php`: tombol **Edit/Hapus** hanya muncul untuk admin; user hanya bisa melihat.

Login default (dibuat otomatis oleh `db.php` saat pertama kali dibuka):
- Admin: `admin` / `admin123`
- User: `user` / `user123`

## 2. Insert Foto (Cover Buku)
- `tambah_buku.php`: ditambahkan field **upload foto cover** (jpg/png/gif/webp, maks 5MB) lengkap dengan preview sebelum submit.
- `daftar_buku.php`: menampilkan **thumbnail cover** di kolom tabel (jika belum ada foto, tampil ikon default 📕).
- `edit_buku.php` sebelumnya sudah mendukung ganti cover — tetap dipertahankan.
- Foto disimpan di folder `profiles/books/`.

## 3. Perbaikan Database (penting!)
Sebelumnya project ini punya **bug besar**: `tambah_buku.php`/`daftar_buku.php`/`hapus_buku.php` menyimpan/membaca dari tabel `books`, sedangkan `edit_buku.php` membaca dari tabel `buku` — dua tabel berbeda yang tidak pernah sinkron. Ini sudah **disatukan** menjadi satu tabel `buku`.

Struktur tabel final ada di `setup_database.sql` (dan otomatis dibuat oleh `db.php`):

```sql
users (id, username, password, full_name, hp, role, created_at)
buku  (id, judul, penulis, penerbit, tahun_terbit, isbn, kategori,
       status, stok, deskripsi, cover_image, file_path, ditambahkan_oleh, created_at)
```

Cara pakai:
- **Otomatis**: cukup akses project via browser (XAMPP/Laragon), `db.php` akan membuat database `uts_perpus` beserta kedua tabel & akun default jika belum ada.
- **Manual**: import `setup_database.sql` lewat phpMyAdmin.

## File yang diubah
`db.php`, `proses_login.php`, `perpustakaan.php`, `dashboard_user.php`, `tambah_buku.php`,
`daftar_buku.php`, `hapus_buku.php`, `edit_buku.php`, `pengaturan.php`, `setup_database.sql`

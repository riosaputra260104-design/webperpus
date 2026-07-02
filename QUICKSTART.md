# 🚀 Quick Start Guide - LitSpace

## 5 Langkah untuk Mulai Menggunakan LitSpace

### 1️⃣ Start Web Server
```
Windows: Buka XAMPP Control Panel → Start Apache & MySQL
Linux: sudo service apache2 start && sudo service mysql start
Mac: brew services start apache2 && brew services start mysql
```

### 2️⃣ Akses Aplikasi
```
Buka browser: http://localhost/uts1
Tunggu database terbuat otomatis (first access)
```

### 3️⃣ Login
```
Username: admin
Password: admin123
Centang checkbox "Ingat Saya"
Klik Login
```

### 4️⃣ Tambah Buku Pertama
```
1. Klik menu "Tambah Buku"
2. Isi form:
   - Judul: Buku Pertama
   - Penulis: Si Pembuat
   - Penerbit: Penerbit Anda
   - Tahun: 2026
   - Kategori: Umum
   - Stok: 5
3. Upload cover image (opsional)
4. Klik "Simpan Buku"
```

### 5️⃣ Kelola Buku
```
✅ READ: Lihat semua buku di "Daftar Buku"
✏️ UPDATE: Klik tombol "Edit" pada buku
🗑️ DELETE: Klik tombol "Hapus" pada buku
➕ CREATE: Tambah buku baru di "Tambah Buku"
```

---

## Menu Admin

| Menu | Akses | Fungsi |
|------|-------|--------|
| 📚 Beranda | Admin | Dashboard & statistik |
| 📖 Daftar Buku | Admin | Lihat, Edit, Hapus buku (READ, UPDATE, DELETE) |
| ➕ Tambah Buku | Admin | Tambah buku baru (CREATE) |
| ⚙️ Pengaturan | Admin | Ubah password & upload foto profil |
| 🚪 Logout | Semua | Keluar dari sistem |

---

## Struktur CRUD

```
CREATE (Buat)
├─ File: tambah_buku.php
├─ Form: Judul, Penulis, Penerbit, dll
├─ Upload: Cover image
└─ Simpan: ke database

READ (Baca)
├─ File: daftar_buku.php
├─ Tampil: Tabel semua buku
├─ Sort: Terbaru dulu
└─ Display: Cover image, info buku

UPDATE (Edit)
├─ File: edit_buku.php
├─ Form: Pre-filled data lama
├─ Upload: Cover baru
└─ Update: ke database

DELETE (Hapus)
├─ File: daftar_buku.php
├─ Konfirmasi: Yakin hapus?
├─ Hapus: Data & cover image
└─ Redirect: ke list buku
```

---

## Default Credentials

### Admin Account
```
Username: admin
Password: admin123
Access: Full (semua fitur)
```

### User Account
```
Username: user
Password: user123
Access: Limited (dashboard only)
```

**⚠️ Untuk keamanan, ubah password setelah instalasi!**

---

## Common Tasks

### Tambah Buku dengan Cover
1. Menu: Tambah Buku
2. Isi semua field yang diperlukan
3. Upload gambar cover (JPG/PNG/GIF max 5MB)
4. Klik "Simpan Buku" ✅

### Edit Buku Existing
1. Menu: Daftar Buku
2. Temukan buku di tabel
3. Klik tombol "Edit"
4. Ubah field yang ingin diubah
5. Klik "Simpan Perubahan" ✅

### Hapus Buku
1. Menu: Daftar Buku
2. Temukan buku di tabel
3. Klik tombol "Hapus"
4. Konfirmasi hapus
5. Buku terhapus dari list ✅

### Ubah Password Admin
1. Menu: Pengaturan
2. Scroll ke "Ubah Password"
3. Isi password lama
4. Isi password baru (min 6 karakter)
5. Konfirmasi password baru
6. Klik "Ubah" ✅

---

## Browser Support

✅ Chrome (latest)
✅ Firefox (latest)
✅ Safari (latest)
✅ Edge (latest)
✅ Responsive untuk mobile/tablet

---

## Features

- ✅ Login dengan database
- ✅ CRUD lengkap (Create, Read, Update, Delete)
- ✅ Upload cover image
- ✅ Responsive design
- ✅ User-friendly interface
- ✅ Security (password hashing, SQL injection prevention)
- ✅ Session management
- ✅ Role-based access control

---

## Troubleshooting

### Login tidak bisa
→ Pastikan checkbox "Ingat Saya" dicentang
→ Username & password sesuai

### Buku tidak muncul
→ Pastikan sudah ada data di database
→ Refresh halaman (F5)

### Upload gambar gagal
→ Ukuran < 5MB
→ Format: JPG, PNG, atau GIF
→ Folder `profiles/books/` ada permission write

### Database tidak terbuat
→ Pastikan MySQL running
→ Cek kredensial di db.php

---

## File Penting

```
uts1/
├── db.php              ← Database connection
├── login.php           ← Login page
├── tambah_buku.php     ← Add books (CREATE)
├── daftar_buku.php     ← List books (READ, DELETE)
├── edit_buku.php       ← Edit books (UPDATE)
├── logout.php          ← Logout
├── profiles/
│   └── books/          ← Cover images
└── README.md           ← Full documentation
```

---

## Next Steps

1. ✅ Login & explore interface
2. ✅ Tambah beberapa buku test
3. ✅ Try edit & delete functionality
4. ✅ Customize sesuai kebutuhan
5. ✅ Deploy ke production (optional)

---

## Need Help?

📖 Baca: README.md (dokumentasi lengkap)
🔧 Setup: INSTALLATION.md (petunjuk instalasi)
📋 CRUD: CRUD_FEATURES.md (detail CRUD operations)

---

**Selamat menggunakan LitSpace!** 📚✨

**Happy reading & coding!**

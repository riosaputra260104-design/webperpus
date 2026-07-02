# 🧪 Testing Guide - LitSpace

## Sebelum Testing

Pastikan:
- ✅ XAMPP/Web server running
- ✅ MySQL service running
- ✅ Akses `http://localhost/uts1`
- ✅ Buka dengan browser modern (Chrome/Firefox/Safari)

---

## 📋 Test Checklist

### Phase 1: Authentication

#### Test 1.1 - Login Admin
```
Status: PASS / FAIL
Steps:
1. Buka http://localhost/uts1
2. Lihat halaman login dengan form
3. Username: admin
4. Password: admin123
5. Centang "Ingat Saya"
6. Klik Login
7. Redirect ke perpustakaan.php (dashboard)

Expected: Login berhasil, session terbuat
```

#### Test 1.2 - Login User Biasa
```
Status: PASS / FAIL
Steps:
1. Logout dulu (jika sudah login)
2. Buka http://localhost/uts1/login.php
3. Username: user
4. Password: user123
5. Centang "Ingat Saya"
6. Klik Login
7. Redirect ke dashboard_user.php

Expected: Login berhasil sebagai user
```

#### Test 1.3 - Login Invalid
```
Status: PASS / FAIL
Steps:
1. Username: admin
2. Password: salah
3. Klik Login
4. Lihat error message

Expected: "Username atau password salah"
```

#### Test 1.4 - Registrasi User Baru
```
Status: PASS / FAIL
Steps:
1. Klik "Daftar di sini"
2. Username: testuser
3. Full Name: Test User
4. Password: test123456
5. Confirm Password: test123456
6. Klik Register
7. Lihat success message
8. Login dengan akun baru

Expected: User berhasil terdaftar di database
```

#### Test 1.5 - Logout
```
Status: PASS / FAIL
Steps:
1. Login sebagai admin
2. Klik menu "Logout"
3. Redirect ke login.php
4. Session destroyed

Expected: Logout berhasil, tidak bisa akses halaman admin
```

---

### Phase 2: CRUD Operations

#### Test 2.1 - CREATE: Tambah Buku
```
Status: PASS / FAIL
Steps:
1. Login sebagai admin
2. Klik "Tambah Buku"
3. Isi form:
   Judul: "Buku Test 1"
   Penulis: "Penulis Test"
   Penerbit: "Penerbit Test"
   Tahun Terbit: 2026
   ISBN: 978-1234567890
   Kategori: Teknologi
   Stok: 10
   Deskripsi: "Ini adalah buku test"
4. Upload cover image (opsional)
5. Klik "Simpan Buku"
6. Lihat success message
7. Redirect ke tambah_buku.php

Expected: Buku berhasil ditambah ke database
```

#### Test 2.2 - READ: Lihat Daftar Buku
```
Status: PASS / FAIL
Steps:
1. Login sebagai admin
2. Klik "Daftar Buku"
3. Lihat tabel dengan semua buku
4. Cek kolom: No, Cover, Judul, Penulis, Penerbit, Tahun, Kategori, Stok
5. Lihat cover image dari buku test (jika di-upload)
6. Cek tombol Edit & Hapus

Expected: Daftar buku muncul dengan semua data dari database
```

#### Test 2.3 - UPDATE: Edit Buku
```
Status: PASS / FAIL
Steps:
1. Di halaman "Daftar Buku"
2. Cari buku "Buku Test 1"
3. Klik tombol "Edit"
4. Ubah Judul: "Buku Test 1 - Edited"
5. Ubah Stok: 20
6. Ubah Kategori: Fiksih
7. Klik "Simpan Perubahan"
8. Lihat success message
9. Cek daftar buku, data ter-update

Expected: Buku ter-update di database
```

#### Test 2.4 - UPDATE: Edit Cover Image
```
Status: PASS / FAIL
Steps:
1. Edit buku (dari Test 2.3)
2. Lihat preview cover image lama
3. Upload cover image baru
4. Klik "Simpan Perubahan"
5. Cek folder profiles/books/ (file lama seharusnya terhapus)
6. Di daftar, cover baru seharusnya tampil

Expected: Cover image ter-update, file lama terhapus
```

#### Test 2.5 - DELETE: Hapus Buku
```
Status: PASS / FAIL
Steps:
1. Di halaman "Daftar Buku"
2. Cari buku yang mau dihapus
3. Klik tombol "Hapus"
4. Lihat konfirmasi: "Yakin ingin menghapus buku ini?"
5. Klik OK
6. Lihat success message
7. Redirect ke daftar_buku.php
8. Buku seharusnya tidak ada di list

Expected: Buku ter-delete dari database & cover image terhapus
```

#### Test 2.6 - Empty State
```
Status: PASS / FAIL
Steps:
1. Hapus semua buku
2. Buka halaman "Daftar Buku"
3. Lihat pesan: "Belum ada buku"

Expected: Empty state message tampil
```

---

### Phase 3: File Upload & Validation

#### Test 3.1 - Upload Valid Image
```
Status: PASS / FAIL
Steps:
1. Tambah buku baru
2. Upload gambar: JPG, PNG, atau GIF
3. Size: < 5MB
4. Lihat preview di halaman edit
5. Lihat di daftar buku

Expected: File uploaded & tampil di daftar
```

#### Test 3.2 - Upload Invalid Type
```
Status: PASS / FAIL
Steps:
1. Tambah buku baru
2. Upload file: PDF, TXT, atau format non-image
3. Submit form

Expected: Error message: "Tipe file gambar harus JPG, PNG, atau GIF"
```

#### Test 3.3 - Upload File Terlalu Besar
```
Status: PASS / FAIL
Steps:
1. Tambah buku baru
2. Upload file > 5MB
3. Submit form

Expected: Error message: "Ukuran gambar tidak boleh lebih dari 5MB"
```

#### Test 3.4 - File Cleanup on Delete
```
Status: PASS / FAIL
Steps:
1. Tambah buku dengan cover image
2. Check folder profiles/books/ → file ada
3. Hapus buku
4. Check folder profiles/books/ → file seharusnya terhapus

Expected: File cover image terhapus dari folder
```

---

### Phase 4: Security & Access Control

#### Test 4.1 - SQL Injection Prevention
```
Status: PASS / FAIL
Steps:
1. Login field, coba masukkan: admin' OR '1'='1
2. Field tidak vulnerable

Expected: Login ditolak, error message normal
```

#### Test 4.2 - User Cannot Access Admin Pages
```
Status: PASS / FAIL
Steps:
1. Login sebagai user (user/user123)
2. Coba akses: http://localhost/uts1/daftar_buku.php
3. Seharusnya redirect ke tambah_buku.php

Expected: User tidak bisa akses admin pages
```

#### Test 4.3 - Non-Login Cannot Access Protected Pages
```
Status: PASS / FAIL
Steps:
1. Logout
2. Coba akses: http://localhost/uts1/perpustakaan.php
3. Redirect ke login.php

Expected: Harus login untuk akses pages
```

#### Test 4.4 - Session Validation
```
Status: PASS / FAIL
Steps:
1. Login sebagai admin
2. Clear browser cookies
3. Coba refresh perpustakaan.php
4. Redirect ke login

Expected: Session invalid tanpa session/cookie
```

---

### Phase 5: Input Validation

#### Test 5.1 - Required Field Validation
```
Status: PASS / FAIL
Steps:
1. Tambah buku
2. Kosongkan "Judul"
3. Submit form

Expected: Error "Judul dan Penulis harus diisi"
```

#### Test 5.2 - Data Sanitization
```
Status: PASS / FAIL
Steps:
1. Edit buku
2. Judul: <script>alert('test')</script>
3. Submit

Expected: Script tidak execute, data di-escape
```

---

### Phase 6: Database Operations

#### Test 6.1 - Data Persistence
```
Status: PASS / FAIL
Steps:
1. Tambah 5 buku
2. Refresh halaman
3. Lihat semua 5 buku masih ada

Expected: Data persisten di database
```

#### Test 6.2 - Data Integrity
```
Status: PASS / FAIL
Steps:
1. Cek database: SELECT * FROM buku
2. Verifikasi semua field ter-input dengan benar
3. Cek created_by field (user_id)

Expected: Data integritas terjaga di database
```

---

### Phase 7: UI/UX & Responsiveness

#### Test 7.1 - Desktop Responsive
```
Status: PASS / FAIL
Steps:
1. Buka di browser desktop
2. Lihat layout
3. Sidebar navigation
4. Buttons & forms
5. Tables
6. Scrolling & functionality

Expected: UI tampil sempurna
```

#### Test 7.2 - Mobile Responsive
```
Status: PASS / FAIL
Steps:
1. Resize browser ke mobile size (320px width)
2. Atau buka di mobile device
3. Lihat layout mobile-friendly
4. Sidebar responsive
5. Forms readable
6. Buttons clickable

Expected: UI responsive & usable di mobile
```

#### Test 7.3 - Cross-Browser
```
Status: PASS / FAIL
Browsers:
- Chrome: PASS/FAIL
- Firefox: PASS/FAIL
- Safari: PASS/FAIL
- Edge: PASS/FAIL

Expected: Semua browser compatible
```

---

## 📊 Test Report Template

```
═══════════════════════════════════════════════════════════
                    TEST REPORT
═══════════════════════════════════════════════════════════

Date: _______________
Tester: _____________
Browser: ____________
OS: __________________

SUMMARY
├─ Total Test Cases: 25
├─ Passed: ___/25
├─ Failed: ___/25
└─ Success Rate: ____%

PHASE RESULTS
├─ Phase 1 (Auth): ___/5 PASS
├─ Phase 2 (CRUD): ___/6 PASS
├─ Phase 3 (Upload): ___/4 PASS
├─ Phase 4 (Security): ___/4 PASS
├─ Phase 5 (Validation): ___/2 PASS
├─ Phase 6 (Database): ___/2 PASS
└─ Phase 7 (UI/UX): ___/2 PASS

CRITICAL ISSUES: _______________
MINOR ISSUES: _______________
NOTES: _______________

Status: PASS / FAIL
═══════════════════════════════════════════════════════════
```

---

## 🐛 Debugging Tips

### Lihat Error Message
- Buka browser DevTools (F12)
- Console tab → lihat error messages
- Network tab → lihat request/response

### Database Query
```sql
-- Login ke MySQL
mysql -u root -p

-- Query buku
USE uts_perpus;
SELECT * FROM buku;
SELECT * FROM users;

-- Check uploaded files
SELECT cover_image FROM buku WHERE id = 1;
```

### PHP Error Log
- Windows: Check PHP error log di XAMPP folder
- Linux: `/var/log/apache2/error.log`

### Common Issues
1. **404 Not Found** → File tidak ada di path
2. **500 Internal Server Error** → PHP syntax error
3. **Database connection error** → MySQL tidak running
4. **File upload error** → Permission issue atau file size
5. **Session not working** → Cookie disabled di browser

---

## ✅ Final Checklist Sebelum Go Live

- [ ] Semua 25 test cases PASS
- [ ] Database backup created
- [ ] Security test completed
- [ ] Performance test done
- [ ] Browser compatibility verified
- [ ] Mobile responsiveness confirmed
- [ ] Error handling tested
- [ ] File permissions set correctly
- [ ] Password default changed
- [ ] Backup procedure documented

---

**Test Status:** _____________

**Approved By:** _______________

**Date:** _______________

---

Jika ada kegagalan test, dokumentasikan dan laporkan issue untuk diperbaiki.

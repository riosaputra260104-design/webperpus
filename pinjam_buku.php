<?php
session_start();
include_once 'db.php';

if (!isset($_SESSION['username']) && isset($_COOKIE['user'])) {
    $_SESSION['username'] = $_COOKIE['user'];
    $_SESSION['role'] = $_COOKIE['role'] ?? 'user';
}
if (!isset($_SESSION['username'])) {
    header("Location: login.php?error=belum_login");
    exit();
}

$conn = get_db_connection();
$username = $_SESSION['username'];
$bukuId = isset($_POST['buku_id']) ? (int) $_POST['buku_id'] : 0;
$lama = 7; // masa pinjam default: 7 hari

if ($bukuId <= 0) {
    header('Location: daftar_buku.php?error=' . urlencode('Buku tidak ditemukan.'));
    exit();
}

// Pastikan buku ada & stok masih tersedia
$stmt = $conn->prepare('SELECT stok FROM buku WHERE id = ?');
$stmt->bind_param('i', $bukuId);
$stmt->execute();
$stmt->bind_result($stok);
$found = $stmt->fetch();
$stmt->close();

if (!$found) {
    header('Location: daftar_buku.php?error=' . urlencode('Buku tidak ditemukan.'));
    exit();
}

// Cek apakah user ini sudah meminjam buku yang sama dan belum dikembalikan
$stmtCek = $conn->prepare("SELECT id FROM peminjaman WHERE buku_id = ? AND username = ? AND status = 'dipinjam'");
$stmtCek->bind_param('is', $bukuId, $username);
$stmtCek->execute();
$stmtCek->store_result();
$sudahPinjam = $stmtCek->num_rows > 0;
$stmtCek->close();

if ($sudahPinjam) {
    header('Location: daftar_buku.php?error=' . urlencode('Kamu sudah meminjam buku ini dan belum mengembalikannya.'));
    exit();
}

if ((int) $stok <= 0) {
    header('Location: daftar_buku.php?error=' . urlencode('Stok buku sedang habis.'));
    exit();
}

$tanggalPinjam = date('Y-m-d');
$tanggalKembali = date('Y-m-d', strtotime("+{$lama} days"));

$stmtInsert = $conn->prepare('INSERT INTO peminjaman (buku_id, username, tanggal_pinjam, tanggal_kembali, status) VALUES (?, ?, ?, ?, "dipinjam")');
$stmtInsert->bind_param('isss', $bukuId, $username, $tanggalPinjam, $tanggalKembali);
$stmtInsert->execute();
$stmtInsert->close();

$stmtStok = $conn->prepare('UPDATE buku SET stok = stok - 1 WHERE id = ? AND stok > 0');
$stmtStok->bind_param('i', $bukuId);
$stmtStok->execute();
$stmtStok->close();

header('Location: buku_saya.php?success=' . urlencode('Buku berhasil dipinjam. Batas pengembalian: ' . date('d M Y', strtotime($tanggalKembali))));
exit();

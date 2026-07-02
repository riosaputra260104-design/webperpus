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
$isAdmin = isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$redirectTo = $isAdmin ? 'daftar_peminjaman.php' : 'buku_saya.php';

if ($id <= 0) {
    header("Location: {$redirectTo}");
    exit();
}

// Ambil data peminjaman, pastikan milik user ini (kecuali admin)
$stmt = $conn->prepare('SELECT buku_id, username, status FROM peminjaman WHERE id = ?');
$stmt->bind_param('i', $id);
$stmt->execute();
$res = $stmt->get_result();
$pinjaman = $res->fetch_assoc();
$stmt->close();

if (!$pinjaman) {
    header("Location: {$redirectTo}?error=" . urlencode('Data peminjaman tidak ditemukan.'));
    exit();
}

if (!$isAdmin && $pinjaman['username'] !== $_SESSION['username']) {
    header("Location: {$redirectTo}?error=" . urlencode('Kamu tidak berhak mengubah data ini.'));
    exit();
}

if ($pinjaman['status'] === 'dipinjam') {
    $today = date('Y-m-d');
    $stmtUpdate = $conn->prepare("UPDATE peminjaman SET status = 'dikembalikan', tanggal_dikembalikan = ? WHERE id = ?");
    $stmtUpdate->bind_param('si', $today, $id);
    $stmtUpdate->execute();
    $stmtUpdate->close();

    $stmtStok = $conn->prepare('UPDATE buku SET stok = stok + 1 WHERE id = ?');
    $stmtStok->bind_param('i', $pinjaman['buku_id']);
    $stmtStok->execute();
    $stmtStok->close();
}

header("Location: {$redirectTo}?success=" . urlencode('Buku berhasil dikembalikan.'));
exit();

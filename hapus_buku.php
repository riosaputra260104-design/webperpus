<?php
session_start();
include_once 'db.php';

if (!isset($_SESSION['username']) && isset($_COOKIE['user'])) {
    $_SESSION['username'] = $_COOKIE['user'];
    $_SESSION['role'] = $_COOKIE['role'] ?? 'user';
}
if (!isset($_SESSION['username'])) {
    header('Location: login.php');
    exit();
}
// Hanya admin yang boleh menghapus buku
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: daftar_buku.php');
    exit();
}

$conn = get_db_connection();

// Cek apakah ada ID buku yang dikirim lewat URL
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $id = (int) $_GET['id'];

    // Hapus file cover & pdf terkait dari disk (kalau ada) sebelum hapus data
    $stmtFind = $conn->prepare("SELECT cover_image, file_path FROM buku WHERE id = ?");
    $stmtFind->bind_param("i", $id);
    $stmtFind->execute();
    $res = $stmtFind->get_result();
    if ($row = $res->fetch_assoc()) {
        if (!empty($row['cover_image']) && file_exists('profiles/books/' . $row['cover_image'])) {
            unlink('profiles/books/' . $row['cover_image']);
        }
        if (!empty($row['file_path']) && file_exists($row['file_path'])) {
            unlink($row['file_path']);
        }
    }
    $stmtFind->close();

    // Query untuk menghapus buku berdasarkan ID dari tabel buku
    $stmt = $conn->prepare("DELETE FROM buku WHERE id = ?");
    $stmt->bind_param("i", $id);
    $query = $stmt->execute();
    $stmt->close();

    if ($query) {
        // Jika berhasil, kembali ke daftar_buku.php dengan alert sukses
        echo "<script>
                alert('Buku berhasil dihapus!');
                window.location.href = 'daftar_buku.php';
              </script>";
    } else {
        // Jika gagal
        echo "<script>
                alert('Gagal menghapus buku.');
                window.location.href = 'daftar_buku.php';
              </script>";
    }
} else {
    // Jika tidak ada ID di URL, kembalikan ke halaman daftar buku
    header("Location: daftar_buku.php");
    exit();
}
?>
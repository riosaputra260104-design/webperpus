<?php
session_start();
include_once 'db.php';

if (!isset($_SESSION['login'])) {
    header('Location: login.php');
    exit;
}

if (!isset($_SESSION['user']) && isset($_COOKIE['user'])) {
    $_SESSION['user'] = $_COOKIE['user'];
    $_SESSION['login'] = true;
}

$conn = get_db_connection();
$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

if ($id > 0) {
    $stmt = mysqli_prepare($conn, 'SELECT username FROM users WHERE id = ?');
    mysqli_stmt_bind_param($stmt, 'i', $id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_bind_result($stmt, $username);
    mysqli_stmt_fetch($stmt);
    mysqli_stmt_close($stmt);

    if ($username === $_SESSION['user']) {
        header('Location: pengaturan.php?error=Tidak dapat menghapus akun yang sedang Anda gunakan.');
        exit;
    }

    $result = mysqli_query($conn, 'SELECT COUNT(*) AS total FROM users');
    $row = mysqli_fetch_assoc($result);
    if ($row['total'] <= 1) {
        header('Location: pengaturan.php?error=Minimal harus ada satu pengguna.');
        exit;
    }

    $stmt = mysqli_prepare($conn, 'DELETE FROM users WHERE id = ?');
    mysqli_stmt_bind_param($stmt, 'i', $id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
}

header('Location: pengaturan.php?success=Pengguna berhasil dihapus.');
exit;

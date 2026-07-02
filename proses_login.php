<?php
session_start();
require_once 'db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: login.php");
    exit();
}

$username = trim($_POST['username']);
$password = $_POST['password'];

// Cek apakah checkbox dicentang (karena di form login kamu bikin wajib)
if (!isset($_POST['ingatsaya'])) {
    header("Location: login.php?error=checkbox");
    exit();
}

$conn = get_db_connection();
$stmt = $conn->prepare("SELECT username, password, role FROM users WHERE username = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows === 1) {
    $stmt->bind_result($dbUsername, $dbPassword, $dbRole);
    $stmt->fetch();

    // Verifikasi password (memastikan password di database di-hash pakai password_hash)
    if (password_verify($password, $dbPassword)) {
        
        // SINKRONISASI: Kita pakai ['username'] supaya sama dengan gembok di semua halaman
        $_SESSION['username'] = $dbUsername;
        $_SESSION['user'] = $dbUsername; // alias, dipakai beberapa halaman lama
        $_SESSION['role'] = $dbRole;
        $_SESSION['login'] = true;

        // Set cookie jika "Ingat Saya" dicentang
        setcookie("user", $dbUsername, time() + 86400, "/");
        setcookie("role", $dbRole, time() + 86400, "/");

        $stmt->close();

        // TAMPILAN BERBEDA SESUAI ROLE:
        // admin -> Panel Admin (perpustakaan.php, kelola semua buku & user)
        // user  -> Panel User (dashboard_user.php, tampilan lebih sederhana)
        if ($dbRole === 'admin') {
            header("Location: perpustakaan.php");
        } else {
            header("Location: dashboard_user.php");
        }
        exit();
    }
}

// Jika username tidak ketemu atau password salah, tutup statement dan tendang balik
$stmt->close();
header("Location: login.php?error=invalid");
exit();
?>
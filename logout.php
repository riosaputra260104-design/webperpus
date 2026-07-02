<?php
session_start();

// Proteksi: Cek session sebelum logout
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

session_unset();
session_destroy();

// Hapus cookie
setcookie("user", "", time() - 3600, "/");
setcookie("role", "", time() - 3600, "/");
setcookie("password", "", time() - 3600, "/");

header("Location: login.php");
exit();
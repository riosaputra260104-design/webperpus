<?php
session_start();
include_once 'db.php';

// Memastikan fungsi sanitize sederhana tersedia jika belum ada di db.php
if (!function_exists('sanitize')) {
    function sanitize($data) {
        return htmlspecialchars(strip_tags(trim($data)));
    }
}

$conn = get_db_connection();
$action = $_GET['action'] ?? 'list';
$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$error = '';
$success = '';

if (isset($_GET['success'])) {
    $success = $_GET['success'];
}
if (isset($_GET['error'])) {
    $error = $_GET['error'];
}

if (!isset($_SESSION['username']) && isset($_COOKIE['user'])) {
    $_SESSION['username'] = $_COOKIE['user'];
    $_SESSION['role'] = $_COOKIE['role'] ?? 'user';
}
if (!isset($_SESSION['username'])) {
    header("Location: login.php?error=belum_login");
    exit(); 
}
// Halaman kelola user hanya untuk admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: dashboard_user.php");
    exit();
}
$editUser = null;

// Proses Update Data (POST). Penambahan pengguna baru dilakukan lewat halaman Register,
// jadi di Pengaturan admin hanya bisa mengedit atau menghapus data pengguna yang sudah ada.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $mode = $_POST['mode'] ?? '';

    if ($mode === 'edit') {
        $id = isset($_POST['id']) ? (int) $_POST['id'] : 0;
        $username = sanitize($_POST['username'] ?? '');
        $nama = sanitize($_POST['nama'] ?? '');
        $hp = sanitize($_POST['hp'] ?? '');
        $password = $_POST['password'] ?? '';

        if ($id <= 0 || $username === '' || $nama === '') {
            $error = 'Username dan nama lengkap harus diisi.';
        } else {
            $stmt = mysqli_prepare($conn, 'SELECT id FROM users WHERE username = ? AND id <> ?');
            mysqli_stmt_bind_param($stmt, 'si', $username, $id);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_store_result($stmt);
            if (mysqli_stmt_num_rows($stmt) > 0) {
                $error = 'Username sudah digunakan pengguna lain.';
            }
            mysqli_stmt_close($stmt);

            if (!$error) {
                if ($password !== '') {
                    if (strlen($password) < 5) {
                        $error = 'Password minimal 5 karakter.';
                    } else {
                        $hash = password_hash($password, PASSWORD_DEFAULT);
                        $stmt = mysqli_prepare($conn, 'UPDATE users SET username = ?, password = ?, full_name = ?, hp = ? WHERE id = ?');
                        mysqli_stmt_bind_param($stmt, 'ssssi', $username, $hash, $nama, $hp, $id);
                        mysqli_stmt_execute($stmt);
                        mysqli_stmt_close($stmt);
                    }
                } else {
                    $stmt = mysqli_prepare($conn, 'UPDATE users SET username = ?, full_name = ?, hp = ? WHERE id = ?');
                    mysqli_stmt_bind_param($stmt, 'sssi', $username, $nama, $hp, $id);
                    mysqli_stmt_execute($stmt);
                    mysqli_stmt_close($stmt);
                }

                if (!$error) {
                    $success = 'Data pengguna berhasil diperbarui.';
                    $action = 'list';
                }
            }
        }
    }
}

// Ambil data single user untuk Mode Edit
if ($action === 'edit' && $id > 0 && $_SERVER['REQUEST_METHOD'] !== 'POST') {
    $stmt = mysqli_prepare($conn, 'SELECT id, username, full_name AS nama, hp FROM users WHERE id = ?');
    mysqli_stmt_bind_param($stmt, 'i', $id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $editUser = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);

    if (!$editUser) {
        header('Location: pengaturan.php');
        exit;
    }
}

// Ambil data untuk tabel
mysqli_query($conn, "ALTER TABLE `users` ADD COLUMN IF NOT EXISTS `hp` VARCHAR(20) DEFAULT NULL AFTER `full_name`");

$result = mysqli_query($conn, 'SELECT id, username, full_name AS nama, hp, created_at FROM users ORDER BY id ASC');
if ($result) {
    $users = mysqli_fetch_all($result, MYSQLI_ASSOC);
} else {
    $users = [];
}

// Statistik ringkas pengguna, dipakai di panel sebelah kiri (menggantikan form Tambah Pengguna)
$totalUser = count($users);
$qAdmin = mysqli_query($conn, "SELECT COUNT(*) AS total FROM users WHERE role = 'admin'");
$totalAdmin = $qAdmin ? mysqli_fetch_assoc($qAdmin)['total'] : 0;
$totalUserBiasa = $totalUser - $totalAdmin;
$qPeminjamAktif = mysqli_query($conn, "SELECT COUNT(DISTINCT username) AS total FROM peminjaman WHERE status = 'dipinjam'");
$totalPeminjamAktif = $qPeminjamAktif ? mysqli_fetch_assoc($qPeminjamAktif)['total'] : 0;
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Pengaturan | LitSpace</title>
  <link rel="stylesheet" href="plugins/fontawesome-free/css/all.min.css">
  <link rel="stylesheet" href="dist/css/adminlte.min.css">
  <style>
    * { font-family: 'Poppins', sans-serif; }
    body { background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%); }
    .content-wrapper { background: transparent !important; }
    .brand-link { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 15px !important; font-size: 20px !important; font-weight: 700; letter-spacing: 1px; }
    .nav-link { transition: all 0.3s ease !important; border-left: 3px solid transparent; }
    .nav-link:hover { background-color: rgba(255,255,255,0.1) !important; border-left-color: #667eea !important; transform: translateX(5px); }
    .nav-link.active { background-color: rgba(102, 126, 234, 0.2) !important; border-left-color: #667eea !important; }
    .content-header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 25px 0 !important; box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3); margin-bottom: 25px; }
    .content-header h1 { font-size: 28px !important; font-weight: 700 !important; margin: 0 !important; }
    .card { border: none; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); transition: all 0.3s ease; margin-bottom: 20px; }
    .card:hover { transform: translateY(-3px); box-shadow: 0 8px 25px rgba(0,0,0,0.1); }
    .card-header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border: none; padding: 15px 20px !important; border-radius: 12px 12px 0 0 !important; }
    .card-body { padding: 20px !important; }
    .btn-primary { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border: none; border-radius: 8px; transition: all 0.3s ease; }
    .btn-primary:hover { background: linear-gradient(135deg, #5568d3 0%, #6a3f91 100%); transform: translateY(-2px); box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4); }
    .table { background: white; margin-bottom: 0; }
    .table thead th { background: linear-gradient(135deg, #f0f4ff 0%, #f8f5ff 100%); color: #667eea; font-weight: 700; border: none; padding: 12px !important; font-size: 13px; text-transform: uppercase; letter-spacing: 0.5px; }
    .table tbody td { padding: 12px !important; vertical-align: middle; font-size: 14px; color: #333; border: none; border-bottom: 1px solid #f0f0f0; }
    .table tbody tr:hover { background-color: #f8f9fc; }
    .table tbody tr:last-child td { border-bottom: none; }
    .main-footer { background: #2c3e50; color: white; padding: 15px !important; font-weight: 500; }
    .main-footer strong { color: #667eea; font-weight: 700; }
  </style>
</head>
<body class="hold-transition sidebar-mini layout-fixed">
<div class="wrapper">

  <nav class="main-header navbar navbar-expand navbar-white navbar-light">
    <ul class="navbar-nav">
      <li class="nav-item">
        <a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars"></i></a>
      </li>
    </ul>
  </nav>

  <aside class="main-sidebar sidebar-dark-primary elevation-4">
    <a href="perpustakaan.php" class="brand-link text-center">
      <span class="brand-text font-weight-light">📚 LitSpace</span>
    </a>
    <div class="sidebar">
      <nav class="mt-2">
        <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
          <li class="nav-item"><a href="perpustakaan.php" class="nav-link"><i class="nav-icon fas fa-home"></i><p>Beranda</p></a></li>
          <li class="nav-item"><a href="daftar_buku.php" class="nav-link"><i class="nav-icon fas fa-book"></i><p>Daftar Buku</p></a></li>
          <li class="nav-item"><a href="tambah_buku.php" class="nav-link"><i class="nav-icon fas fa-plus"></i><p>Tambah Buku</p></a></li>
          <li class="nav-item"><a href="daftar_peminjaman.php" class="nav-link"><i class="nav-icon fas fa-clipboard-list"></i><p>Buku Dipinjam</p></a></li>
          <li class="nav-item"><a href="pengaturan.php" class="nav-link active"><i class="nav-icon fas fa-users"></i><p>Pengaturan</p></a></li>
          <li class="nav-item"><a href="logout.php" class="nav-link text-danger"><i class="nav-icon fas fa-sign-out-alt"></i><p>Logout</p></a></li>
        </ul>
      </nav>
    </div>
  </aside>

  <div class="content-wrapper">
    <div class="content-header">
      <div class="container-fluid">
        <h1>Pengaturan</h1>
      </div>
    </div>
    
    <div class="content">
      <div class="container-fluid">
        <?php if ($error): ?>
          <div class="alert alert-danger alert-dismissible fade show">
            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
            <?= htmlspecialchars($error) ?>
          </div>
        <?php endif; ?>
        <?php if ($success): ?>
          <div class="alert alert-success alert-dismissible fade show">
            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
            <?= htmlspecialchars($success) ?>
          </div>
        <?php endif; ?>

        <div class="row">
          <div class="col-xl-4 col-lg-5">
            <?php if ($action === 'edit' && $editUser): ?>
              <div class="card">
                <div class="card-header">
                  <h3 class="card-title">Edit Pengguna</h3>
                </div>
                <div class="card-body">
                  <form method="POST">
                    <input type="hidden" name="mode" value="edit">
                    <input type="hidden" name="id" value="<?= htmlspecialchars($editUser['id']) ?>">
                    <div class="form-group">
                      <label>Username</label>
                      <input type="text" name="username" class="form-control" required value="<?= htmlspecialchars($editUser['username'] ?? '') ?>">
                    </div>
                    <div class="form-group">
                      <label>Nama Lengkap</label>
                      <input type="text" name="nama" class="form-control" required value="<?= htmlspecialchars($editUser['nama'] ?? '') ?>">
                    </div>
                    <div class="form-group">
                      <label>HP</label>
                      <input type="text" name="hp" class="form-control" value="<?= htmlspecialchars($editUser['hp'] ?? '') ?>">
                    </div>
                    <div class="form-group">
                      <label>Password <small class="text-muted">(kosongkan jika tidak diubah)</small></label>
                      <input type="password" name="password" class="form-control" autocomplete="new-password">
                    </div>
                    <button type="submit" class="btn btn-primary btn-block">Simpan Perubahan</button>
                    <a href="pengaturan.php" class="btn btn-secondary btn-block mt-2">Batal</a>
                  </form>
                </div>
              </div>
            <?php else: ?>
              <div class="card">
                <div class="card-header">
                  <h3 class="card-title">Ringkasan Pengguna</h3>
                </div>
                <div class="card-body">
                  <p style="font-size:14px;color:#555;">
                    Pengguna baru mendaftar sendiri lewat halaman <strong>Register</strong>. Di sini admin cukup
                    mengelola (edit / hapus) data pengguna yang sudah terdaftar.
                  </p>
                  <table class="table table-sm" style="margin-top:15px;">
                    <tbody>
                      <tr>
                        <td>Total Pengguna</td>
                        <td class="text-right"><strong><?= $totalUser ?></strong></td>
                      </tr>
                      <tr>
                        <td>Admin</td>
                        <td class="text-right"><strong><?= $totalAdmin ?></strong></td>
                      </tr>
                      <tr>
                        <td>User Biasa</td>
                        <td class="text-right"><strong><?= $totalUserBiasa ?></strong></td>
                      </tr>
                      <tr>
                        <td>Sedang Meminjam Buku</td>
                        <td class="text-right"><strong><?= $totalPeminjamAktif ?></strong></td>
                      </tr>
                    </tbody>
                  </table>
                  <a href="daftar_peminjaman.php" class="btn btn-primary btn-block mt-2">
                    <i class="fas fa-clipboard-list"></i> Lihat Buku Dipinjam
                  </a>
                </div>
              </div>
            <?php endif; ?>
          </div>

          <div class="col-xl-8 col-lg-7">
            <div class="card">
              <div class="card-header">
                <h3 class="card-title">Daftar Pengguna</h3>
              </div>
              <div class="card-body table-responsive p-0">
                <table class="table table-hover table-striped">
                  <thead>
                    <tr>
                      <th style="width: 40px;">#</th>
                      <th>Username</th>
                      <th>Nama</th>
                      <th>HP</th>
                      <th>Terdaftar</th>
                      <th style="width: 140px;" class="text-center">Aksi</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php if (!empty($users)): ?>
                      <?php foreach ($users as $index => $user): ?>
                        <tr>
                          <td><?= $index + 1 ?></td>
                          <td><strong><?= htmlspecialchars($user['username']) ?></strong></td>
                          <td><?= htmlspecialchars($user['nama']) ?></td>
                          <td><?= htmlspecialchars($user['hp'] ?? '-') ?></td>
                          <td><small class="text-muted"><?= htmlspecialchars($user['created_at']) ?></small></td>
                          <td class="text-center">
                            <a href="pengaturan.php?action=edit&id=<?= $user['id'] ?>" class="btn btn-primary btn-xs mr-1"><i class="fas fa-edit"></i> Edit</a>
                            <a href="hapus_user.php?id=<?= $user['id'] ?>" class="btn btn-danger btn-xs" onclick="return confirm('Hapus pengguna ini?')"><i class="fas fa-trash"></i> Hapus</a>
                          </td>
                        </tr>
                      <?php endforeach; ?>
                    <?php else: ?>
                      <tr>
                        <td colspan="6" class="text-center">Tidak ada data pengguna.</td>
                      </tr>
                    <?php endif; ?>
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <footer class="main-footer text-center">
    <strong>LitSpace</strong> © 2026. All rights reserved.
  </footer>
</div>

<script src="plugins/jquery/jquery.min.js"></script>
<script src="plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="dist/js/adminlte.min.js"></script>
</body>
</html>
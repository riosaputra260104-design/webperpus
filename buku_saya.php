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

$isAdmin = isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
if ($isAdmin) {
    header('Location: daftar_peminjaman.php');
    exit();
}

$conn = get_db_connection();
$username = $_SESSION['username'];

$stmt = $conn->prepare(
    'SELECT p.id, p.tanggal_pinjam, p.tanggal_kembali, p.tanggal_dikembalikan, p.status,
            b.judul, b.penulis, b.cover_image
     FROM peminjaman p
     JOIN buku b ON b.id = p.buku_id
     WHERE p.username = ?
     ORDER BY p.status = "dipinjam" DESC, p.tanggal_pinjam DESC'
);
$stmt->bind_param('s', $username);
$stmt->execute();
$result = $stmt->get_result();
$pinjaman = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$error = $_GET['error'] ?? '';
$success = $_GET['success'] ?? '';
$today = date('Y-m-d');
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <title>Buku Saya | LitSpace</title>

  <link rel="stylesheet" href="plugins/fontawesome-free/css/all.min.css">
  <link rel="stylesheet" href="dist/css/adminlte.min.css">
  <style>
    * { font-family: 'Poppins', sans-serif; }
    body { background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%); min-height: 100vh; }
    .content-wrapper { background: transparent; }
    .main-header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
    .main-header .nav-link, .main-header .navbar-nav span { color: white !important; font-weight: 500; transition: all 0.3s ease; }
    .main-header .nav-link:hover { transform: translateY(-2px); }
    .text-danger { color: #ff6b6b !important; transition: all 0.3s ease; }
    .text-danger:hover { color: #ee5a52 !important; }
    .main-sidebar { background: linear-gradient(180deg, #2c3e50 0%, #34495e 100%); box-shadow: 2px 0 10px rgba(0,0,0,0.1); }
    .brand-link { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 15px !important; font-size: 20px !important; font-weight: 700; letter-spacing: 1px; }
    .nav-link { transition: all 0.3s ease !important; border-left: 3px solid transparent; margin-left: 5px; }
    .nav-link:hover { background-color: rgba(255,255,255,0.1) !important; border-left-color: #667eea !important; transform: translateX(5px); }
    .nav-link.active { background-color: rgba(102, 126, 234, 0.2) !important; border-left-color: #667eea !important; }
    .content-header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 30px 0 !important; box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3); margin-bottom: 30px; }
    .content-header h1 { font-size: 32px !important; font-weight: 700 !important; margin-bottom: 10px !important; }
    .card { border: none; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); transition: all 0.3s ease; margin-bottom: 20px; }
    .card:hover { transform: translateY(-5px); box-shadow: 0 8px 25px rgba(0,0,0,0.15); }
    .card-header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border: none; padding: 20px !important; border-radius: 12px 12px 0 0 !important; }
    .card-body { padding: 25px !important; }
    .btn-primary { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border: none; border-radius: 8px; transition: all 0.3s ease; }
    .btn-primary:hover { background: linear-gradient(135deg, #5568d3 0%, #6a3f91 100%); transform: translateY(-2px); box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4); }
    .table { background: white; }
    .table thead th { background: linear-gradient(135deg, #f0f4ff 0%, #f8f5ff 100%); color: #667eea; font-weight: 700; border: none; padding: 16px !important; font-size: 14px; text-transform: uppercase; letter-spacing: 0.5px; }
    .table tbody td { padding: 16px !important; vertical-align: middle; font-size: 14px; color: #333; border: none; border-bottom: 1px solid #f0f0f0; }
    .table tbody tr:hover { background-color: #f8f9fc; }
    .table tbody tr:last-child td { border-bottom: none; }
    .main-footer { background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%); color: white; padding: 20px !important; font-weight: 500; box-shadow: 0 -2px 10px rgba(0,0,0,0.1); }
    .main-footer strong { color: #667eea; font-weight: 700; }
    html, body { height: 100%; }
    .wrapper { min-height: 100vh; display: flex; flex-direction: column; }
    .main-sidebar { height: 100vh; position: fixed; }
    .content-wrapper { margin-left: 250px; min-height: 100vh; flex: 1; }
    .badge-late { background: #fee2e2; color: #dc2626; }
    .badge-active { background: #dbeafe; color: #1d4ed8; }
    .badge-done { background: #dcfce7; color: #15803d; }
  </style>
</head>

<body class="hold-transition sidebar-mini">
<div class="wrapper">

<!-- Sidebar -->
<aside class="main-sidebar sidebar-dark-primary elevation-4">
  <a href="dashboard_user.php" class="brand-link text-center">
    <span class="brand-text font-weight-light">📚 LitSpace</span>
  </a>

  <div class="sidebar">
    <div class="text-center mb-2">
      <span class="badge badge-info">🙂 Panel User</span>
    </div>
    <nav>
      <ul class="nav nav-pills nav-sidebar flex-column">
        <li class="nav-item">
          <a href="dashboard_user.php" class="nav-link">
            <i class="nav-icon fas fa-home"></i>
            <p>Beranda</p>
          </a>
        </li>
        <li class="nav-item">
          <a href="daftar_buku.php" class="nav-link">
            <i class="nav-icon fas fa-book"></i>
            <p>Daftar Buku</p>
          </a>
        </li>
        <li class="nav-item">
          <a href="buku_saya.php" class="nav-link active">
            <i class="nav-icon fas fa-book-reader"></i>
            <p>Buku Saya</p>
          </a>
        </li>
        <li class="nav-item">
          <a href="logout.php" class="nav-link text-danger">
            <i class="nav-icon fas fa-sign-out-alt"></i>
            <p>Logout</p>
          </a>
        </li>
      </ul>
    </nav>
  </div>
</aside>

<!-- Content -->
<div class="content-wrapper">
  <div class="content-header">
    <div class="container-fluid">
      <h1>📖 Buku Saya</h1>
      <p class="text-muted" style="color:rgba(255,255,255,0.85) !important;">Daftar buku yang sedang dan pernah kamu pinjam</p>
    </div>
  </div>

  <div class="content">
    <div class="container-fluid">

      <?php if ($error): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
      <?php endif; ?>
      <?php if ($success): ?>
        <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
      <?php endif; ?>

      <div class="card">
        <div class="card-header">
          <h3 class="card-title">Riwayat Peminjaman</h3>
        </div>

        <div class="card-body table-responsive p-0">
          <table class="table table-hover table-striped">
            <thead>
              <tr>
                <th style="width: 40px;">No</th>
                <th>Judul Buku</th>
                <th>Penulis</th>
                <th>Tanggal Pinjam</th>
                <th>Batas Kembali</th>
                <th>Status</th>
                <th style="width: 150px;">Aksi</th>
              </tr>
            </thead>
            <tbody>
              <?php $no = 1; foreach ($pinjaman as $p): ?>
                <?php
                  $telat = $p['status'] === 'dipinjam' && $p['tanggal_kembali'] < $today;
                ?>
                <tr>
                  <td><?= $no++ ?></td>
                  <td><?= htmlspecialchars($p['judul']) ?></td>
                  <td><?= htmlspecialchars($p['penulis']) ?></td>
                  <td><?= date('d M Y', strtotime($p['tanggal_pinjam'])) ?></td>
                  <td><?= date('d M Y', strtotime($p['tanggal_kembali'])) ?></td>
                  <td>
                    <?php if ($p['status'] === 'dikembalikan'): ?>
                      <span class="badge badge-done">Sudah dikembalikan</span>
                    <?php elseif ($telat): ?>
                      <span class="badge badge-late">Terlambat</span>
                    <?php else: ?>
                      <span class="badge badge-active">Sedang dipinjam</span>
                    <?php endif; ?>
                  </td>
                  <td>
                    <?php if ($p['status'] === 'dipinjam'): ?>
                      <a href="kembalikan_buku.php?id=<?= $p['id'] ?>" class="btn btn-primary btn-sm" onclick="return confirm('Tandai buku ini sudah dikembalikan?')">Kembalikan</a>
                    <?php else: ?>
                      <span class="text-muted" style="font-size:12px;">Dikembalikan <?= $p['tanggal_dikembalikan'] ? date('d M Y', strtotime($p['tanggal_dikembalikan'])) : '' ?></span>
                    <?php endif; ?>
                  </td>
                </tr>
              <?php endforeach; ?>
              <?php if (empty($pinjaman)): ?>
                <tr><td colspan="7" class="text-center text-muted">Kamu belum pernah meminjam buku. Yuk pinjam buku di <a href="daftar_buku.php">Daftar Buku</a>.</td></tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>

    </div>
  </div>
</div>

<!-- Footer -->
<footer class="main-footer text-center">
  LitSpace © 2026
</footer>

</div>

<script src="plugins/jquery/jquery.min.js"></script>
<script src="plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="dist/js/adminlte.min.js"></script>

</body>
</html>

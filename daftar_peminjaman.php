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
// Halaman ini khusus admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: dashboard_user.php');
    exit();
}

$conn = get_db_connection();

$filter = $_GET['filter'] ?? 'semua';
$where = '';
if ($filter === 'dipinjam') {
    $where = "WHERE p.status = 'dipinjam'";
} elseif ($filter === 'terlambat') {
    $where = "WHERE p.status = 'dipinjam' AND p.tanggal_kembali < CURDATE()";
} elseif ($filter === 'dikembalikan') {
    $where = "WHERE p.status = 'dikembalikan'";
}

$sql = "SELECT p.id, p.username, p.tanggal_pinjam, p.tanggal_kembali, p.tanggal_dikembalikan, p.status,
               b.judul, b.penulis
        FROM peminjaman p
        JOIN buku b ON b.id = p.buku_id
        $where
        ORDER BY p.status = 'dipinjam' DESC, p.tanggal_pinjam DESC";
$result = mysqli_query($conn, $sql);
$daftar = $result ? mysqli_fetch_all($result, MYSQLI_ASSOC) : [];

// Statistik ringkas
$qTotalPinjam = mysqli_query($conn, "SELECT COUNT(*) AS total FROM peminjaman WHERE status = 'dipinjam'");
$totalSedangDipinjam = mysqli_fetch_assoc($qTotalPinjam)['total'];

$qTelat = mysqli_query($conn, "SELECT COUNT(*) AS total FROM peminjaman WHERE status = 'dipinjam' AND tanggal_kembali < CURDATE()");
$totalTelat = mysqli_fetch_assoc($qTelat)['total'];

$qSelesai = mysqli_query($conn, "SELECT COUNT(*) AS total FROM peminjaman WHERE status = 'dikembalikan'");
$totalSelesai = mysqli_fetch_assoc($qSelesai)['total'];

$error = $_GET['error'] ?? '';
$success = $_GET['success'] ?? '';
$today = date('Y-m-d');
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Daftar Buku Dipinjam | LitSpace</title>
  <link rel="stylesheet" href="plugins/fontawesome-free/css/all.min.css">
  <link rel="stylesheet" href="dist/css/adminlte.min.css">
  <style>
    * { font-family: 'Poppins', sans-serif; }
    body { background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%); min-height: 100vh; }
    .content-wrapper { background: transparent; }
    .main-header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
    .main-sidebar { background: linear-gradient(180deg, #2c3e50 0%, #34495e 100%); box-shadow: 2px 0 10px rgba(0,0,0,0.1); }
    .brand-link { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 15px !important; font-size: 20px !important; font-weight: 700; letter-spacing: 1px; }
    .nav-link { transition: all 0.3s ease !important; border-left: 3px solid transparent; margin-left: 5px; }
    .nav-link:hover { background-color: rgba(255,255,255,0.1) !important; border-left-color: #667eea !important; transform: translateX(5px); }
    .nav-link.active { background-color: rgba(102, 126, 234, 0.2) !important; border-left-color: #667eea !important; }
    .content-header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 30px 0 !important; box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3); margin-bottom: 30px; }
    .content-header h1 { font-size: 32px !important; font-weight: 700 !important; margin-bottom: 10px !important; }
    .small-box { border-radius: 10px !important; box-shadow: 0 4px 15px rgba(0,0,0,0.1) !important; border: none !important; transition: all 0.3s ease; }
    .small-box:hover { transform: translateY(-5px); box-shadow: 0 8px 25px rgba(0,0,0,0.15) !important; }
    .small-box .inner { padding: 20px !important; }
    .small-box h3 { font-size: 28px !important; font-weight: 700 !important; }
    .card { border: none; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); transition: all 0.3s ease; margin-bottom: 20px; }
    .card:hover { transform: translateY(-5px); box-shadow: 0 8px 25px rgba(0,0,0,0.15); }
    .card-header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border: none; padding: 20px !important; border-radius: 12px 12px 0 0 !important; }
    .card-body { padding: 25px !important; }
    .btn-primary { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border: none; border-radius: 8px; transition: all 0.3s ease; }
    .btn-primary:hover { transform: translateY(-2px); box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4); }
    .table { background: white; }
    .table thead th { background: linear-gradient(135deg, #f0f4ff 0%, #f8f5ff 100%); color: #667eea; font-weight: 700; border: none; padding: 16px !important; font-size: 14px; text-transform: uppercase; letter-spacing: 0.5px; }
    .table tbody td { padding: 16px !important; vertical-align: middle; font-size: 14px; color: #333; border: none; border-bottom: 1px solid #f0f0f0; }
    .table tbody tr:hover { background-color: #f8f9fc; }
    .table tbody tr:last-child td { border-bottom: none; }
    .main-footer { background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%); color: white; padding: 20px !important; font-weight: 500; box-shadow: 0 -2px 10px rgba(0,0,0,0.1); }
    .main-footer strong { color: #667eea; font-weight: 700; }
    .filter-pills a { border-radius: 20px; padding: 6px 16px; font-size: 13px; font-weight: 600; margin-right: 8px; display: inline-block; margin-bottom: 8px; text-decoration: none; }
    .filter-pills a.active { background: #667eea; color: white; }
    .filter-pills a:not(.active) { background: #f0f4ff; color: #667eea; }
    .badge-late { background: #fee2e2; color: #dc2626; }
    .badge-active { background: #dbeafe; color: #1d4ed8; }
    .badge-done { background: #dcfce7; color: #15803d; }
    html, body { height: 100%; }
    .wrapper { min-height: 100vh; display: flex; flex-direction: column; }
    .main-sidebar { height: 100vh; position: fixed; }
    .content-wrapper { margin-left: 250px; min-height: 100vh; flex: 1; }
  </style>
</head>
<body class="hold-transition sidebar-mini layout-fixed">
<div class="wrapper">

  <nav class="main-header navbar navbar-expand navbar-white navbar-light">
    <ul class="navbar-nav">
      <li class="nav-item"><a class="nav-link" data-widget="pushmenu" href="#"><i class="fas fa-bars"></i></a></li>
      <li class="nav-item d-none d-sm-inline-block"><a href="perpustakaan.php" class="nav-link">Beranda</a></li>
    </ul>
    <ul class="navbar-nav ml-auto">
      <li class="nav-item"><a href="logout.php" class="nav-link text-danger">Logout</a></li>
    </ul>
  </nav>

  <aside class="main-sidebar sidebar-dark-primary elevation-4">
    <a href="perpustakaan.php" class="brand-link text-center">
      <span class="brand-text font-weight-light">📚 LitSpace</span>
    </a>
    <div class="sidebar">
      <div class="text-center mb-2">
        <span class="badge badge-danger">👑 Panel Admin</span>
      </div>
      <nav>
        <ul class="nav nav-pills nav-sidebar flex-column">
          <li class="nav-item"><a href="perpustakaan.php" class="nav-link"><i class="nav-icon fas fa-home"></i><p>Beranda</p></a></li>
          <li class="nav-item"><a href="daftar_buku.php" class="nav-link"><i class="nav-icon fas fa-book"></i><p>Daftar Buku</p></a></li>
          <li class="nav-item"><a href="tambah_buku.php" class="nav-link"><i class="nav-icon fas fa-plus"></i><p>Tambah Buku</p></a></li>
          <li class="nav-item"><a href="daftar_peminjaman.php" class="nav-link active"><i class="nav-icon fas fa-clipboard-list"></i><p>Buku Dipinjam</p></a></li>
          <li class="nav-item"><a href="pengaturan.php" class="nav-link"><i class="nav-icon fas fa-users-cog"></i><p>Kelola User</p></a></li>
          <li class="nav-item"><a href="logout.php" class="nav-link text-danger"><i class="nav-icon fas fa-sign-out-alt"></i><p>Logout</p></a></li>
        </ul>
      </nav>
    </div>
  </aside>

  <div class="content-wrapper">
    <div class="content-header">
      <div class="container-fluid">
        <h1>📋 Daftar Buku Dipinjam</h1>
        <p style="opacity:0.9;">Pantau siapa saja yang sedang meminjam buku beserta tanggal jatuh temponya</p>
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

        <div class="row">
          <div class="col-lg-4">
            <div class="small-box bg-info">
              <div class="inner"><h3><?= $totalSedangDipinjam ?></h3><p>Sedang Dipinjam</p></div>
              <div class="icon"><i class="fas fa-book-reader"></i></div>
            </div>
          </div>
          <div class="col-lg-4">
            <div class="small-box bg-danger">
              <div class="inner"><h3><?= $totalTelat ?></h3><p>Terlambat Dikembalikan</p></div>
              <div class="icon"><i class="fas fa-exclamation-triangle"></i></div>
            </div>
          </div>
          <div class="col-lg-4">
            <div class="small-box bg-success">
              <div class="inner"><h3><?= $totalSelesai ?></h3><p>Sudah Dikembalikan</p></div>
              <div class="icon"><i class="fas fa-check-circle"></i></div>
            </div>
          </div>
        </div>

        <div class="card">
          <div class="card-header">
            <h3 class="card-title">Riwayat Peminjaman Buku</h3>
          </div>
          <div class="card-body">
            <div class="filter-pills">
              <a href="daftar_peminjaman.php?filter=semua" class="<?= $filter === 'semua' ? 'active' : '' ?>">Semua</a>
              <a href="daftar_peminjaman.php?filter=dipinjam" class="<?= $filter === 'dipinjam' ? 'active' : '' ?>">Sedang Dipinjam</a>
              <a href="daftar_peminjaman.php?filter=terlambat" class="<?= $filter === 'terlambat' ? 'active' : '' ?>">Terlambat</a>
              <a href="daftar_peminjaman.php?filter=dikembalikan" class="<?= $filter === 'dikembalikan' ? 'active' : '' ?>">Sudah Dikembalikan</a>
            </div>
          </div>
          <div class="card-body table-responsive p-0 pt-0">
            <table class="table table-hover table-striped">
              <thead>
                <tr>
                  <th style="width: 40px;">No</th>
                  <th>Judul Buku</th>
                  <th>Peminjam</th>
                  <th>Tanggal Pinjam</th>
                  <th>Batas Kembali</th>
                  <th>Status</th>
                  <th style="width: 140px;">Aksi</th>
                </tr>
              </thead>
              <tbody>
                <?php $no = 1; foreach ($daftar as $p): ?>
                  <?php $telat = $p['status'] === 'dipinjam' && $p['tanggal_kembali'] < $today; ?>
                  <tr>
                    <td><?= $no++ ?></td>
                    <td><?= htmlspecialchars($p['judul']) ?><br><small class="text-muted"><?= htmlspecialchars($p['penulis']) ?></small></td>
                    <td><?= htmlspecialchars($p['username']) ?></td>
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
                        <a href="kembalikan_buku.php?id=<?= $p['id'] ?>" class="btn btn-primary btn-sm" onclick="return confirm('Tandai buku ini sudah dikembalikan?')">Tandai Kembali</a>
                      <?php else: ?>
                        <span class="text-muted" style="font-size:12px;">Selesai <?= $p['tanggal_dikembalikan'] ? date('d M Y', strtotime($p['tanggal_dikembalikan'])) : '' ?></span>
                      <?php endif; ?>
                    </td>
                  </tr>
                <?php endforeach; ?>
                <?php if (empty($daftar)): ?>
                  <tr><td colspan="7" class="text-center text-muted">Belum ada data peminjaman buku.</td></tr>
                <?php endif; ?>
              </tbody>
            </table>
          </div>
        </div>

      </div>
    </div>
  </div>

  <footer class="main-footer text-center">
    <strong>LitSpace &copy; 2026</strong> - Perpustakaan Digital
  </footer>
</div>

<script src="plugins/jquery/jquery.min.js"></script>
<script src="plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="dist/js/adminlte.min.js"></script>
</body>
</html>

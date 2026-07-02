<?php
session_start();
include_once 'db.php';

// Hidrasi session dari cookie "Ingat Saya" jika session kosong
if (!isset($_SESSION['username']) && isset($_COOKIE['user'])) {
    $_SESSION['username'] = $_COOKIE['user'];
    $_SESSION['user'] = $_COOKIE['user'];
    $_SESSION['role'] = $_COOKIE['role'] ?? 'user';
}

if (!isset($_SESSION['username'])) {
    header("Location: login.php?error=belum_login");
    exit(); 
}

// perpustakaan.php adalah PANEL ADMIN. User biasa diarahkan ke panel miliknya sendiri.
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: dashboard_user.php");
    exit();
}

$conn = get_db_connection();

// 1. Hitung Total Buku
$qTotal = mysqli_query($conn, "SELECT COUNT(*) as total FROM buku");
$rTotal = mysqli_fetch_assoc($qTotal);
$totalBuku = $rTotal['total'];

// 2. Hitung Buku yang Sudah Dibaca
$qDibaca = mysqli_query($conn, "SELECT COUNT(*) as total FROM buku WHERE status = 'dibaca'");
$rDibaca = mysqli_fetch_assoc($qDibaca);
$bukuDibaca = $rDibaca['total'];

// 3. Hitung Jumlah Kategori Unik
$qKategori = mysqli_query($conn, "SELECT COUNT(DISTINCT kategori) as total FROM buku");
$rKategori = mysqli_fetch_assoc($qKategori);
$totalKategori = $rKategori['total'];

$profile_photo = 'profiles/admin.jpg';
if (!file_exists($profile_photo)) {
    $profile_photo = 'dist/img/user2-160x160.jpg';
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>LitSpace | Perpustakaan Digital</title>

  <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700">
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap">
  <link rel="stylesheet" href="plugins/fontawesome-free/css/all.min.css">
  <link rel="stylesheet" href="dist/css/adminlte.min.css">
  
  <style>
    * {
      font-family: 'Poppins', sans-serif;
    }

    body {
      background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
      min-height: 100vh;
    }

    .content-wrapper {
      background: transparent;
    }

    .main-header {
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    }

    .main-header .navbar-nav .nav-link {
      color: white !important;
      font-weight: 500;
      transition: all 0.3s ease;
    }

    .main-header .navbar-nav .nav-link:hover {
      transform: translateY(-2px);
    }

    .text-danger {
      color: #ff6b6b !important;
      transition: all 0.3s ease;
    }

    .text-danger:hover {
      color: #ee5a52 !important;
    }

    .main-sidebar {
      background: linear-gradient(180deg, #2c3e50 0%, #34495e 100%);
      box-shadow: 2px 0 10px rgba(0,0,0,0.1);
    }

    .brand-link {
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      padding: 15px !important;
      font-size: 20px !important;
      font-weight: 700;
      letter-spacing: 1px;
    }

    .nav-link {
      transition: all 0.3s ease !important;
      border-left: 3px solid transparent;
      margin-left: 5px;
    }

    .nav-link:hover {
      background-color: rgba(255,255,255,0.1) !important;
      border-left-color: #667eea !important;
      transform: translateX(5px);
    }

    .nav-link.active {
      background-color: rgba(102, 126, 234, 0.2) !important;
      border-left-color: #667eea !important;
    }

    .content-header {
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      color: white;
      padding: 30px 0 !important;
      box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
      margin-bottom: 30px;
    }

    .content-header h1 {
      font-size: 32px !important;
      font-weight: 700 !important;
      margin-bottom: 10px !important;
    }

    .content-header p {
      font-size: 16px;
      font-weight: 300;
      opacity: 0.9;
    }

    .small-box {
      border-radius: 10px !important;
      box-shadow: 0 4px 15px rgba(0,0,0,0.1) !important;
      border: none !important;
      transition: all 0.3s ease;
      position: relative;
      overflow: hidden;
    }

    .small-box:hover {
      transform: translateY(-5px);
      box-shadow: 0 8px 25px rgba(0,0,0,0.15) !important;
    }

    .small-box .inner {
      padding: 20px !important;
    }

    .small-box h3 {
      font-size: 28px !important;
      font-weight: 700 !important;
    }

    .small-box p {
      font-size: 14px;
      font-weight: 500;
      opacity: 0.9;
    }

    .card {
      border: none;
      border-radius: 12px;
      box-shadow: 0 4px 15px rgba(0,0,0,0.1);
      transition: all 0.3s ease;
      margin-bottom: 20px;
    }

    .card:hover {
      transform: translateY(-5px);
      box-shadow: 0 8px 25px rgba(0,0,0,0.15);
    }

    .card-header {
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      color: white;
      border: none;
      padding: 20px !important;
      border-radius: 12px 12px 0 0 !important;
    }

    .card-header h5 {
      margin: 0 !important;
      font-weight: 700;
      font-size: 18px;
    }

    .card-body {
      padding: 25px !important;
    }

    .card-body p {
      font-size: 14px;
      line-height: 1.6;
      color: #333;
      margin-bottom: 15px;
    }

    .card-body b {
      color: #667eea;
      font-weight: 700;
      display: block;
      margin-bottom: 5px;
    }

    .btn {
      border: none;
      border-radius: 8px;
      padding: 8px 20px;
      font-weight: 600;
      transition: all 0.3s ease;
      text-transform: uppercase;
      font-size: 12px;
      letter-spacing: 0.5px;
    }

    .btn-primary {
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      color: white;
    }

    .btn-primary:hover {
      background: linear-gradient(135deg, #5568d3 0%, #6a3f91 100%);
      transform: translateY(-2px);
      box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
    }

    .btn-success {
      background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
      color: white;
    }

    .btn-success:hover {
      background: linear-gradient(135deg, #0d7a76 0%, #2dcc6f 100%);
      transform: translateY(-2px);
      box-shadow: 0 4px 12px rgba(17, 153, 142, 0.4);
    }

    .btn-warning {
      background: linear-gradient(135deg, #f5af19 0%, #f12711 100%);
      color: white;
    }

    .btn-warning:hover {
      background: linear-gradient(135deg, #d9940d 0%, #d61909 100%);
      transform: translateY(-2px);
      box-shadow: 0 4px 12px rgba(245, 175, 25, 0.4);
    }

    .main-footer {
      background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%);
      color: white;
      padding: 20px !important;
      font-weight: 500;
      box-shadow: 0 -2px 10px rgba(0,0,0,0.1);
    }

    .main-footer strong {
      color: #667eea;
      font-weight: 700;
    }
  </style>
</head>

<body class="hold-transition sidebar-mini layout-fixed">
  <div class="wrapper">

  <!-- Navbar -->
  <nav class="main-header navbar navbar-expand navbar-white navbar-light">
    <ul class="navbar-nav">
      <li class="nav-item">
        <a class="nav-link" data-widget="pushmenu" href="#"><i class="fas fa-bars"></i></a>
      </li>
      <li class="nav-item d-none d-sm-inline-block">
        <a href="perpustakaan.php" class="nav-link">Beranda</a>
      </li>
    </ul>

    <ul class="navbar-nav ml-auto">
      <li class="nav-item">
        <a href="logout.php" class="nav-link text-danger">Logout</a>
      </li>
    </ul>
  </nav>

  <!-- Sidebar -->
  <aside class="main-sidebar sidebar-dark-primary elevation-4">
    <a href="#" class="brand-link text-center">
      <span class="brand-text font-weight-light">📚 LitSpace</span>
    </a>

    <div class="sidebar">
            <div class="user-panel mt-3 pb-3 mb-3 d-flex align-items-center">
          <div class="image">
              <img src="<?= htmlspecialchars($profile_photo) ?>"
                  class="image-circle elevation-2"
                  style="width:50px; height:50px; object-fit:cover;">
          </div>
          <div class="info">
              <a href="#" class="d-block"><?= htmlspecialchars($_SESSION['username']) ?></a>
              <span class="badge badge-danger" style="font-size:10px;">👑 Panel Admin</span>
          </div>
      </div>

      <nav>
        <ul class="nav nav-pills nav-sidebar flex-column">
          <li class="nav-item">
            <a href="perpustakaan.php" class="nav-link active">
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
            <a href="tambah_buku.php" class="nav-link">
              <i class="nav-icon fas fa-plus"></i>
              <p>Tambah Buku</p>
            </a>
          </li>
          <li class="nav-item">
            <a href="daftar_peminjaman.php" class="nav-link">
              <i class="nav-icon fas fa-clipboard-list"></i>
              <p>Buku Dipinjam</p>
            </a>
          </li>
          <li class="nav-item">
            <a href="pengaturan.php" class="nav-link">
              <i class="nav-icon fas fa-users-cog"></i>
              <p>Kelola User</p>
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

    <!-- Header -->
    <div class="content-header">
      <div class="container-fluid">
        <h1 class="m-0">Selamat datang di LitSpace 📚</h1>
        <p>Platform perpustakaan digital untuk membaca dan belajar</p>
      </div>
    </div>

    <!-- Statistik -->
      <div class="row">
        <div class="col-lg-4">
          <div class="small-box bg-info">
            <div class="inner">
              <h3><?= $totalBuku ?></h3>
              <p>Total Buku</p>
            </div>
            <div class="icon"><i class="fas fa-book"></i></div>
          </div>
        </div>

        <div class="col-lg-4">
          <div class="small-box bg-success">
            <div class="inner">
              <h3><?= $bukuDibaca ?></h3>
              <p>Buku Dibaca</p>
            </div>
            <div class="icon"><i class="fas fa-book-reader"></i></div>
          </div>
        </div>

        <div class="col-lg-4">
          <div class="small-box bg-warning">
            <div class="inner">
              <h3><?= $totalKategori ?></h3>
              <p>Kategori</p>
            </div>
            <div class="icon"><i class="fas fa-list"></i></div>
          </div>
        </div>
      </div>

        <!-- Daftar Buku -->
        <div class="col-lg-12">
  <h4 style="color: #333; font-weight: 700; margin-bottom: 20px;">📚 Buku Terbaru</h4>
  
  <?php 
  // Kita ambil 3 data buku paling baru berdasarkan id terbesar
  $qBukuTerbaru = mysqli_query($conn, "SELECT * FROM buku ORDER BY id DESC LIMIT 3");

  if (mysqli_num_rows($qBukuTerbaru) > 0) {
      while ($buku = mysqli_fetch_assoc($qBukuTerbaru)) { 
        $coverSrc = (!empty($buku['cover_image']) && file_exists('profiles/books/' . $buku['cover_image']))
            ? 'profiles/books/' . htmlspecialchars($buku['cover_image'])
            : null;
  ?>
        <div class="card">
          <div class="card-body">
            <div style="display: flex; gap: 15px;">
              <?php if ($coverSrc): ?>
                <img src="<?= $coverSrc ?>" alt="cover" style="width:50px;height:70px;object-fit:cover;border-radius:6px;min-width:50px;">
              <?php else: ?>
                <div style="font-size: 40px; min-width: 50px;">📖</div>
              <?php endif; ?>
              <div style="flex: 1;">
                <h5 style="color: #667eea; margin: 0 0 5px 0; font-weight: 700;">
                  <?= htmlspecialchars($buku['judul']) ?>
                </h5>
                <p style="color: #666; margin: 0 0 15px 0; font-size: 13px;">
                  Penulis: <?= htmlspecialchars($buku['penulis']) ?>
                </p>
                <?php if (!empty($buku['file_path'])): ?>
                <a href="<?= htmlspecialchars($buku['file_path']) ?>" class="btn btn-primary btn-sm">                  <i class="fas fa-book-reader"></i> Baca Sekarang
                </a>
                <?php endif; ?>
              </div>
            </div>
          </div>
        </div>
  <?php 
      } 
  } else { 
  ?>
      <div class="alert alert-info">Belum ada buku terbaru di database.</div>
  <?php } ?>
</div>

          <!-- Rekomendasi & Akses Cepat -->
          <div class="col-lg-12">
            <h4 style="color: #333; font-weight: 700; margin-bottom: 20px;">⭐ Rekomendasi</h4>
            
            <div class="card">
              <div class="card-body" style="text-align: center;">
                <div style="font-size: 50px; margin-bottom: 15px;">📖</div>
                <h5 style="color: #333; font-weight: 700; margin-bottom: 10px;">Perluas Wawasan</h5>
                <p style="color: #666; margin-bottom: 15px; font-size: 13px;">Baca buku-buku pilihan untuk meningkatkan literasi kamu</p>
                <a href="daftar_buku.php" class="btn btn-warning btn-block">
                  <i class="fas fa-arrow-right"></i> Lihat Semua Buku
                </a>
              </div>
            </div>

            <div class="card">
              <div class="card-body" style="text-align: center;">
                <div style="font-size: 50px; margin-bottom: 15px;">➕</div>
                <h5 style="color: #333; font-weight: 700; margin-bottom: 10px;">Tambah Koleksi</h5>
                <p style="color: #666; margin-bottom: 15px; font-size: 13px;">Kontribusi buku baru ke perpustakaan digital</p>
                <a href="tambah_buku.php" class="btn btn-primary btn-block">
                  <i class="fas fa-plus"></i> Tambah Buku
                </a>
              </div>
            </div>
          </div>

        </div>

      </div>
    </div>

  </div>

  <!-- Footer -->
  <footer class="main-footer text-center">
    <strong>LitSpace &copy; 2026</strong> - Perpustakaan Digital
  </footer>

</div>

<script src="plugins/jquery/jquery.min.js"></script>
<script src="plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="dist/js/adminlte.min.js"></script>
</body>
</html>
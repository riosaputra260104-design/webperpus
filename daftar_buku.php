<?php
session_start();
include_once 'db.php';

// 1. CEK COOKIE TERLEBIH DAHULU: Jika session kosong tapi cookie ada, login otomatis
if (!isset($_SESSION['username']) && isset($_COOKIE['user'])) {
    $_SESSION['username'] = $_COOKIE['user'];
    $_SESSION['role'] = $_COOKIE['role'] ?? 'user';
}

// 2. PROTEKSI HALAMAN: Jika setelah dicek session tetap kosong, baru lempar ke login
if (!isset($_SESSION['username'])) {
    header("Location: login.php?error=belum_login");
    exit(); 
}
$isAdmin = isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
$conn = get_db_connection();
$result = mysqli_query($conn, 'SELECT * FROM buku ORDER BY created_at DESC');
$books = mysqli_fetch_all($result, MYSQLI_ASSOC);

// Cek buku apa saja yang sedang dipinjam oleh user yang sedang login (biar tombol Pinjam bisa disable)
$sedangDipinjamOleh = [];
if (!$isAdmin) {
    $stmtPinjam = $conn->prepare("SELECT buku_id FROM peminjaman WHERE username = ? AND status = 'dipinjam'");
    $stmtPinjam->bind_param('s', $_SESSION['username']);
    $stmtPinjam->execute();
    $resPinjam = $stmtPinjam->get_result();
    while ($row = $resPinjam->fetch_assoc()) {
        $sedangDipinjamOleh[$row['buku_id']] = true;
    }
    $stmtPinjam->close();
}

$error = $_GET['error'] ?? '';
$success = $_GET['success'] ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Daftar Buku | LitSpace</title>

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

    .main-header .nav-link, .main-header .navbar-nav span {
      color: white !important;
      font-weight: 500;
      transition: all 0.3s ease;
    }

    .main-header .nav-link:hover {
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

    .card {
      border: none;
      border-radius: 12px;
      box-shadow: 0 4px 15px rgba(0,0,0,0.1);
      transition: all 0.3s ease;
      margin-bottom: 20px;
    }

    .card:hover {
      transform: translateY(-5px);
      box-shadow: 0 8px 25px rgba(136, 104, 104, 0.15);
    }

    .card-header {
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      color: white;
      border: none;
      padding: 20px !important;
      border-radius: 12px 12px 0 0 !important;
    }

    .card-body {
      padding: 25px !important;
    }

    .btn-primary {
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      color: white;
      border: none;
      border-radius: 8px;
      transition: all 0.3s ease;
    }

    .btn-primary:hover {
      background: linear-gradient(135deg, #5568d3 0%, #6a3f91 100%);
      transform: translateY(-2px);
      box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
    }

    .table {
      background: white;
    }

    .table thead th {
      background: linear-gradient(135deg, #f0f4ff 0%, #f8f5ff 100%);
      color: #667eea;
      font-weight: 700;
      border: none;
      padding: 16px !important;
      font-size: 14px;
      text-transform: uppercase;
      letter-spacing: 0.5px;
    }

    .table tbody td {
      padding: 16px !important;
      vertical-align: middle;
      font-size: 14px;
      color: #333;
      border: none;
      border-bottom: 1px solid #f0f0f0;
    }

    .table tbody tr:hover {
      background-color: #f8f9fc;
    }

    .table tbody tr:last-child td {
      border-bottom: none;
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
    html, body {
  height: 100%;
}

.wrapper {
  min-height: 100vh;
}

.main-sidebar {
  height: 100vh;
  position: fixed;
}

.content-wrapper {
  margin-left: 250px;
  min-height: 100vh;
}
.wrapper {
  min-height: 100vh;
  display: flex;
  flex-direction: column;
}

.content-wrapper {
  flex: 1;
}
  </style>
</head>

<body class="hold-transition sidebar-mini">
<div class="wrapper">

<!-- Sidebar -->
<aside class="main-sidebar sidebar-dark-primary elevation-4">
  <a href="<?= $isAdmin ? 'perpustakaan.php' : 'dashboard_user.php' ?>" class="brand-link text-center">
    <span class="brand-text font-weight-light">📚 LitSpace</span>
  </a>

  <div class="sidebar">
    <div class="text-center mb-2">
      <span class="badge <?= $isAdmin ? 'badge-danger' : 'badge-info' ?>"><?= $isAdmin ? '👑 Panel Admin' : '🙂 Panel User' ?></span>
    </div>
    <nav>
      <ul class="nav nav-pills nav-sidebar flex-column">

        <li class="nav-item">
          <a href="<?= $isAdmin ? 'perpustakaan.php' : 'dashboard_user.php' ?>" class="nav-link">
            <i class="nav-icon fas fa-home"></i>
            <p>Beranda</p>
          </a>
        </li>

        <li class="nav-item">
          <a href="daftar_buku.php" class="nav-link active">
            <i class="nav-icon fas fa-book"></i>
            <p>Daftar Buku</p>
          </a>
        </li>

        <?php if ($isAdmin): ?>
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
        <?php else: ?>
        <li class="nav-item">
          <a href="buku_saya.php" class="nav-link">
            <i class="nav-icon fas fa-book-reader"></i>
            <p>Buku Saya</p>
          </a>
        </li>
        <?php endif; ?>
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
      <h1>📖 Daftar Buku</h1>
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

      <!-- Tabel Buku -->
      <div class="card">
        <div class="card-header">
          <h3 class="card-title">List Buku LitSpace</h3>
        </div>

        <div class="card-body table-responsive p-0">
          <table class="table table-hover table-striped">
            <thead>
              <tr>
                <th style="width: 60px;">No</th>
                <th style="width: 70px;">Cover</th>
                <th>Judul</th>
                <th>Penulis</th>
                <th>Kategori</th>
                <th>Status</th>
                <th style="width: 70px;">Stok</th>
                <th>File</th>
                <th style="width: 180px;">Aksi</th>
              </tr>
            </thead>

            <tbody>
            <?php
            $no = 1;
            foreach ($books as $book) {
                $fileLink = $book['file_path'] ? '<a href="' . htmlspecialchars($book['file_path']) . '" class="btn btn-success btn-sm" >Lihat</a>' : '-';

                $coverPath = 'profiles/books/' . ($book['cover_image'] ?? '');
                if (!empty($book['cover_image']) && file_exists($coverPath)) {
                    $coverHtml = '<img src="' . htmlspecialchars($coverPath) . '" style="width:45px;height:60px;object-fit:cover;border-radius:6px;">';
                } else {
                    $coverHtml = '<div style="width:45px;height:60px;display:flex;align-items:center;justify-content:center;background:#f0f4ff;border-radius:6px;font-size:20px;">📕</div>';
                }

                echo '<tr>';
                echo '<td>' . $no . '</td>';
                echo '<td>' . $coverHtml . '</td>';
                echo '<td>' . htmlspecialchars($book['judul']) . '</td>';
                echo '<td>' . htmlspecialchars($book['penulis']) . '</td>';
                echo '<td>' . htmlspecialchars($book['kategori']) . '</td>';
                echo '<td>' . htmlspecialchars($book['status']) . '</td>';
                echo '<td>' . (int) $book['stok'] . '</td>';
                echo '<td>' . $fileLink . '</td>';
                echo '<td>';
                if ($isAdmin) {
                    echo '<a href="edit_buku.php?id=' . $book['id'] . '" class="btn btn-primary btn-sm mr-1">Edit</a>';
                    echo '<a href="hapus_buku.php?id=' . $book['id'] . '" class="btn btn-danger btn-sm" onclick="return confirm(\'Hapus buku ini?\')">Hapus</a>';
                } else {
                    if (isset($sedangDipinjamOleh[$book['id']])) {
                        echo '<span class="badge" style="background:#dbeafe;color:#1d4ed8;padding:6px 10px;">Sedang kamu pinjam</span>';
                    } elseif ((int) $book['stok'] <= 0) {
                        echo '<button class="btn btn-secondary btn-sm" disabled>Stok Habis</button>';
                    } else {
                        echo '<form method="POST" action="pinjam_buku.php" style="display:inline;">';
                        echo '<input type="hidden" name="buku_id" value="' . $book['id'] . '">';
                        echo '<button type="submit" class="btn btn-success btn-sm" onclick="return confirm(\'Pinjam buku ini selama 7 hari?\')"><i class="fas fa-hand-holding"></i> Pinjam</button>';
                        echo '</form>';
                    }
                }
                echo '</td>';
                echo '</tr>';
                $no++;
            }
            if (empty($books)) {
                echo '<tr><td colspan="9" class="text-center text-muted">Belum ada buku.</td></tr>';
            }
            ?>
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
<?php
session_start();

if (!isset($_SESSION['username']) && isset($_COOKIE['user'])) {
    $_SESSION['username'] = $_COOKIE['user'];
    $_SESSION['user'] = $_COOKIE['user'];
    if (isset($_COOKIE['role'])) {
        $_SESSION['role'] = $_COOKIE['role'];
    }
}
if (!isset($_SESSION['user']) && isset($_SESSION['username'])) {
    $_SESSION['user'] = $_SESSION['username'];
}

// Proteksi login
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

// dashboard_user.php adalah PANEL USER. Admin diarahkan ke panel miliknya sendiri.
if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin') {
    header("Location: perpustakaan.php");
    exit();
}

require_once 'db.php';
$conn = get_db_connection();

// Ambil info user dari database
$stmt = $conn->prepare("SELECT full_name, created_at FROM users WHERE username = ?");
$stmt->bind_param("s", $_SESSION['username']);
$stmt->execute();
$stmt->bind_result($full_name, $created_at);
$stmt->fetch();
$stmt->close();

$created_date = date('d M Y', strtotime($created_at ?? 'now'));
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Dashboard | LitSpace</title>
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
      margin-left: 0;
      padding: 30px;
    }

    .main-header {
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      box-shadow: 0 2px 10px rgba(0,0,0,0.1);
      margin-left: 0;
      padding: 15px 20px;
    }

    .main-header .navbar-nav {
      margin-left: auto;
    }

    .main-header .nav-link {
      color: white !important;
      font-weight: 500;
      transition: all 0.3s ease;
    }

    .main-header .nav-link:hover {
      transform: translateY(-2px);
    }

    .navbar-brand {
      font-size: 20px;
      font-weight: 700;
      color: white !important;
      letter-spacing: 1px;
    }

    .card {
      border: none;
      border-radius: 16px;
      box-shadow: 0 4px 15px rgba(0,0,0,0.1);
      margin-bottom: 24px;
    }

    .card-header {
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      color: white;
      border: none;
      padding: 20px !important;
      border-radius: 16px 16px 0 0 !important;
    }

    .card-header h5 {
      margin: 0;
      font-size: 18px;
      font-weight: 600;
    }

    .card-body {
      padding: 30px !important;
    }

    .profile-section {
      display: flex;
      gap: 30px;
      align-items: center;
    }

    .profile-avatar {
      width: 120px;
      height: 120px;
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      border-radius: 16px;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 50px;
      box-shadow: 0 8px 20px rgba(102, 126, 234, 0.3);
    }

    .profile-info h3 {
      margin: 0 0 10px;
      font-size: 24px;
      color: #1f2937;
    }

    .profile-info p {
      margin: 6px 0;
      color: #6b7280;
      font-size: 15px;
    }

    .profile-info strong {
      color: #4f46e5;
    }

    .action-buttons {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 16px;
      margin-top: 30px;
    }

    .btn-action {
      padding: 16px 20px;
      border: none;
      border-radius: 12px;
      font-size: 16px;
      font-weight: 600;
      cursor: pointer;
      transition: all 0.3s ease;
      text-decoration: none;
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 10px;
      text-align: center;
    }

    .btn-primary-custom {
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      color: white;
      box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
    }

    .btn-primary-custom:hover {
      transform: translateY(-2px);
      box-shadow: 0 6px 20px rgba(102, 126, 234, 0.5);
    }

    .btn-secondary-custom {
      background: #f3f4f6;
      color: #1f2937;
      border: 2px solid #e5e7eb;
    }

    .btn-secondary-custom:hover {
      background: #e5e7eb;
      transform: translateY(-2px);
    }

    .btn-logout {
      background: #fee2e2;
      color: #dc2626;
      border: 2px solid #fecaca;
    }

    .btn-logout:hover {
      background: #fecaca;
      transform: translateY(-2px);
    }

    .features {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
      gap: 16px;
      margin-top: 30px;
    }

    .feature-box {
      background: linear-gradient(135deg, rgba(102, 126, 234, 0.1) 0%, rgba(118, 75, 162, 0.1) 100%);
      padding: 20px;
      border-radius: 12px;
      text-align: center;
      border: 1px solid rgba(102, 126, 234, 0.2);
    }

    .feature-box i {
      font-size: 28px;
      color: #667eea;
      margin-bottom: 10px;
    }

    .feature-box h6 {
      margin: 10px 0;
      color: #1f2937;
      font-weight: 600;
    }

    .feature-box p {
      margin: 0;
      font-size: 13px;
      color: #6b7280;
    }

    .main-footer {
      background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%);
      color: white;
      text-align: center;
      padding: 20px !important;
      margin-left: 0;
      margin-top: 40px;
    }
  </style>
</head>
<body class="hold-transition layout-top-nav">
  <div class="wrapper">
    <!-- Navbar -->
    <nav class="main-header navbar navbar-expand-md navbar-light">
      <div class="container">
        <span class="navbar-brand">📚 LitSpace <small style="font-size:12px;opacity:0.85;">| Panel User</small></span>
        <div class="navbar-nav ml-auto">
          <a class="nav-link" href="logout.php">
            <i class="fas fa-sign-out-alt"></i> Logout
          </a>
        </div>
      </div>
    </nav>

    <!-- Content -->
    <div class="content-wrapper">
      <!-- Profile Card -->
      <div class="card">
        <div class="card-header">
          <h5><i class="fas fa-user-circle"></i> Profil Saya</h5>
        </div>
        <div class="card-body">
          <div class="profile-section">
            <div class="profile-avatar">
              👤
            </div>
            <div class="profile-info">
              <h3><?php echo htmlspecialchars($full_name ?: $_SESSION['username']); ?></h3>
              <p><strong>Username:</strong> <?php echo htmlspecialchars($_SESSION['username']); ?></p>
              <p><strong>Role:</strong> <span class="badge" style="background:#dbeafe;color:#1d4ed8;">🙂 User Biasa</span></p>
              <p><strong>Bergabung:</strong> <?php echo $created_date; ?></p>
            </div>
          </div>

          <div class="action-buttons">
            <a href="daftar_buku.php" class="btn-action btn-primary-custom">
              <i class="fas fa-book"></i> Lihat Daftar Buku
            </a>
            <a href="buku_saya.php" class="btn-action btn-primary-custom">
              <i class="fas fa-book-reader"></i> Buku Saya
            </a>
            <a href="profil_user.php" class="btn-action btn-secondary-custom">
              <i class="fas fa-edit"></i> Edit Profil
            </a>
            <a href="logout.php" class="btn-action btn-logout">
              <i class="fas fa-sign-out-alt"></i> Logout
            </a>
          </div>
        </div>
      </div>

      <!-- Features -->
      <div class="card">
        <div class="card-header">
          <h5><i class="fas fa-star"></i> Fitur yang Tersedia</h5>
        </div>
        <div class="card-body">
          <div class="features">
            <div class="feature-box">
              <i class="fas fa-hand-holding"></i>
              <h6>Pinjam Buku</h6>
              <p>Pinjam buku langsung dari Daftar Buku</p>
            </div>
            <div class="feature-box">
              <i class="fas fa-calendar-check"></i>
              <h6>Buku Saya</h6>
              <p>Pantau tanggal pinjam & batas pengembalian</p>
            </div>
            <div class="feature-box">
              <i class="fas fa-user-edit"></i>
              <h6>Edit Profil</h6>
              <p>Ubah data pribadi dan foto profil</p>
            </div>
            <div class="feature-box">
              <i class="fas fa-shield-alt"></i>
              <h6>Aman</h6>
              <p>Password terenkripsi dengan aman</p>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Footer -->
    <footer class="main-footer">
      <strong>LitSpace © 2026</strong> - Platform Perpustakaan Digital
    </footer>
  </div>

  <script src="plugins/jquery/jquery.min.js"></script>
  <script src="plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
  <script src="dist/js/adminlte.min.js"></script>
</body>
</html>

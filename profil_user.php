<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['user']) && isset($_COOKIE['user'])) {
    $_SESSION['user'] = $_COOKIE['user'];
    if (isset($_COOKIE['role'])) {
        $_SESSION['role'] = $_COOKIE['role'];
    }
}

// Proteksi login
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

// User hanya bisa akses halaman ini
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'user') {
    header("Location: perpustakaan.php");
    exit();
}

$conn = get_db_connection();
$error = '';
$success = '';

// Ambil data user saat ini
$stmt = $conn->prepare("SELECT full_name FROM users WHERE username = ?");
$stmt->bind_param("s", $_SESSION['user']);
$stmt->execute();
$stmt->bind_result($full_name);
$stmt->fetch();
$stmt->close();

// Proses update profil
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $new_full_name = trim($_POST['full_name'] ?? '');
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $current_password = $_POST['current_password'] ?? '';

    if (empty($new_full_name)) {
        $error = 'Nama lengkap tidak boleh kosong.';
    } else {
        // Update nama
        $updateStmt = $conn->prepare("UPDATE users SET full_name = ? WHERE username = ?");
        $updateStmt->bind_param("ss", $new_full_name, $_SESSION['user']);
        $updateStmt->execute();

        // Jika ada perubahan password
        if (!empty($new_password)) {
            if (strlen($new_password) < 6) {
                $error = 'Password baru minimal 6 karakter.';
            } elseif ($new_password !== $confirm_password) {
                $error = 'Password baru tidak cocok.';
            } else {
                // Verifikasi password lama
                $checkStmt = $conn->prepare("SELECT password FROM users WHERE username = ?");
                $checkStmt->bind_param("s", $_SESSION['user']);
                $checkStmt->execute();
                $checkStmt->bind_result($db_password);
                $checkStmt->fetch();
                $checkStmt->close();

                if (!password_verify($current_password, $db_password)) {
                    $error = 'Password saat ini tidak sesuai.';
                } else {
                    $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                    $pwdStmt = $conn->prepare("UPDATE users SET password = ? WHERE username = ?");
                    $pwdStmt->bind_param("ss", $hashed_password, $_SESSION['user']);
                    $pwdStmt->execute();
                    $pwdStmt->close();
                }
            }
        }

        if (empty($error)) {
            $success = 'Profil berhasil diperbarui!';
            $full_name = $new_full_name;
        }

        $updateStmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Edit Profil | LitSpace</title>
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
    }

    .main-header .nav-link {
      color: white !important;
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
    }

    .card-header {
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      color: white;
      border: none;
      padding: 20px !important;
      border-radius: 16px 16px 0 0 !important;
    }

    .card-body {
      padding: 30px !important;
    }

    .form-group label {
      font-weight: 600;
      color: #334155;
    }

    .form-control {
      border-radius: 8px;
      border: 1px solid #d1d5db;
    }

    .form-control:focus {
      border-color: #667eea;
      box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
    }

    .btn {
      padding: 10px 20px;
      border-radius: 8px;
      font-weight: 600;
    }

    .btn-primary {
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      border: none;
    }

    .btn-secondary {
      background: #e5e7eb;
      color: #1f2937;
      border: none;
    }

    .btn-secondary:hover {
      background: #d1d5db;
    }

    .error {
      color: #dc2626;
      padding: 12px;
      background: #fee2e2;
      border-radius: 8px;
      margin-bottom: 20px;
    }

    .success {
      color: #16a34a;
      padding: 12px;
      background: #dcfce7;
      border-radius: 8px;
      margin-bottom: 20px;
    }

    .divider {
      margin: 30px 0;
      border-top: 2px solid #e5e7eb;
    }

    .main-footer {
      background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%);
      color: white;
      text-align: center;
      padding: 20px !important;
      margin-left: 0;
    }
  </style>
</head>
<body class="hold-transition layout-top-nav">
  <div class="wrapper">
    <!-- Navbar -->
    <nav class="main-header navbar navbar-expand-md navbar-light">
      <div class="container">
        <a href="dashboard_user.php" class="navbar-brand">📚 LitSpace</a>
        <div class="navbar-nav ml-auto">
          <a class="nav-link" href="logout.php">
            <i class="fas fa-sign-out-alt"></i> Logout
          </a>
        </div>
      </div>
    </nav>

    <!-- Content -->
    <div class="content-wrapper">
      <div class="card">
        <div class="card-header">
          <h5><i class="fas fa-user-edit"></i> Edit Profil</h5>
        </div>
        <div class="card-body">
          <?php if ($error): ?>
            <div class="error"><?php echo htmlspecialchars($error); ?></div>
          <?php endif; ?>

          <?php if ($success): ?>
            <div class="success"><?php echo htmlspecialchars($success); ?></div>
          <?php endif; ?>

          <form method="POST">
            <!-- Section 1: Info Pribadi -->
            <h6 class="mb-3">Informasi Pribadi</h6>
            
            <div class="form-group">
              <label>Username</label>
              <input type="text" class="form-control" value="<?php echo htmlspecialchars($_SESSION['user']); ?>" disabled>
              <small class="text-muted">Username tidak dapat diubah</small>
            </div>

            <div class="form-group">
              <label>Nama Lengkap</label>
              <input type="text" name="full_name" class="form-control" required value="<?php echo htmlspecialchars($full_name); ?>">
            </div>

            <!-- Section 2: Ubah Password -->
            <div class="divider"></div>
            <h6 class="mb-3">Ubah Password (Opsional)</h6>

            <div class="form-group">
              <label>Password Saat Ini</label>
              <input type="password" name="current_password" class="form-control" placeholder="Kosongkan jika tidak ingin mengubah password">
            </div>

            <div class="form-group">
              <label>Password Baru</label>
              <input type="password" name="new_password" class="form-control" placeholder="Minimal 6 karakter">
            </div>

            <div class="form-group">
              <label>Konfirmasi Password Baru</label>
              <input type="password" name="confirm_password" class="form-control" placeholder="Ulangi password baru">
            </div>

            <!-- Buttons -->
            <div class="mt-30">
              <button type="submit" class="btn btn-primary">
                <i class="fas fa-save"></i> Simpan Perubahan
              </button>
              <a href="dashboard_user.php" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Kembali
              </a>
            </div>
          </form>
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

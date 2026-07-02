<?php
// PASTIKAN TIDAK ADA SPASI ATAU ENTER SEBELUM TAG PENGERJAAN PHP INI
session_start();
include_once 'db.php';

// 1. PINDAHKAN PENGECEKAN COOKIE KE ATAS: Jika session kosong tapi cookie ada, login-kan otomatis
if (!isset($_SESSION['username']) && isset($_COOKIE['user'])) {
    $_SESSION['username'] = $_COOKIE['user'];
    $_SESSION['role'] = $_COOKIE['role'] ?? 'user';
}

// 2. BARU PROTEKSI HALAMAN: Jika session login benar-benar kosong, alihkan ke login
if (!isset($_SESSION['username'])) {
    header("Location: login.php?error=belum_login");
    exit(); 
}
$isAdmin = isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
// Hanya admin yang boleh menambahkan buku baru
if (!$isAdmin) {
    header('Location: daftar_buku.php');
    exit();
}
$conn = get_db_connection();
$success = false;
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $judul = sanitize($_POST['judul'] ?? '');
    $penulis = sanitize($_POST['penulis'] ?? '');
    $kategori = sanitize($_POST['kategori'] ?? '');
    $status = in_array($_POST['status'] ?? '', ['dibaca', 'belum']) ? $_POST['status'] : 'belum';
    $filePath = null;
    $coverImage = null;

    // Upload file buku (PDF)
    if (!empty($_FILES['file']['name'])) {
        $allowed = ['pdf'];
        $ext = strtolower(pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION));
        if (in_array($ext, $allowed)) {
            $uploadDir = __DIR__ . DIRECTORY_SEPARATOR . 'Buku';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            $filename = time() . '_' . basename($_FILES['file']['name']);
            $targetFile = $uploadDir . DIRECTORY_SEPARATOR . $filename;
            if (move_uploaded_file($_FILES['file']['tmp_name'], $targetFile)) {
                $filePath = 'Buku/' . $filename;
            } else {
                $error = 'Gagal mengunggah file PDF.';
            }
        } else {
            $error = 'Format file buku hanya PDF.';
        }
    }

    // Upload foto cover buku
    if (empty($error) && !empty($_FILES['cover_image']['name'])) {
        $allowedImg = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        $imgExt = strtolower(pathinfo($_FILES['cover_image']['name'], PATHINFO_EXTENSION));
        if (in_array($imgExt, $allowedImg)) {
            if ($_FILES['cover_image']['size'] > 5 * 1024 * 1024) {
                $error = 'Ukuran foto cover maksimal 5MB.';
            } else {
                $coverDir = __DIR__ . DIRECTORY_SEPARATOR . 'profiles' . DIRECTORY_SEPARATOR . 'books';
                if (!is_dir($coverDir)) {
                    mkdir($coverDir, 0755, true);
                }
                $coverFilename = 'book_' . time() . '_' . mt_rand(100, 999) . '.' . $imgExt;
                $coverTarget = $coverDir . DIRECTORY_SEPARATOR . $coverFilename;
                if (move_uploaded_file($_FILES['cover_image']['tmp_name'], $coverTarget)) {
                    $coverImage = $coverFilename;
                } else {
                    $error = 'Gagal mengunggah foto cover.';
                }
            }
        } else {
            $error = 'Format foto cover hanya JPG, PNG, GIF, atau WEBP.';
        }
    }

    if (empty($error) && $judul && $penulis) {
        $stmt = mysqli_prepare($conn, 'INSERT INTO buku (judul, penulis, kategori, status, file_path, cover_image, ditambahkan_oleh) VALUES (?, ?, ?, ?, ?, ?, ?)');
        mysqli_stmt_bind_param($stmt, 'sssssss', $judul, $penulis, $kategori, $status, $filePath, $coverImage, $_SESSION['username']);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        $success = true;
    } elseif (empty($error)) {
        $error = 'Judul dan Penulis wajib diisi.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Tambah Buku | LitSpace</title>

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
      box-shadow: 0 8px 25px rgba(0,0,0,0.15);
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

    .form-group label {
      font-weight: 600;
      color: #334155;
    }

    .form-control {
      border-radius: 12px;
      border: 1px solid #d1d5db;
      box-shadow: none;
    }

    .btn-primary {
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      color: white;
      border: none;
      border-radius: 10px;
      transition: all 0.3s ease;
      padding: 12px 25px;
      font-weight: 600;
    }

    .btn-primary:hover {
      background: linear-gradient(135deg, #5568d3 0%, #6a3f91 100%);
      transform: translateY(-2px);
      box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
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
          <a href="daftar_buku.php" class="nav-link">
            <i class="nav-icon fas fa-book"></i>
            <p>Daftar Buku</p>
          </a>
        </li>

        <li class="nav-item">
          <a href="tambah_buku.php" class="nav-link active">
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
  <div class="content-header">
    <div class="container-fluid">
      <h1> Tambah Buku</h1>
    </div>
  </div>

  <div class="content">
    <div class="container-fluid">

      <div class="card">
        <div class="card-header">
          <h3 class="card-title">Tambah Buku Baru</h3>
        </div>
        <div class="card-body">
          <?php if (!empty($error)): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
          <?php elseif ($success): ?>
            <div class="alert alert-success">Buku berhasil ditambahkan ke database.</div>
          <?php endif; ?>

          <form method="POST" enctype="multipart/form-data">

            <div class="form-group">
              <label>Judul Buku</label>
              <input type="text" name="judul" class="form-control" required>
            </div>

            <div class="form-group">
              <label>Penulis</label>
              <input type="text" name="penulis" class="form-control" required>
            </div>

            <div class="form-group">
              <label>Kategori</label>
              <input type="text" name="kategori" class="form-control" placeholder="Contoh: Informatika">
            </div>

            <div class="form-group">
              <label>Status</label>
              <select name="status" class="form-control">
                <option value="belum">Belum dibaca</option>
                <option value="dibaca">Dibaca</option>
              </select>
            </div>

            <div class="form-group">
              <label>Upload File Buku (PDF)</label>
              <input type="file" name="file" class="form-control" accept="application/pdf">
            </div>

            <div class="form-group">
              <label>Foto Cover Buku (JPG/PNG/GIF/WEBP)</label>
              <input type="file" name="cover_image" class="form-control" accept="image/*" id="coverInput" onchange="previewCover(event)">
              <small class="text-muted">Opsional. Maks 5MB. Foto ini akan tampil di Daftar Buku.</small>
              <div class="mt-2">
                <img id="coverPreview" src="" alt="" style="display:none;max-width:150px;max-height:200px;border-radius:8px;">
              </div>
            </div>

            <button type="submit" class="btn btn-primary">
              Tambah Buku
            </button>

          </form>

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
<script>
function previewCover(event) {
  const file = event.target.files[0];
  const preview = document.getElementById('coverPreview');
  if (file) {
    preview.src = URL.createObjectURL(file);
    preview.style.display = 'block';
  } else {
    preview.style.display = 'none';
  }
}
</script>

</body>
</html>
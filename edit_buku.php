<?php
session_start();

if (!isset($_SESSION['username']) && isset($_COOKIE['user'])) {
    $_SESSION['username'] = $_COOKIE['user'];
    if (isset($_COOKIE['role'])) {
        $_SESSION['role'] = $_COOKIE['role'];
    }
}

// Proteksi: Redirect ke login jika tidak ada session
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

// Proteksi: Hanya admin yang bisa akses halaman ini
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: tambah_buku.php");
    exit();
}

require_once 'db.php';
$conn = get_db_connection();

$error = '';
$success = '';
$buku = null;

// Validasi ID
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: daftar_buku.php");
    exit();
}

$id = $_GET['id'];

// Ambil data buku
$stmt = $conn->prepare("SELECT id, judul, penulis, penerbit, tahun_terbit, isbn, kategori, stok, deskripsi, cover_image FROM buku WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$buku = $result->fetch_assoc();
$stmt->close();

if (!$buku) {
    header("Location: daftar_buku.php");
    exit();
}

// PROSES UPDATE BUKU
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $judul = trim($_POST['judul'] ?? '');
    $penulis = trim($_POST['penulis'] ?? '');
    $penerbit = trim($_POST['penerbit'] ?? '');
    $tahun_terbit = $_POST['tahun_terbit'] ?? null;
    $isbn = trim($_POST['isbn'] ?? '');
    $kategori = trim($_POST['kategori'] ?? '');
    $stok = $_POST['stok'] ?? 0;
    $deskripsi = trim($_POST['deskripsi'] ?? '');

    // Validasi
    if (empty($judul) || empty($penulis)) {
        $error = 'Judul dan Penulis harus diisi.';
    } else {
        $cover_image = $buku['cover_image'];

        // Proses update cover image
        if (isset($_FILES['cover_image']) && $_FILES['cover_image']['error'] === UPLOAD_ERR_OK) {
            $upload_dir = 'profiles/books/';
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }

            $file_name = $_FILES['cover_image']['name'];
            $file_tmp = $_FILES['cover_image']['tmp_name'];
            $file_size = $_FILES['cover_image']['size'];
            $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

            // Validasi tipe file
            $allowed_types = ['jpg', 'jpeg', 'png', 'gif'];
            if (!in_array($file_ext, $allowed_types)) {
                $error = 'Tipe file gambar harus JPG, PNG, atau GIF.';
            } elseif ($file_size > 5 * 1024 * 1024) { // 5MB
                $error = 'Ukuran gambar tidak boleh lebih dari 5MB.';
            } else {
                // Hapus file lama
                if ($buku['cover_image']) {
                    $old_file = $upload_dir . $buku['cover_image'];
                    if (file_exists($old_file)) {
                        unlink($old_file);
                    }
                }

                // Generate nama file unik
                $cover_image = 'book_' . time() . '.' . $file_ext;
                $upload_path = $upload_dir . $cover_image;

                if (!move_uploaded_file($file_tmp, $upload_path)) {
                    $error = 'Gagal mengupload gambar cover.';
                    $cover_image = $buku['cover_image'];
                }
            }
        }

        if (empty($error)) {
            // Update buku ke database
            $stmt = $conn->prepare("UPDATE buku SET judul = ?, penulis = ?, penerbit = ?, tahun_terbit = ?, isbn = ?, kategori = ?, stok = ?, deskripsi = ?, cover_image = ? WHERE id = ?");
            $stmt->bind_param("sssisssssi", $judul, $penulis, $penerbit, $tahun_terbit, $isbn, $kategori, $stok, $deskripsi, $cover_image, $id);

            if ($stmt->execute()) {
                $success = 'Buku berhasil diperbarui!';
                // Update data lokal untuk tampilan
                $buku['judul'] = $judul;
                $buku['penulis'] = $penulis;
                $buku['penerbit'] = $penerbit;
                $buku['tahun_terbit'] = $tahun_terbit;
                $buku['isbn'] = $isbn;
                $buku['kategori'] = $kategori;
                $buku['stok'] = $stok;
                $buku['deskripsi'] = $deskripsi;
                $buku['cover_image'] = $cover_image;
            } else {
                $error = 'Gagal memperbarui buku. ' . $conn->error;
            }
            $stmt->close();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <title>Edit Buku | LitSpace</title>

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
      margin-bottom: 8px;
    }

    .form-control {
      border-radius: 12px;
      border: 1px solid #d1d5db;
      box-shadow: none;
      padding: 10px 15px;
    }

    .form-control:focus {
      border-color: #667eea;
      box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
    }

    textarea.form-control {
      resize: vertical;
      min-height: 100px;
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

    .btn-primary:active {
      transform: translateY(0);
    }

    .btn-secondary {
      background: #6b7280;
      color: white;
      border: none;
      border-radius: 10px;
      transition: all 0.3s ease;
      padding: 12px 25px;
      font-weight: 600;
    }

    .btn-secondary:hover {
      background: #4b5563;
      transform: translateY(-2px);
    }

    .alert {
      border-radius: 12px;
      border: none;
      margin-bottom: 20px;
    }

    .alert-danger {
      background-color: #fee2e2;
      color: #991b1b;
    }

    .alert-success {
      background-color: #dcfce7;
      color: #166534;
    }

    .form-row {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 20px;
    }

    @media (max-width: 768px) {
      .form-row {
        grid-template-columns: 1fr;
      }
    }

    .main-footer {
      background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%);
      color: white;
      padding: 20px !important;
      font-weight: 500;
      box-shadow: 0 -2px 10px rgba(0,0,0,0.1);
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

    .cover-preview {
      max-width: 150px;
      max-height: 200px;
      border-radius: 8px;
      margin-top: 10px;
    }

    .button-group {
      display: flex;
      gap: 10px;
      margin-top: 20px;
    }
  </style>
</head>

<body class="hold-transition sidebar-mini">
<div class="wrapper">

<!-- Sidebar -->
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

        <li class="nav-item">
          <a href="perpustakaan.php" class="nav-link">
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
  <div class="content-header">
    <div class="container-fluid">
      <h1>Edit Buku</h1>
    </div>
  </div>

  <div class="content">
    <div class="container-fluid">

      <?php if (!empty($error)): ?>
      <div class="alert alert-danger">
        <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error); ?>
      </div>
      <?php endif; ?>

      <?php if (!empty($success)): ?>
      <div class="alert alert-success">
        <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($success); ?>
      </div>
      <?php endif; ?>

      <div class="card">
        <div class="card-header">
          <h3 class="card-title">Edit Buku: <?php echo htmlspecialchars($buku['judul']); ?></h3>
        </div>
        <div class="card-body">

          <form method="POST" enctype="multipart/form-data">

            <div class="form-row">
              <div class="form-group">
                <label for="judul">Judul Buku <span class="text-danger">*</span></label>
                <input type="text" id="judul" name="judul" class="form-control" required value="<?php echo htmlspecialchars($buku['judul']); ?>">
              </div>

              <div class="form-group">
                <label for="penulis">Penulis <span class="text-danger">*</span></label>
                <input type="text" id="penulis" name="penulis" class="form-control" required value="<?php echo htmlspecialchars($buku['penulis']); ?>">
              </div>
            </div>

            <div class="form-row">
              <div class="form-group">
                <label for="penerbit">Penerbit</label>
                <input type="text" id="penerbit" name="penerbit" class="form-control" value="<?php echo htmlspecialchars($buku['penerbit'] ?? ''); ?>">
              </div>

              <div class="form-group">
                <label for="tahun_terbit">Tahun Terbit</label>
                <input type="number" id="tahun_terbit" name="tahun_terbit" class="form-control" min="1900" max="<?php echo date('Y'); ?>" value="<?php echo htmlspecialchars($buku['tahun_terbit'] ?? date('Y')); ?>">
              </div>
            </div>

            <div class="form-row">
              <div class="form-group">
                <label for="isbn">ISBN</label>
                <input type="text" id="isbn" name="isbn" class="form-control" value="<?php echo htmlspecialchars($buku['isbn'] ?? ''); ?>">
              </div>

              <div class="form-group">
                <label for="kategori">Kategori</label>
                <input type="text" id="kategori" name="kategori" class="form-control" value="<?php echo htmlspecialchars($buku['kategori'] ?? ''); ?>">
              </div>
            </div>

            <div class="form-group">
              <label for="stok">Stok Buku</label>
              <input type="number" id="stok" name="stok" class="form-control" min="0" value="<?php echo htmlspecialchars($buku['stok']); ?>">
            </div>

            <div class="form-group">
              <label for="deskripsi">Deskripsi</label>
              <textarea id="deskripsi" name="deskripsi" class="form-control"><?php echo htmlspecialchars($buku['deskripsi'] ?? ''); ?></textarea>
            </div>

            <div class="form-group">
              <label for="cover_image">Cover Buku (Gambar)</label>
              <?php if (!empty($buku['cover_image']) && file_exists('profiles/books/' . $buku['cover_image'])): ?>
              <div>
                <p class="text-muted mb-2">Cover Saat Ini:</p>
                <img src="profiles/books/<?php echo htmlspecialchars($buku['cover_image']); ?>" alt="Cover" class="cover-preview">
              </div>
              <?php endif; ?>
              <input type="file" id="cover_image" name="cover_image" class="form-control" accept="image/*">
              <small class="text-muted">Format: JPG, PNG, GIF. Maks: 5MB (Kosongkan jika tidak ingin mengubah)</small>
            </div>

            <div class="button-group">
              <button type="submit" class="btn btn-primary">
                <i class="fas fa-save"></i> Simpan Perubahan
              </button>
              <a href="daftar_buku.php" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Kembali
              </a>
            </div>

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

</body>
</html>

<?php
$DB_HOST = 'localhost';
$DB_USER = 'root';
$DB_PASS = '';
$DB_NAME = 'uts_perpus';

$conn = new mysqli($DB_HOST, $DB_USER, $DB_PASS);
if ($conn->connect_error) {
    die('Database connection failed: ' . $conn->connect_error);
}

if (!$conn->query("CREATE DATABASE IF NOT EXISTS `$DB_NAME` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci")) {
    die('Database creation failed: ' . $conn->error);
}

$conn->select_db($DB_NAME);

// SUDAH DITAMBAHKAN KOLOM HP DI SINI
$createUsers = "CREATE TABLE IF NOT EXISTS `users` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `username` VARCHAR(50) NOT NULL UNIQUE,
    `password` VARCHAR(255) NOT NULL,
    `full_name` VARCHAR(100) DEFAULT NULL,
    `hp` VARCHAR(20) DEFAULT NULL,
    `role` ENUM('admin','user') NOT NULL DEFAULT 'user',
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

if (!$conn->query($createUsers)) {
    die('Table creation failed: ' . $conn->error);
}

$defaultUsers = [
    ['admin', 'admin123', 'Administrator', 'admin'],
    ['user', 'user123', 'User Biasa', 'user'],
];

$insertSql = "INSERT INTO users (`username`, `password`, `full_name`, `role`)
    SELECT ?, ?, ?, ? FROM DUAL
    WHERE NOT EXISTS (SELECT 1 FROM users WHERE username = ? )";
$stmt = $conn->prepare($insertSql);
if (!$stmt) {
    die('Prepare failed: ' . $conn->error);
}

foreach ($defaultUsers as $userData) {
    [$username, $plainPassword, $fullName, $role] = $userData;
    $hashedPassword = password_hash($plainPassword, PASSWORD_DEFAULT);
    $stmt->bind_param('sssss', $username, $hashedPassword, $fullName, $role, $username);
    $stmt->execute();
}

$stmt->close();

// TABEL BUKU (tabel tunggal & konsisten dipakai di semua halaman: tambah, daftar, edit, hapus)
$createBuku = "CREATE TABLE IF NOT EXISTS `buku` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `judul` VARCHAR(150) NOT NULL,
    `penulis` VARCHAR(100) NOT NULL,
    `penerbit` VARCHAR(100) DEFAULT NULL,
    `tahun_terbit` YEAR DEFAULT NULL,
    `isbn` VARCHAR(30) DEFAULT NULL,
    `kategori` VARCHAR(50) DEFAULT NULL,
    `status` ENUM('dibaca','belum') NOT NULL DEFAULT 'belum',
    `stok` INT UNSIGNED NOT NULL DEFAULT 1,
    `deskripsi` TEXT DEFAULT NULL,
    `cover_image` VARCHAR(255) DEFAULT NULL,
    `file_path` VARCHAR(255) DEFAULT NULL,
    `ditambahkan_oleh` VARCHAR(50) DEFAULT NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

if (!$conn->query($createBuku)) {
    die('Table creation failed: ' . $conn->error);
}

// TABEL PEMINJAMAN (mencatat siapa meminjam buku apa, kapan pinjam & batas kembali)
$createPeminjaman = "CREATE TABLE IF NOT EXISTS `peminjaman` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `buku_id` INT UNSIGNED NOT NULL,
    `username` VARCHAR(50) NOT NULL,
    `tanggal_pinjam` DATE NOT NULL,
    `tanggal_kembali` DATE NOT NULL COMMENT 'Batas waktu pengembalian',
    `tanggal_dikembalikan` DATE DEFAULT NULL COMMENT 'Diisi saat buku benar-benar dikembalikan',
    `status` ENUM('dipinjam','dikembalikan') NOT NULL DEFAULT 'dipinjam',
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    KEY `idx_buku_id` (`buku_id`),
    KEY `idx_username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

if (!$conn->query($createPeminjaman)) {
    die('Table creation failed: ' . $conn->error);
}

// Isi contoh data buku kalau tabel buku masih kosong, biar ada isinya dari awal
$qCekBuku = $conn->query("SELECT COUNT(*) AS total FROM buku");
$rCekBuku = $qCekBuku ? $qCekBuku->fetch_assoc() : ['total' => 0];
if ((int) $rCekBuku['total'] === 0) {
    $contohBuku = [
        ['Laskar Pelangi', 'Andrea Hirata', 'Bentang Pustaka', 2005, 'Novel', 'belum', 3, 'Kisah perjuangan anak-anak Belitung mengejar pendidikan.'],
        ['Bumi Manusia', 'Pramoedya Ananta Toer', 'Hasta Mitra', 1980, 'Novel Sejarah', 'belum', 2, 'Novel pertama dari tetralogi Buru.'],
        ['Filosofi Teras', 'Henry Manampiring', 'Kompas', 2018, 'Pengembangan Diri', 'belum', 4, 'Pengantar filsafat Stoa untuk kehidupan modern.'],
        ['Atomic Habits', 'James Clear', 'Gramedia', 2018, 'Pengembangan Diri', 'belum', 3, 'Cara membangun kebiasaan baik dan menghilangkan kebiasaan buruk.'],
        ['Sapiens: Riwayat Singkat Umat Manusia', 'Yuval Noah Harari', 'KPG', 2011, 'Sejarah', 'belum', 2, 'Perjalanan panjang sejarah manusia dari masa ke masa.'],
        ['Negeri 5 Menara', 'Ahmad Fuadi', 'Gramedia', 2009, 'Novel', 'belum', 3, 'Kisah santri di pondok pesantren yang meraih mimpi ke lima benua.'],
    ];
    $stmtSeed = $conn->prepare('INSERT INTO buku (judul, penulis, penerbit, tahun_terbit, kategori, status, stok, deskripsi, ditambahkan_oleh) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)');
    $seedBy = 'admin';
    foreach ($contohBuku as $b) {
        [$judul, $penulis, $penerbit, $tahun, $kategori, $status, $stok, $deskripsi] = $b;
        $stmtSeed->bind_param('sssisssss', $judul, $penulis, $penerbit, $tahun, $kategori, $status, $stok, $deskripsi, $seedBy);
        $stmtSeed->execute();
    }
    $stmtSeed->close();
}

// Pastikan folder upload cover foto & file buku selalu ada
$coverDir = __DIR__ . DIRECTORY_SEPARATOR . 'profiles' . DIRECTORY_SEPARATOR . 'books';
if (!is_dir($coverDir)) {
    mkdir($coverDir, 0755, true);
}
$bukuDir = __DIR__ . DIRECTORY_SEPARATOR . 'Buku';
if (!is_dir($bukuDir)) {
    mkdir($bukuDir, 0755, true);
}

function get_db_connection() {
    global $conn;
    return $conn;
}

// INI FUNGSI SANITIZE YANG TADI HILANG
function sanitize($data) {
    global $conn;
    return mysqli_real_escape_string($conn, trim(htmlspecialchars($data)));
}
?>
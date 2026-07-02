<?php
session_start();
require_once 'db.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $full_name = trim($_POST['full_name'] ?? '');

    // Validasi
    if (empty($username) || empty($password) || empty($full_name)) {
        $error = 'Semua field harus diisi.';
    } elseif (strlen($password) < 6) {
        $error = 'Password minimal 6 karakter.';
    } elseif ($password !== $confirm_password) {
        $error = 'Password tidak cocok.';
    } else {
        $conn = get_db_connection();
        
        // Cek username sudah ada atau belum
        $stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $error = 'Username sudah terdaftar.';
        } else {
            // Insert user baru
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $role = 'user';
            
            $insertStmt = $conn->prepare("INSERT INTO users (username, password, full_name, role) VALUES (?, ?, ?, ?)");
            $insertStmt->bind_param("ssss", $username, $hashed_password, $full_name, $role);
            
            if ($insertStmt->execute()) {
                $success = 'Registrasi berhasil! Silakan login.';
                $_POST = [];
            } else {
                $error = 'Gagal mendaftar. Coba lagi.';
            }
            $insertStmt->close();
        }
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Daftar - LitSpace</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap');

        * {
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            margin: 0;
            min-height: 100vh;
            background: url('bg/perpustakaan.jpg') no-repeat center center fixed;
            background-size: cover;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            color: #222;
        }

        body::before {
            content: '';
            position: absolute;
            inset: 0;
            background: rgba(15, 23, 42, 0.35);
            z-index: 0;
        }

        .register-box {
            position: relative;
            z-index: 1;
            width: 380px;
            margin: 20px;
            padding: 32px 28px;
            background: rgba(255, 255, 255, 0.95);
            border-radius: 24px;
            text-align: center;
            box-shadow: 0 24px 60px rgba(15, 23, 42, 0.18);
            border: 1px solid rgba(255, 255, 255, 0.45);
        }

        .register-box h2 {
            margin: 0 0 10px;
            font-size: 28px;
            letter-spacing: -0.4px;
        }

        .register-box p.subtitle {
            margin: 0 0 24px;
            color: #556080;
            font-size: 13px;
            line-height: 1.6;
        }

        .input-group {
            position: relative;
            margin: 12px 0;
        }

        .input-group span {
            position: absolute;
            left: 14px;
            top: 50%;
            transform: translateY(-50%);
            color: #667085;
            font-size: 16px;
            pointer-events: none;
        }

        .input-group input {
            width: 100%;
            padding: 12px 16px 12px 42px;
            border: 1px solid #dfe4ee;
            border-radius: 12px;
            background: #f9fbff;
            color: #1f2937;
            font-size: 14px;
            transition: border-color 0.25s ease, box-shadow 0.25s ease;
        }

        .input-group input:focus {
            outline: none;
            border-color: #4f46e5;
            box-shadow: 0 0 0 4px rgba(79, 70, 229, 0.12);
            background: #ffffff;
        }

        button {
            width: 100%;
            padding: 12px 0;
            margin-top: 12px;
            background: linear-gradient(135deg, #4f46e5 0%, #3b82f6 100%);
            color: white;
            border: none;
            border-radius: 12px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.25s ease;
            box-shadow: 0 12px 32px rgba(59, 130, 246, 0.22);
        }

        button:hover {
            transform: translateY(-2px);
            box-shadow: 0 18px 44px rgba(59, 130, 246, 0.28);
        }

        button:active {
            transform: translateY(0);
            opacity: 0.95;
        }

        .error {
            color: #ef4444;
            font-size: 13px;
            margin: 12px 0;
            padding: 10px;
            background: #fee2e2;
            border-radius: 8px;
            font-weight: 500;
        }

        .success {
            color: #16a34a;
            font-size: 13px;
            margin: 12px 0;
            padding: 10px;
            background: #dcfce7;
            border-radius: 8px;
            font-weight: 500;
        }

        .login-link {
            margin-top: 16px;
            font-size: 13px;
            color: #556080;
        }

        .login-link a {
            color: #4f46e5;
            text-decoration: none;
            font-weight: 600;
            transition: color 0.25s ease;
        }

        .login-link a:hover {
            color: #3b82f6;
            text-decoration: underline;
        }
    </style>
</head>
<body>

<div class="register-box">
    <h2>Buat Akun 📚</h2>
    <p class="subtitle">Bergabunglah dengan LitSpace dan mulai membaca buku pilihan.</p>

    <?php if ($error): ?>
        <div class="error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <?php if ($success): ?>
        <div class="success"><?php echo htmlspecialchars($success); ?></div>
    <?php endif; ?>

    <form method="POST">
        <div class="input-group">
            <span>👤</span>
            <input type="text" name="full_name" placeholder="Nama Lengkap" required value="<?php echo htmlspecialchars($_POST['full_name'] ?? ''); ?>">
        </div>
        <div class="input-group">
            <span>@</span>
            <input type="text" name="username" placeholder="Username" required value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>">
        </div>
        <div class="input-group">
            <span>🔒</span>
            <input type="password" name="password" placeholder="Password (minimal 6 karakter)" required>
        </div>
        <div class="input-group">
            <span>🔒</span>
            <input type="password" name="confirm_password" placeholder="Konfirmasi Password" required>
        </div>
        <button type="submit">Daftar</button>
    </form>

    <p class="login-link">Sudah punya akun? <a href="login.php">Masuk di sini</a></p>
</div>

</body>
</html>

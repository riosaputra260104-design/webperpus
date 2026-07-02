<!DOCTYPE html>
<?php $serverError = isset($_GET['error']) ? $_GET['error'] : ''; 
?>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>LitSpace</title>
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

        .login-box {
            position: relative;
            z-index: 1;
            width: 360px;
            margin: 20px;
            padding: 32px 28px;
            background: rgba(255, 255, 255, 0.95);
            border-radius: 24px;
            text-align: center;
            box-shadow: 0 24px 60px rgba(15, 23, 42, 0.18);
            border: 1px solid rgba(255, 255, 255, 0.45);
        }

        .login-box h2 {
            margin: 0 0 10px;
            font-size: 30px;
            letter-spacing: -0.4px;
        }

        .login-box p.subtitle {
            margin: 0 0 24px;
            color: #556080;
            font-size: 14px;
            line-height: 1.6;
        }

        .input-group {
            position: relative;
            margin: 10px 0;
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

        .input-group input[type="text"],
        .input-group input[type="password"] {
            width: 100%;
            padding: 14px 16px 14px 42px;
            border: 1px solid #dfe4ee;
            border-radius: 14px;
            background: #f9fbff;
            color: #1f2937;
            font-size: 15px;
            transition: border-color 0.25s ease, box-shadow 0.25s ease;
        }

        .input-group input[type="text"]:focus,
        .input-group input[type="password"]:focus {
            outline: none;
            border-color: #4f46e5;
            box-shadow: 0 0 0 4px rgba(79, 70, 229, 0.12);
            background: #ffffff;
        }

        .ingat {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            margin: 18px 0 24px;
            font-size: 14px;
            color: #475569;
        }

        .ingat input {
            width: auto;
            margin: 0;
            cursor: pointer;
        }

        .ingat label {
            cursor: pointer;
        }

        button {
            width: 100%;
            padding: 14px 0;
            background: linear-gradient(135deg, #4f46e5 0%, #3b82f6 100%);
            color: white;
            border: none;
            border-radius: 14px;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
            transition: transform 0.25s ease, box-shadow 0.25s ease, opacity 0.25s ease;
            box-shadow: 0 12px 32px rgba(59, 130, 246, 0.22);
        }

        button:hover {
            transform: translateY(-2px) scale(1.01);
            box-shadow: 0 18px 44px rgba(59, 130, 246, 0.28);
        }

        button:active {
            transform: translateY(0) scale(0.99);
            opacity: 0.95;
        }

        @keyframes btn-glow {
            0%, 100% {
                box-shadow: 0 12px 32px rgba(59, 130, 246, 0.22);
            }
            50% {
                box-shadow: 0 18px 44px rgba(59, 130, 246, 0.32);
            }
        }

        button {
            animation: btn-glow 4s ease-in-out infinite;
        }
       
        .error {
            color: #ef4444;
            font-size: 14px;
            margin-top: 12px;
            min-height: 20px;
            font-weight: 500;
        }

        .register-link {
            margin-top: 16px;
            font-size: 14px;
            color: #475569;
        }

        .register-link a {
            color: #4f46e5;
            text-decoration: none;
            font-weight: 600;
            transition: color 0.25s ease;
        }

        .register-link a:hover {
            color: #3b82f6;
            text-decoration: underline;
        }
    </style>
</head>
<body>

<div class="login-box">
    <h2>LitSpace 📚</h2>
    <p class="subtitle">Masuk untuk mengakses perpustakaan digital dan koleksi buku terbaik.</p>
    <form method="POST" action="proses_login.php" onsubmit="return validateForm()">
        <div class="input-group">
            <span>👤</span>
            <input type="text" name="username" placeholder="Username" required>
        </div>
        <div class="input-group">
            <span>🔒</span>
            <input type="password" name="password" placeholder="Password" required>
        </div>
        <div class="ingat">
            <input type="checkbox" name="ingatsaya" id="ingatsaya"> 
            <label for="ingatsaya">Ingat Saya</label>
        </div>
        <button type="submit">Login</button>
    </form>

    <p class="error" id="errorMsg"><?php
        if ($serverError === 'invalid') {
            echo 'Username atau password salah.';
        } elseif ($serverError === 'checkbox') {
            echo 'Anda harus mencentang "Ingat Saya" untuk login.';
        }
    ?></p>

    <p class="register-link">Belum punya akun? <a href="register.php">Daftar di sini</a></p>

    <script>
        function validateForm() {
            var checkbox = document.getElementById('ingatsaya');
            var errorMsg = document.getElementById('errorMsg');
            
            if (!checkbox.checked) {
                errorMsg.textContent = 'Anda harus mencentang "Ingat Saya" untuk login';
                return false;
            }
            
            errorMsg.textContent = '';
            return true;
        }
    </script>
</div>

<script src="script.js"></script>

</body>
</html>


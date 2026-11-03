<?php
session_start();
require_once 'config.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    $stmt = $pdo->prepare(
        'SELECT id, nama, username, password, role
         FROM users
         WHERE username = ?'
    );
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        session_regenerate_id(true);

        $_SESSION['user'] = [
            'id' => $user['id'],
            'nama' => $user['nama'],
            'username' => $user['username'],
            'role' => $user['role']
        ];

        header('Location: dashboard.php');
        exit;
    }

    $error = 'Username atau password salah.';
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Peminjaman Alat</title>
    <!-- Memuat Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background: linear-gradient(135deg, #121212 0%, #1f1f1f 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0;
            padding: 2rem 0;
        }
        /* Desain Kartu Emas Mewah */
        .gold-card-wrapper {
            width: 100%;
            max-width: 420px;
            padding: 3px;
            background: linear-gradient(135deg, #bf953f 0%, #fcf6ba 25%, #b38728 50%, #fbf5b7 75%, #aa771c 100%);
            border-radius: 1.5rem;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.5), 0 0 30px rgba(191, 149, 63, 0.15);
        }
        .login-card {
            background: #141414;
            padding: 2.5rem;
            border-radius: calc(1.5rem - 3px);
            color: #f8f9fa;
        }
        .gold-title {
            background: linear-gradient(135deg, #bf953f 0%, #fcf6ba 50%, #b38728 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            font-weight: 700;
        }
        .icon-box {
            width: 60px;
            height: 60px;
            background: linear-gradient(135deg, rgba(191,149,63,0.15) 0%, rgba(170,119,28,0.3) 100%);
            border: 1px solid rgba(252,246,186,0.3);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.25rem auto;
            font-size: 1.75rem;
            color: #fcf6ba;
        }
        .form-control {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(191, 149, 63, 0.3);
            color: #fff;
            border-radius: 0.75rem;
            padding: 0.75rem 1rem;
        }
        .form-control:focus {
            background: rgba(255, 255, 255, 0.08);
            border-color: #fcf6ba;
            color: #fff;
            box-shadow: 0 0 10px rgba(252, 246, 186, 0.2);
        }
        .form-control::placeholder {
            color: rgba(255, 255, 255, 0.4);
        }
        .btn-gold {
            background: linear-gradient(135deg, #bf953f 0%, #aa771c 100%);
            border: none;
            color: #141414;
            font-weight: 700;
            border-radius: 0.75rem;
            padding: 0.75rem;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(191, 149, 63, 0.3);
        }
        .btn-gold:hover {
            background: linear-gradient(135deg, #fcf6ba 0%, #bf953f 100%);
            color: #000;
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(191, 149, 63, 0.5);
        }
        .demo-box {
            background: rgba(255, 255, 255, 0.03) !important;
            border: 1px solid rgba(191, 149, 63, 0.2);
            color: #ccc !important;
        }
        /* Custom Styling untuk Tombol Mata */
        .toggle-password {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(191, 149, 63, 0.3);
            border-left: none;
            color: #fcf6ba;
            cursor: pointer;
            border-top-right-radius: 0.75rem;
            border-bottom-right-radius: 0.75rem;
            padding: 0.75rem 1rem;
        }
        .toggle-password:hover {
            background: rgba(255, 255, 255, 0.1);
            color: #fff;
        }
        .register-link {
            color: #fcf6ba;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        .register-link:hover {
            color: #bf953f;
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="container px-3 d-flex justify-content-center">
        <div class="gold-card-wrapper">
            <div class="login-card">
                <!-- Ikon Dekoratif Mewah -->
                <div class="icon-box shadow-sm">
                    <i class="bi bi-shield-lock-fill"></i>
                </div>

                <div class="text-center mb-4">
                    <h3 class="gold-title mb-1">Peminjaman Alat</h3>
                    <p class="text-secondary small">Silakan masuk ke akun kamu</p>
                </div>

                <?php if (!empty($error)): ?>
                    <div class="alert alert-danger bg-danger bg-opacity-25 border-danger text-danger py-2 small rounded-3 text-center" role="alert">
                        <i class="bi bi-exclamation-triangle-fill me-1"></i> <?= htmlspecialchars($error) ?>
                    </div>
                <?php endif; ?>

                <form method="post">
                    <div class="mb-3">
                        <label class="form-label fw-semibold text-secondary small">Username</label>
                        <div class="input-group">
                            <span class="input-group-text bg-transparent border-end-0 text-warning border-secondary opacity-75"><i class="bi bi-person"></i></span>
                            <input type="text" name="username" class="form-control border-start-0 ps-0" placeholder="Masukkan username..." required>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-semibold text-secondary small">Password</label>
                        <div class="input-group">
                            <span class="input-group-text bg-transparent border-end-0 text-warning border-secondary opacity-75"><i class="bi bi-key"></i></span>
                            <input type="password" name="password" id="password" class="form-control border-start-0 border-end-0 ps-0" placeholder="Masukkan password..." required>
                            <span class="input-group-text toggle-password" id="togglePassword" title="Tampilkan/Sembunyikan Password">
                                <i class="bi bi-eye-slash" id="eyeIcon"></i>
                            </span>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-gold w-100 py-2 fw-bold shadow-sm mb-3">
                        <i class="bi bi-box-arrow-in-right me-1"></i> Login
                    </button>
                </form>

                <!-- Tautan Pendaftaran Akun Baru -->
                <div class="text-center mb-3">
                    <p class="text-secondary small mb-0">Belum punya akun? <a href="register.php" class="register-link">Daftar di sini</a></p>
                </div>

                <!-- Informasi Daftar Akun Demo -->
                <div class="pt-3 border-top border-secondary border-opacity-25 text-center">
                    <p class="text-secondary small mb-2 fw-semibold">Informasi Akun Demo:</p>
                    <div class="demo-box small text-start p-2 rounded">
                        <div>👑 <strong>Admin:</strong> admin / password</div>
                        <div>🛡️ <strong>Petugas:</strong> petugas / password</div>
                        <div>👤 <strong>Peminjam:</strong> peminjam / password</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS Bundle & Script untuk Toggle Password -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const togglePassword = document.getElementById('togglePassword');
        const passwordInput = document.getElementById('password');
        const eyeIcon = document.getElementById('eyeIcon');

        togglePassword.addEventListener('click', function () {
            const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', type);

            if (type === 'text') {
                eyeIcon.classList.remove('bi-eye-slash');
                eyeIcon.classList.add('bi-eye');
            } else {
                eyeIcon.classList.remove('bi-eye');
                eyeIcon.classList.add('bi-eye-slash');
            }
        });
    </script>
</body>
</html>
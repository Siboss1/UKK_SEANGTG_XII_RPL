<?php
session_start();
require_once 'config.php';

// Validasi Keamanan: Pastikan user sudah login dan merupakan admin (opsional sesuai kebutuhan)
if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}

$pesan_sukses = '';
$pesan_error = '';

// Proses Tambah User
if (isset($_POST['tambah_user'])) {
    $nama = trim($_POST['nama']);
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    $role = $_POST['role'];

    if (!empty($nama) && !empty($username) && !empty($password) && !empty($role)) {
        // Hash password agar aman
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        try {
            $stmt = $pdo->prepare("INSERT INTO users (nama, username, password, role) VALUES (?, ?, ?, ?)");
            $stmt->execute([$nama, $username, $hashed_password, $role]);
            $pesan_sukses = "User baru berhasil ditambahkan!";
        } catch (PDOException $e) {
            $pesan_error = "Gagal menambah user: " . $e->getMessage();
        }
    } else {
        $pesan_error = "Semua kolom wajib diisi!";
    }
}

// Proses Hapus User
if (isset($_GET['hapus'])) {
    $id_user = $_GET['hapus'];
    try {
        $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
        $stmt->execute([$id_user]);
        header('Location: data_user.php');
        exit;
    } catch (PDOException $e) {
        $pesan_error = "Gagal menghapus user!";
    }
}

// Ambil daftar seluruh user dari database
try {
    $stmt = $pdo->query("SELECT * FROM users ORDER BY id DESC");
    $users = $stmt->fetchAll();
} catch (PDOException $e) {
    $users = [];
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Data User - Peminjaman Alat</title>
    <!-- Bootstrap 5 CSS -->
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
            color: #f8f9fa;
            margin: 0;
            padding: 2rem 0;
        }
        /* Kartu kerangka emas mewah */
        .gold-card-wrapper {
            width: 100%;
            padding: 3px;
            background: linear-gradient(135deg, #bf953f 0%, #fcf6ba 25%, #b38728 50%, #fbf5b7 75%, #aa771c 100%);
            border-radius: 1.5rem;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.6), 0 0 35px rgba(191, 149, 63, 0.2);
            margin-bottom: 2rem;
        }
        .content-card {
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
        /* Form Control Custom Emas */
        .form-control, .form-select {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(191, 149, 63, 0.3);
            color: #fff;
            border-radius: 0.75rem;
            padding: 0.75rem 1rem;
        }
        .form-control:focus, .form-select:focus {
            background: rgba(255, 255, 255, 0.08);
            border-color: #fcf6ba;
            color: #fff;
            box-shadow: 0 0 10px rgba(252, 246, 186, 0.2);
        }
        .form-control::placeholder {
            color: rgba(255, 255, 255, 0.4);
        }
        .form-select option {
            background: #141414;
            color: #fff;
        }
        /* Tombol Emas */
        .btn-gold {
            background: linear-gradient(135deg, #bf953f 0%, #aa771c 100%);
            border: none;
            color: #141414;
            font-weight: 700;
            border-radius: 0.75rem;
            padding: 0.75rem 1.5rem;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(191, 149, 63, 0.3);
        }
        .btn-gold:hover {
            background: linear-gradient(135deg, #fcf6ba 0%, #bf953f 100%);
            color: #000;
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(191, 149, 63, 0.5);
        }
        /* Tombol Kembali / Outline Emas */
        .btn-outline-gold {
            background: rgba(191, 149, 63, 0.1);
            border: 1px solid rgba(191, 149, 63, 0.5);
            color: #fcf6ba;
            border-radius: 50rem;
            padding: 0.5rem 1.25rem;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        .btn-outline-gold:hover {
            background: linear-gradient(135deg, #bf953f 0%, #aa771c 100%);
            color: #141414;
            border-color: #fcf6ba;
        }
        /* Tabel Custom */
        .table-dark-gold {
            background: transparent;
            color: #f8f9fa;
        }
        .table-dark-gold th {
            background: rgba(191, 149, 63, 0.15);
            color: #fcf6ba;
            border-bottom: 2px solid rgba(191, 149, 63, 0.3);
            text-transform: uppercase;
            font-size: 0.85rem;
            letter-spacing: 0.5px;
            padding: 1rem;
        }
        .table-dark-gold td {
            background: rgba(255, 255, 255, 0.02);
            color: #e0e0e0;
            border-bottom: 1px solid rgba(191, 149, 63, 0.1);
            padding: 1rem;
            vertical-align: middle;
        }
        .table-dark-gold tbody tr:hover td {
            background: rgba(191, 149, 63, 0.05);
            color: #fff;
        }
        /* Badge Role */
        .badge-admin { background: #dc3545; color: #fff; }
        .badge-petugas { background: #0dcaf0; color: #000; }
        .badge-peminjam { background: #ffc107; color: #000; }
    </style>
</head>
<body>

    <div class="container my-4">
        <!-- Header & Tombol Kembali -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="gold-title mb-1"><i class="bi bi-people-fill me-2"></i> Kelola Data User</h2>
                <p class="text-secondary small mb-0">Tambah, lihat, dan kelola akun pengguna sistem</p>
            </div>
            <a href="dashboard.php" class="btn btn-outline-gold d-flex align-items-center gap-2">
                <i class="bi bi-arrow-left"></i> Kembali ke Dashboard
            </a>
        </div>

        <!-- Notifikasi -->
        <?php if (!empty($pesan_sukses)): ?>
            <div class="alert alert-success bg-success bg-opacity-25 border-success text-success text-center py-2 rounded-3 mb-4">
                <i class="bi bi-check-circle-fill me-1"></i> <?= $pesan_sukses; ?>
            </div>
        <?php endif; ?>
        <?php if (!empty($pesan_error)): ?>
            <div class="alert alert-danger bg-danger bg-opacity-25 border-danger text-danger text-center py-2 rounded-3 mb-4">
                <i class="bi bi-exclamation-triangle-fill me-1"></i> <?= $pesan_error; ?>
            </div>
        <?php endif; ?>

        <!-- Form Tambah User Baru -->
        <div class="gold-card-wrapper">
            <div class="content-card">
                <h5 class="gold-title mb-4"><i class="bi bi-person-plus-fill me-2"></i> Tambah User Baru</h5>
                
                <form action="" method="POST">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label text-secondary small fw-semibold">Nama Lengkap</label>
                            <input type="text" name="nama" class="form-control" placeholder="Masukkan nama lengkap..." required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-secondary small fw-semibold">Username</label>
                            <input type="text" name="username" class="form-control" placeholder="Masukkan username..." required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-secondary small fw-semibold">Password</label>
                            <input type="password" name="password" class="form-control" placeholder="Masukkan password..." required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-secondary small fw-semibold">Role / Level</label>
                            <select name="role" class="form-select" required>
                                <option value="" disabled selected>Pilih Role...</option>
                                <option value="Admin">Admin</option>
                                <option value="Petugas">Petugas</option>
                                <option value="Peminjam">Peminjam</option>
                            </select>
                        </div>
                        <div class="col-12 mt-4">
                            <button type="submit" name="tambah_user" class="btn btn-gold w-100 py-2">
                                <i class="bi bi-save2-fill me-1"></i> Simpan User
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Daftar Pengguna Sistem (Tabel) -->
        <div class="gold-card-wrapper">
            <div class="content-card">
                <h5 class="gold-title mb-4"><i class="bi bi-table me-2"></i> Daftar Pengguna Sistem</h5>

                <div class="table-responsive">
                    <table class="table table-dark-gold align-middle mb-0">
                        <thead>
                            <tr>
                                <th class="ps-3">#ID</th>
                                <th>Nama</th>
                                <th>Username</th>
                                <th>Role</th>
                                <th class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($users)): ?>
                                <tr>
                                    <td colspan="5" class="text-center py-4 text-secondary">Belum ada data pengguna.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($users as $row): ?>
                                <tr>
                                    <td class="ps-3 fw-bold text-warning">#<?= $row['id']; ?></td>
                                    <td class="fw-semibold"><?= htmlspecialchars($row['nama']); ?></td>
                                    <td><?= htmlspecialchars($row['username']); ?></td>
                                    <td>
                                        <?php 
                                            $r = strtolower($row['role']);
                                            $badge_class = 'badge-peminjam';
                                            if ($r === 'admin') $badge_class = 'badge-admin';
                                            elseif ($r === 'petugas') $badge_class = 'badge-petugas';
                                        ?>
                                        <span class="badge <?= $badge_class; ?> px-3 py-1 rounded-pill fw-bold text-uppercase" style="font-size: 0.7rem;"><?= htmlspecialchars($row['role']); ?></span>
                                    </td>
                                    <td class="text-center">
                                        <a href="data_user.php?hapus=<?= $row['id']; ?>" class="btn btn-danger btn-sm px-3 py-1 rounded-pill fw-semibold shadow-sm" onclick="return confirm('Yakin ingin menghapus user ini?')">
                                            <i class="bi bi-trash-fill me-1"></i> Hapus
                                        </a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
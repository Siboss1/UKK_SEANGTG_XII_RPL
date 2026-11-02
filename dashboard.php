<?php
session_start();
require_once 'config.php';

// Keamanan: Cek apakah pengguna sudah login
if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}

$role = strtolower($_SESSION['user']['role']); // admin, petugas, atau peminjam
$nama = $_SESSION['user']['nama'];
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Peminjaman Alat</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body { background-color: #f8f9fa; }
        .card { border-radius: 12px; border: none; }
        .list-group-item { border-radius: 8px !important; margin-bottom: 6px; }
    </style>
</head>
<body>

    <div class="container my-5" style="max-width: 800px;">
        <!-- Header Profil -->
        <div class="card shadow-sm p-4 mb-4 bg-white">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h3 class="fw-bold mb-1">Aplikasi Peminjaman Alat</h3>
                    <p class="text-muted mb-0">Selamat datang, <strong><?= htmlspecialchars($nama); ?></strong></p>
                </div>
                <span class="badge bg-primary px-3 py-2 fs-6 text-uppercase"><?= htmlspecialchars($role); ?></span>
            </div>
        </div>

        <!-- Menu Berdasarkan Level Pengguna -->
        <div class="card shadow-sm p-4 bg-white">
            <h5 class="fw-bold mb-3 border-bottom pb-2">Menu Utama</h5>
            <div class="list-group list-group-flush">

                <!-- 1. FITUR KHUSUS ADMIN -->
                <?php if ($role === 'admin'): ?>
                    <a href="data_user.php" class="list-group-item list-group-item-action list-group-item-light fw-semibold">
                        <i class="bi bi-people-fill text-primary me-2"></i> CRUD User
                    </a>
                    <a href="data_alat.php" class="list-group-item list-group-item-action list-group-item-light fw-semibold">
                        <i class="bi bi-tools text-primary me-2"></i> CRUD Alat
                    </a>
                    <a href="kategori.php" class="list-group-item list-group-item-action list-group-item-light fw-semibold">
                        <i class="bi bi-tags-fill text-primary me-2"></i> CRUD Kategori
                    </a>
                    <a href="peminjaman.php" class="list-group-item list-group-item-action list-group-item-light fw-semibold">
                        <i class="bi bi-card-checklist text-primary me-2"></i> CRUD Data Peminjaman
                    </a>
                    <a href="pengembalian.php" class="list-group-item list-group-item-action list-group-item-light fw-semibold">
                        <i class="bi bi-arrow-return-left text-primary me-2"></i> CRUD Pengembalian
                    </a>
                    <a href="log_aktifitas.php" class="list-group-item list-group-item-action list-group-item-light fw-semibold">
                        <i class="bi bi-journal-text text-primary me-2"></i> Log Aktifitas
                    </a>
                <?php endif; ?>

                <!-- 2. FITUR KHUSUS PETUGAS -->
                <?php if ($role === 'petugas'): ?>
                    <a href="peminjaman.php" class="list-group-item list-group-item-action list-group-item-light fw-semibold">
                        <i class="bi bi-check-circle-fill text-success me-2"></i> Menyetujui Peminjaman
                    </a>
                    <a href="pengembalian.php" class="list-group-item list-group-item-action list-group-item-light fw-semibold">
                        <i class="bi bi-eye-fill text-success me-2"></i> Memantau Pengembalian
                    </a>
                    <a href="riwayat.php" class="list-group-item list-group-item-action list-group-item-light fw-semibold">
                        <i class="bi bi-printer-fill text-success me-2"></i> Mencetak Laporan
                    </a>
                <?php endif; ?>

                <!-- 3. FITUR KHUSUS PEMINJAM -->
                <?php if ($role === 'peminjam'): ?>
                    <a href="data_alat.php" class="list-group-item list-group-item-action list-group-item-light fw-semibold">
                        <i class="bi bi-search text-warning me-2"></i> Melihat Daftar Alat
                    </a>
                    <a href="peminjaman.php" class="list-group-item list-group-item-action list-group-item-light fw-semibold">
                        <i class="bi bi-plus-circle-fill text-warning me-2"></i> Mengajukan Peminjaman
                    </a>
                    <a href="pengembalian.php" class="list-group-item list-group-item-action list-group-item-light fw-semibold">
                        <i class="bi bi-box-arrow-in-down-left text-warning me-2"></i> Mengembalikan Alat
                    </a>
                <?php endif; ?>

                <!-- LOGOUT (SEMUA ROLE) -->
                <a href="logout.php" class="list-group-item list-group-item-action list-group-item-danger fw-semibold mt-3">
                    <i class="bi bi-box-arrow-right me-2"></i> Logout
                </a>

            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
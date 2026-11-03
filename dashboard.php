<?php
session_start();
require_once 'config.php';

// Validasi Keamanan: Pastikan user sudah login
if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}

$user = $_SESSION['user'];
$role = strtolower($user['role']);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Peminjaman Alat</title>
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
            display: flex;
            align-items: center;
            justify-content: center;
            color: #f8f9fa;
            margin: 0;
            padding: 2rem 0;
        }
        /* Kartu utama dengan kerangka emas mewah */
        .gold-card-wrapper {
            width: 100%;
            max-width: 550px;
            padding: 3px;
            background: linear-gradient(135deg, #bf953f 0%, #fcf6ba 25%, #b38728 50%, #fbf5b7 75%, #aa771c 100%);
            border-radius: 1.5rem;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.6), 0 0 35px rgba(191, 149, 63, 0.2);
        }
        .dashboard-card {
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
        /* Badge Role Mewah */
        .badge-role {
            background: linear-gradient(135deg, #bf953f 0%, #aa771c 100%);
            color: #141414;
            font-weight: 700;
            padding: 0.4rem 1rem;
            border-radius: 50rem;
            font-size: 0.8rem;
            letter-spacing: 0.5px;
            box-shadow: 0 4px 10px rgba(191, 149, 63, 0.3);
        }
        /* List Menu Utama */
        .menu-list .list-group-item {
            background: rgba(255, 255, 255, 0.03);
            border: 1px solid rgba(191, 149, 63, 0.2);
            color: #f8f9fa;
            border-radius: 0.75rem !important;
            margin-bottom: 0.75rem;
            padding: 0.9rem 1.25rem;
            font-weight: 500;
            transition: all 0.3s ease;
        }
        .menu-list .list-group-item:hover {
            background: rgba(191, 149, 63, 0.1);
            border-color: #fcf6ba;
            color: #fcf6ba;
            transform: translateX(5px);
        }
        .menu-list .list-group-item i {
            color: #bf953f;
            margin-right: 0.75rem;
            font-size: 1.1rem;
        }
        /* Tombol Logout Spesial */
        .btn-logout {
            background: linear-gradient(135deg, #dc3545 0%, #a71d2a 100%);
            border: none;
            color: #fff;
            border-radius: 0.75rem;
            padding: 0.9rem 1.25rem;
            font-weight: 600;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(220, 53, 69, 0.3);
        }
        .btn-logout:hover {
            background: linear-gradient(135deg, #e4606d 0%, #dc3545 100%);
            color: #fff;
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(220, 53, 69, 0.5);
        }
        /* Styling Dekorasi Naga Kecil */
        .dragon-badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 45px;
            height: 45px;
            background: linear-gradient(135deg, rgba(191,149,63,0.2) 0%, rgba(170,119,28,0.4) 100%);
            border: 1px solid rgba(252,246,186,0.4);
            border-radius: 50%;
            color: #fcf6ba;
            font-size: 1.3rem;
            box-shadow: 0 0 15px rgba(191,149,63,0.3);
        }
    </style>
</head>
<body>

    <div class="container px-3 d-flex justify-content-center">
        <div class="gold-card-wrapper">
            <div class="dashboard-card">
                
                <!-- Header dengan Judul dan Icon Naga Kecil di Sebelah Kanan -->
                <div class="d-flex justify-content-between align-items-center mb-4 pb-3 border-bottom border-secondary border-opacity-25">
                    <div>
                        <h3 class="gold-title mb-1">Aplikasi Peminjaman Alat</h3>
                        <p class="text-secondary small mb-0">Selamat datang, <span class="text-warning fw-semibold"><?= htmlspecialchars($user['nama']); ?></span></p>
                    </div>
                    <div class="text-end">
                        <!-- Lambang / Ikon Naga Kecil -->
                        <div class="dragon-badge" title="Thevorix Dragon Emblem">
                            <i class="bi bi-fire"></i>
                        </div>
                        <div class="mt-1">
                            <span class="badge-role text-uppercase"><?= htmlspecialchars($user['role']); ?></span>
                        </div>
                    </div>
                </div>

                <h6 class="text-uppercase text-secondary fw-bold small mb-3 tracking-wider" style="letter-spacing: 1px;">Menu Utama</h6>

                <!-- Daftar Menu Berdasarkan Role -->
                <div class="list-group menu-list mb-3">
                    <?php if ($role === 'admin'): ?>
                        <a href="data_user.php" class="list-group-item list-group-item-action d-flex align-items-center">
                            <i class="bi bi-people-fill"></i> CRUD User
                        </a>
                        <a href="data_alat.php" class="list-group-item list-group-item-action d-flex align-items-center">
                            <i class="bi bi-tools"></i> CRUD Alat
                        </a>
                        <a href="kategori.php" class="list-group-item list-group-item-action d-flex align-items-center">
                            <i class="bi bi-tags-fill"></i> CRUD Kategori
                        </a>
                        <a href="peminjaman.php" class="list-group-item list-group-item-action d-flex align-items-center">
                            <i class="bi bi-journal-check"></i> CRUD Data Peminjaman
                        </a>
                        <a href="pengembalian.php" class="list-group-item list-group-item-action d-flex align-items-center">
                            <i class="bi bi-arrow-return-left"></i> CRUD Pengembalian
                        </a>
                        <a href="log_aktifitas.php" class="list-group-item list-group-item-action d-flex align-items-center">
                            <i class="bi bi-clock-history"></i> Log Aktifitas
                        </a>
                    <?php elseif ($role === 'petugas'): ?>
                        <!-- Menu Khusus Petugas Sesuai Tabel Spesifikasi Tugas -->
                        <a href="peminjaman.php" class="list-group-item list-group-item-action d-flex align-items-center">
                            <i class="bi bi-check2-square"></i> Menyetujui Peminjaman
                        </a>
                        <a href="pengembalian.php" class="list-group-item list-group-item-action d-flex align-items-center">
                            <i class="bi bi-eye-fill"></i> Memantau Pengembalian
                        </a>
                        <a href="laporan.php" class="list-group-item list-group-item-action d-flex align-items-center">
                            <i class="bi bi-printer-fill"></i> Mencetak Laporan
                        </a>
                    <?php else: ?>
                        <!-- Menu khusus Peminjam -->
                        <a href="data_alat.php" class="list-group-item list-group-item-action d-flex align-items-center">
                            <i class="bi bi-search"></i> Melihat Daftar Alat
                        </a>
                        <a href="peminjaman.php" class="list-group-item list-group-item-action d-flex align-items-center">
                            <i class="bi bi-plus-circle-fill"></i> Mengajukan Peminjaman
                        </a>
                        <a href="pengembalian.php" class="list-group-item list-group-item-action d-flex align-items-center">
                            <i class="bi bi-box-arrow-in-down-left"></i> Mengembalikan Alat
                        </a>
                    <?php endif; ?>
                </div>

                <!-- Tombol Logout -->
                <a href="logout.php" class="btn btn-logout w-100 d-flex align-items-center justify-content-center gap-2">
                    <i class="bi bi-box-arrow-right fs-5"></i> Logout
                </a>

            </div>
        </div>
    </div>

    <!-- Bootstrap JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
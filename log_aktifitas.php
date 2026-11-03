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

// Ambil data log aktivitas dengan penanganan fleksibel terhadap struktur database
try {
    $query = "SELECT p.*, 
                     COALESCE(u.nama, u.username, 'User') as nama_peminjam,
                     COALESCE(a.nama, 'Alat') as nama_alat
              FROM peminjaman p
              LEFT JOIN users u ON p.user_id = u.id
              LEFT JOIN alat a ON p.alat_id = a.id
              ORDER BY p.id DESC";
    $stmt = $pdo->query($query);
    $log_list = $stmt->fetchAll();
} catch (PDOException $e) {
    try {
        $stmt = $pdo->query("SELECT * FROM peminjaman ORDER BY id DESC");
        $log_list = $stmt->fetchAll();
    } catch (PDOException $ex) {
        $log_list = [];
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Log Aktivitas Peminjaman Alat - Peminjaman Alat</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background: linear-gradient(135deg, #0d0d0d 0%, #1a1a1a 100%);
            min-height: 100vh;
            color: #f8f9fa;
            margin: 0;
            padding: 2rem 0;
        }
        /* Kerangka Emas Super Mewah & Berkilau */
        .gold-card-wrapper {
            width: 100%;
            padding: 3px;
            background: linear-gradient(135deg, #d4af37 0%, #fff6bf 25%, #aa771c 50%, #fef19b 75%, #8f6516 100%);
            border-radius: 1.5rem;
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.7), 0 0 40px rgba(212, 175, 55, 0.25);
            margin-bottom: 2rem;
        }
        .content-card {
            background: #121212;
            padding: 2.5rem;
            border-radius: calc(1.5rem - 3px);
            color: #f8f9fa;
        }
        .gold-title {
            background: linear-gradient(135deg, #fef19b 0%, #d4af37 50%, #aa771c 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            font-weight: 700;
        }
        /* Form Control Emas Mewah dengan Teks Terang */
        .form-control {
            background: rgba(255, 255, 255, 0.08);
            border: 1px solid rgba(212, 175, 55, 0.5);
            color: #ffffff !important;
            border-radius: 0.75rem;
            padding: 0.75rem 1rem;
        }
        .form-control:focus {
            background: rgba(255, 255, 255, 0.12);
            border-color: #fff6bf;
            color: #ffffff !important;
            box-shadow: 0 0 15px rgba(255, 246, 191, 0.3);
        }
        .form-control::placeholder {
            color: rgba(255, 255, 255, 0.6);
        }
        /* Styling khusus ikon kalender bawaan browser agar terlihat terang */
        input[type="date"]::-webkit-calendar-picker-indicator {
            filter: invert(1) brightness(1.5);
            cursor: pointer;
        }
        /* Tombol Emas Elegan */
        .btn-gold {
            background: linear-gradient(135deg, #d4af37 0%, #aa771c 100%);
            border: none;
            color: #121212;
            font-weight: 700;
            border-radius: 0.75rem;
            padding: 0.75rem 1.5rem;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(212, 175, 55, 0.3);
        }
        .btn-gold:hover {
            background: linear-gradient(135deg, #fff6bf 0%, #d4af37 100%);
            color: #000;
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(212, 175, 55, 0.5);
        }
        /* Tombol Kembali / Outline Emas */
        .btn-outline-gold {
            background: rgba(212, 175, 55, 0.08);
            border: 1px solid rgba(212, 175, 55, 0.5);
            color: #fff6bf;
            border-radius: 50rem;
            padding: 0.5rem 1.25rem;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        .btn-outline-gold:hover {
            background: linear-gradient(135deg, #d4af37 0%, #aa771c 100%);
            color: #121212;
            border-color: #fff6bf;
        }
        /* Tabel Custom Emas */
        .table-dark-gold {
            background: transparent;
            color: #f8f9fa;
        }
        .table-dark-gold th {
            background: rgba(212, 175, 55, 0.15);
            color: #fff6bf;
            border-bottom: 2px solid rgba(212, 175, 55, 0.35);
            text-transform: uppercase;
            font-size: 0.85rem;
            letter-spacing: 0.5px;
            padding: 1rem;
        }
        .table-dark-gold td {
            background: rgba(255, 255, 255, 0.015);
            color: #e0e0e0;
            border-bottom: 1px solid rgba(212, 175, 55, 0.1);
            padding: 1rem;
            vertical-align: middle;
        }
        .table-dark-gold tbody tr:hover td {
            background: rgba(212, 175, 55, 0.06);
            color: #fff;
        }
        /* Logo Naga Eksklusif */
        .dragon-badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 45px;
            height: 45px;
            background: linear-gradient(135deg, rgba(212,175,55,0.2) 0%, rgba(170,119,28,0.4) 100%);
            border: 1px solid rgba(255,246,191,0.4);
            border-radius: 50%;
            color: #fff6bf;
            font-size: 1.3rem;
            box-shadow: 0 0 20px rgba(212,175,55,0.35);
        }
    </style>
</head>
<body>

    <div class="container my-4">
        <!-- Header & Logo Naga di Sebelah Kanan -->
        <div class="d-flex justify-content-between align-items-center mb-4 pb-3 border-bottom border-secondary border-opacity-25">
            <div>
                <h2 class="gold-title mb-1"><i class="bi bi-clock-history me-2"></i> Log Aktivitas Peminjaman Alat</h2>
                <p class="text-secondary small mb-0">Monitoring aktivitas dan riwayat sistem (Admin: Pantau | Petugas: Kelola & Cek)</p>
            </div>
            <div class="d-flex align-items-center gap-3">
                <div class="dragon-badge" title="Thevorix Dragon Emblem">
                    <i class="bi bi-fire"></i>
                </div>
                <a href="dashboard.php" class="btn btn-outline-gold d-flex align-items-center gap-2">
                    <i class="bi bi-arrow-left"></i> Dashboard
                </a>
            </div>
        </div>

        <!-- Filter Form Section -->
        <div class="gold-card-wrapper">
            <div class="content-card py-3">
                <form action="" method="GET">
                    <div class="row g-3 align-items-end">
                        <div class="col-md-4">
                            <label class="form-label text-secondary small fw-semibold">Tanggal Mulai</label>
                            <input type="date" name="tgl_mulai" class="form-control">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label text-secondary small fw-semibold">Tanggal Selesai</label>
                            <input type="date" name="tgl_selesai" class="form-control">
                        </div>
                        <div class="col-md-4">
                            <button type="submit" class="btn btn-gold w-100">
                                <i class="bi bi-funnel-fill me-1"></i> Filter Data
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Rekapitulasi Log Aktivitas (Tabel) -->
        <div class="gold-card-wrapper">
            <div class="content-card">
                <h5 class="gold-title mb-4"><i class="bi bi-table me-2"></i> Rekapitulasi Log Aktivitas Peminjaman</h5>

                <div class="table-responsive">
                    <table class="table table-dark-gold align-middle mb-0">
                        <thead>
                            <tr>
                                <th class="ps-3">#ID</th>
                                <th>Nama Alat</th>
                                <th>Peminjam</th>
                                <th class="text-center">Jumlah</th>
                                <th>Tgl Pinjam</th>
                                <th>Tgl Kembali</th>
                                <th class="text-center">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($log_list)): ?>
                                <tr>
                                    <td colspan="7" class="text-center py-4 text-secondary">Belum ada data log aktivitas.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($log_list as $row): ?>
                                <tr>
                                    <td class="ps-3 fw-bold text-warning">#<?= $row['id'] ?? 1; ?></td>
                                    <td class="fw-semibold"><?= htmlspecialchars($row['nama_alat'] ?? $row['alat'] ?? 'Alat'); ?></td>
                                    <td><?= htmlspecialchars($row['nama_peminjam'] ?? $row['peminjam'] ?? 'User'); ?></td>
                                    <td class="text-center">
                                        <span class="badge bg-info bg-opacity-10 text-info fw-bold px-3 py-1"><?= $row['jumlah'] ?? $row['stok'] ?? 1; ?> Unit</span>
                                    </td>
                                    <td><?= htmlspecialchars($row['tanggal_pinjam'] ?? $row['tgl_pinjam'] ?? '-'); ?></td>
                                    <td><?= htmlspecialchars($row['tanggal_kembali'] ?? $row['tgl_kembali'] ?? '-'); ?></td>
                                    <td class="text-center">
                                        <?php 
                                            $status = $row['status'] ?? 'Pending';
                                            $badge_bg = 'bg-secondary';
                                            if (strtolower($status) === 'dikembalikan' || strtolower($status) === 'returned') {
                                                $badge_bg = 'bg-success';
                                            } elseif (strtolower($status) === 'disetujui' || strtolower($status) === 'approved') {
                                                $badge_bg = 'bg-primary';
                                            } elseif (strtolower($status) === 'pending') {
                                                $badge_bg = 'bg-warning text-dark';
                                            } else {
                                                $badge_bg = 'bg-danger';
                                            }
                                        ?>
                                        <span class="badge <?= $badge_bg; ?> px-3 py-1 rounded-pill fw-bold"><?= htmlspecialchars($status); ?></span>
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
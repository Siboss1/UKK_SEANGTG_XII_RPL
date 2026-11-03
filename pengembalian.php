<?php
session_start();
require_once 'config.php';

// Keamanan: Cek apakah pengguna sudah login
if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}

$role = strtolower($_SESSION['user']['role']);
$user_id = $_SESSION['user']['id'];

$pesan_sukses = "";
$pesan_error = "";

// PROSES PENGEMBALIAN HANYA OLEH PEMINJAM
if (isset($_POST['proses_kembali']) && $role === 'peminjam') {
    $id_peminjaman = $_POST['id_peminjaman'];

    // Ambil data peminjaman untuk mengecek alat dan jumlahnya
    $stmt_get = $pdo->prepare("SELECT * FROM peminjaman WHERE id = ?");
    $stmt_get->execute([$id_peminjaman]);
    $data_pinjam = $stmt_get->fetch();

    if ($data_pinjam) {
        $alat_id = $data_pinjam['alat_id'];
        $jumlah = $data_pinjam['jumlah'];

        // 1. Update status peminjaman menjadi 'Dikembalikan'
        $stmt_update = $pdo->prepare("UPDATE peminjaman SET status = 'Dikembalikan' WHERE id = ?");
        $stmt_update->execute([$id_peminjaman]);

        // 2. Kembalikan stok alat ke tabel alat
        $stmt_stok = $pdo->prepare("UPDATE alat SET stok = stok + ? WHERE id = ?");
        $stmt_stok->execute([$jumlah, $alat_id]);

        $pesan_sukses = "Alat berhasil dikembalikan dan stok telah diperbarui!";
    } else {
        $pesan_error = "Data peminjaman tidak ditemukan!";
    }
}

// Ambil data berdasarkan role pengguna
try {
    if ($role === 'peminjam') {
        // Peminjam hanya melihat alat yang sedang mereka pinjam untuk dikembalikan
        $query = "SELECT p.*, a.nama AS nama_alat 
                  FROM peminjaman p 
                  LEFT JOIN alat a ON p.alat_id = a.id 
                  WHERE p.user_id = ? AND (p.status = 'Disetujui' OR p.status = 'Pending') 
                  ORDER BY p.id DESC";
        $stmt = $pdo->prepare($query);
        $stmt->execute([$user_id]);
        $data_list = $stmt->fetchAll();
    } else {
        // Admin & Petugas melihat seluruh data pengembalian/peminjaman (Petugas murni memantau)
        $query = "SELECT p.*, a.nama AS nama_alat, u.nama AS nama_peminjam 
                  FROM peminjaman p 
                  LEFT JOIN alat a ON p.alat_id = a.id 
                  LEFT JOIN users u ON p.user_id = u.id 
                  ORDER BY p.id DESC";
        $data_list = $pdo->query($query)->fetchAll();
    }
} catch (PDOException $e) {
    $data_list = [];
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pengembalian Alat</title>
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
        /* Kerangka Emas Mewah */
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
        /* Tombol Emas */
        .btn-gold {
            background: linear-gradient(135deg, #bf953f 0%, #aa771c 100%);
            border: none;
            color: #141414;
            font-weight: 700;
            border-radius: 0.75rem;
            padding: 0.5rem 1.25rem;
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
        /* Tabel Custom Emas */
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
        /* Logo Naga Eksklusif */
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
    <div class="container my-4">
        <!-- Header Section -->
        <div class="d-flex justify-content-between align-items-center mb-4 pb-3 border-bottom border-secondary border-opacity-25">
            <div>
                <h2 class="gold-title mb-1"><i class="bi bi-box-arrow-in-down-left me-2"></i> Pengembalian Alat</h2>
                <p class="text-secondary small mb-0">
                    <?php if ($role === 'admin'): ?>
                        Panel Monitoring (Admin: Hanya Melihat & Memantau)
                    <?php elseif ($role === 'petugas'): ?>
                        Panel Pemantauan (Petugas: Memantau Pengembalian Alat)
                    <?php else: ?>
                        Daftar alat yang sedang Anda pinjam untuk dikembalikan
                    <?php endif; ?>
                </p>
            </div>
            <div class="d-flex align-items-center gap-3">
                <div class="dragon-badge" title="Thevorix Dragon Emblem">
                    <i class="bi bi-fire"></i>
                </div>
                <a href="dashboard.php" class="btn btn-outline-gold d-flex align-items-center gap-2">
                    <i class="bi bi-arrow-left"></i> Kembali ke Dashboard
                </a>
            </div>
        </div>

        <!-- Notifikasi -->
        <?php if (!empty($pesan_sukses)): ?>
            <div class="alert alert-success bg-success bg-opacity-25 border-success text-success text-center py-2 rounded-3 mb-4 shadow-sm" role="alert">
                <i class="bi bi-check-circle-fill me-2"></i> <?= $pesan_sukses; ?>
            </div>
        <?php endif; ?>
        <?php if (!empty($pesan_error)): ?>
            <div class="alert alert-danger bg-danger bg-opacity-25 border-danger text-danger text-center py-2 rounded-3 mb-4 shadow-sm" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-2"></i> <?= $pesan_error; ?>
            </div>
        <?php endif; ?>

        <!-- Tabel Data Pengembalian dalam Gold Card -->
        <div class="gold-card-wrapper">
            <div class="content-card">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h5 class="gold-title mb-0">
                        <i class="bi bi-table me-2"></i> 
                        <?= ($role === 'peminjam') ? 'Alat yang Masih Dipinjam' : 'Daftar Seluruh Pengembalian Alat'; ?>
                    </h5>
                    <span class="badge bg-warning bg-opacity-10 text-warning border border-warning border-opacity-25 px-3 py-2 fw-bold text-uppercase" style="font-size: 0.75rem;">Role: <?= htmlspecialchars($role); ?></span>
                </div>
                
                <div class="table-responsive">
                    <table class="table table-dark-gold align-middle mb-0">
                        <thead>
                            <tr>
                                <th class="py-3 ps-3">#ID</th>
                                <th class="py-3">Nama Alat</th>
                                <?php if ($role !== 'peminjam'): ?>
                                    <th class="py-3">Peminjam</th>
                                <?php endif; ?>
                                <th class="py-3 text-center">Jumlah</th>
                                <th class="py-3">Tgl Pinjam</th>
                                <th class="py-3">Rencana Kembali</th>
                                <th class="py-3 text-center">Status</th>
                                <?php if ($role !== 'admin'): ?>
                                    <th class="py-3 text-center"><?= ($role === 'petugas') ? 'Keterangan' : 'Aksi'; ?></th>
                                <?php endif; ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($data_list)): ?>
                                <tr>
                                    <td colspan="<?= ($role !== 'peminjam' && $role !== 'admin') ? 8 : 7; ?>" class="text-center py-5 text-secondary">
                                        <i class="bi bi-inbox fs-1 d-block mb-2 text-secondary opacity-50"></i> Tidak ada data pengembalian saat ini.
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($data_list as $row): ?>
                                <tr>
                                    <td class="fw-bold text-warning ps-3">#<?= $row['id']; ?></td>
                                    <td class="fw-semibold text-white"><?= htmlspecialchars($row['nama_alat'] ?? '-'); ?></td>
                                    <?php if ($role !== 'peminjam'): ?>
                                        <td>
                                            <span class="badge bg-secondary bg-opacity-25 text-light border border-secondary px-2 py-1">
                                                <i class="bi bi-person-fill text-warning me-1"></i> <?= htmlspecialchars($row['nama_peminjam'] ?? '-'); ?>
                                            </span>
                                        </td>
                                    <?php endif; ?>
                                    <td class="text-center"><span class="badge bg-info bg-opacity-10 text-info fw-bold px-3 py-1"><?= $row['jumlah'] ?? 1; ?> Unit</span></td>
                                    <td class="small text-secondary"><?= $row['tanggal_pinjam'] ?? '-'; ?></td>
                                    <td class="small text-warning fw-semibold"><?= $row['tanggal_kembali'] ?? '-'; ?></td>
                                    <td class="text-center">
                                        <?php 
                                            $status = $row['status'] ?? 'Pending';
                                            $badge_bg = 'bg-secondary';
                                            if ($status === 'Dikembalikan') $badge_bg = 'bg-success';
                                            elseif ($status === 'Disetujui') $badge_bg = 'bg-primary';
                                            elseif ($status === 'Pending') $badge_bg = 'bg-warning text-dark';
                                            elseif ($status === 'Tidak Disetujui') $badge_bg = 'bg-danger';
                                        ?>
                                        <span class="badge <?= $badge_bg; ?> px-3 py-1 rounded-pill fw-bold shadow-sm"><?= htmlspecialchars($status); ?></span>
                                    </td>
                                    
                                    <?php if ($role !== 'admin'): ?>
                                        <td class="text-center">
                                            <?php if ($role === 'peminjam'): ?>
                                                <?php if ($status === 'Disetujui'): ?>
                                                    <form action="" method="POST" class="d-inline">
                                                        <input type="hidden" name="id_peminjaman" value="<?= $row['id']; ?>">
                                                        <button type="submit" name="proses_kembali" class="btn btn-gold btn-sm px-3 shadow-sm rounded-pill fw-semibold" onclick="return confirm('Yakin ingin mengembalikan alat ini?')">
                                                            <i class="bi bi-box-arrow-in-down-left me-1"></i> Kembalikan
                                                        </button>
                                                    </form>
                                                <?php else: ?>
                                                    <span class="badge bg-warning text-dark px-2 py-1">Menunggu Disetujui</span>
                                                <?php endif; ?>
                                            <?php elseif ($role === 'petugas'): ?>
                                                <!-- Petugas murni memantau tanpa tombol aksi -->
                                                <span class="text-secondary small fst-italic"><i class="bi bi-eye me-1"></i> Dipantau</span>
                                            <?php endif; ?>
                                        </td>
                                    <?php endif; ?>
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
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
$pesan_sukses = '';
$pesan_error = '';

// 1. PROSES AJUKAN PEMINJAMAN (KHUSUS PEMINJAM)
if (isset($_POST['ajukan_pinjam']) && $role === 'peminjam') {
    $alat_id = $_POST['alat_id'];
    $jumlah = $_POST['jumlah'];
    $tgl_pinjam = $_POST['tgl_pinjam'];
    $tgl_kembali = $_POST['tgl_kembali'];
    $user_id = $user['id'];
    $status_default = 'Pending';

    if (!empty($alat_id) && !empty($jumlah) && !empty($tgl_pinjam) && !empty($tgl_kembali)) {
        try {
            $stmt = $pdo->prepare("INSERT INTO peminjaman (user_id, alat_id, jumlah, tanggal_pinjam, tanggal_kembali, status) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$user_id, $alat_id, $jumlah, $tgl_pinjam, $tgl_kembali, $status_default]);
            $pesan_sukses = "Pengajuan peminjaman berhasil dikirim dan menunggu persetujuan!";
        } catch (PDOException $e) {
            $pesan_error = "Gagal mengajukan peminjaman: " . $e->getMessage();
        }
    } else {
        $pesan_error = "Semua kolom form peminjaman wajib diisi!";
    }
}

// 2. Proses aksi persetujuan (Bisa dilakukan oleh Admin atau Petugas)
if (isset($_GET['aksi']) && isset($_GET['id']) && in_array($role, ['admin', 'petugas'])) {
    $id_peminjaman = $_GET['id'];
    $aksi = $_GET['aksi'];
    
    $status_baru = ($aksi === 'setuju') ? 'Disetujui' : 'Ditolak';

    try {
        $stmt = $pdo->prepare("UPDATE peminjaman SET status = ? WHERE id = ?");
        $stmt->execute([$status_baru, $id_peminjaman]);
        $pesan_sukses = "Status peminjaman berhasil diperbarui menjadi $status_baru!";
    } catch (PDOException $e) {
        $pesan_error = "Gagal memperbarui status: " . $e->getMessage();
    }
}

// Ambil data alat untuk pilihan di form peminjaman
try {
    $alat_stmt = $pdo->query("SELECT * FROM alat ORDER BY nama ASC");
    $daftar_alat = $alat_stmt->fetchAll();
} catch (PDOException $e) {
    $daftar_alat = [];
}

// Ambil data peminjaman (Peminjam melihat miliknya sendiri, Admin & Petugas melihat semua data)
try {
    if ($role === 'peminjam') {
        $query = "SELECT p.*, 
                         COALESCE(u.nama, u.username, 'User') as nama_peminjam,
                         COALESCE(a.nama, 'Alat') as nama_alat
                  FROM peminjaman p
                  LEFT JOIN users u ON p.user_id = u.id
                  LEFT JOIN alat a ON p.alat_id = a.id
                  WHERE p.user_id = ?
                  ORDER BY p.id DESC";
        $stmt = $pdo->prepare($query);
        $stmt->execute([$user['id']]);
        $peminjaman_list = $stmt->fetchAll();
    } else {
        $query = "SELECT p.*, 
                         COALESCE(u.nama, u.username, 'User') as nama_peminjam,
                         COALESCE(a.nama, 'Alat') as nama_alat
                  FROM peminjaman p
                  LEFT JOIN users u ON p.user_id = u.id
                  LEFT JOIN alat a ON p.alat_id = a.id
                  ORDER BY p.id DESC";
        $stmt = $pdo->query($query);
        $peminjaman_list = $stmt->fetchAll();
    }
} catch (PDOException $e) {
    try {
        $stmt = $pdo->query("SELECT * FROM peminjaman ORDER BY id DESC");
        $peminjaman_list = $stmt->fetchAll();
    } catch (PDOException $ex) {
        $peminjaman_list = [];
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Peminjaman Alat - Peminjaman Alat</title>
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
        .form-control, .form-select {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(191, 149, 63, 0.3);
            color: #ffffff !important;
            border-radius: 0.75rem;
            padding: 0.75rem 1rem;
        }
        .form-control:focus, .form-select:focus {
            background: rgba(255, 255, 255, 0.08);
            border-color: #fcf6ba;
            color: #ffffff !important;
            box-shadow: 0 0 10px rgba(252, 246, 186, 0.2);
        }
        .form-control::placeholder {
            color: rgba(255, 255, 255, 0.4);
        }
        .form-select option {
            background: #141414;
            color: #fff;
        }
        input[type="date"]::-webkit-calendar-picker-indicator {
            filter: invert(1) brightness(1.5);
            cursor: pointer;
        }
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
        <!-- Header & Logo Naga di Sebelah Kanan -->
        <div class="d-flex justify-content-between align-items-center mb-4 pb-3 border-bottom border-secondary border-opacity-25">
            <div>
                <h2 class="gold-title mb-1"><i class="bi bi-journal-check me-2"></i> Manajemen Peminjaman Alat</h2>
                <p class="text-secondary small mb-0">Panel Sistem (Role: <span class="text-warning text-uppercase fw-semibold"><?= htmlspecialchars($user['role']); ?></span>)</p>
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
            <div class="alert alert-success bg-success bg-opacity-25 border-success text-success text-center py-2 rounded-3 mb-4">
                <i class="bi bi-check-circle-fill me-1"></i> <?= $pesan_sukses; ?>
            </div>
        <?php endif; ?>
        <?php if (!empty($pesan_error)): ?>
            <div class="alert alert-danger bg-danger bg-opacity-25 border-danger text-danger text-center py-2 rounded-3 mb-4">
                <i class="bi bi-exclamation-triangle-fill me-1"></i> <?= $pesan_error; ?>
            </div>
        <?php endif; ?>

        <!-- FORM AJUKAN PEMINJAMAN: HANYA MUNCUL UNTUK PEMINJAM -->
        <?php if ($role === 'peminjam'): ?>
        <div class="gold-card-wrapper">
            <div class="content-card">
                <h5 class="gold-title mb-4"><i class="bi bi-plus-circle-fill me-2"></i> Form Pengajuan Peminjaman Alat</h5>
                <form action="" method="POST">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label text-secondary small fw-semibold">Pilih Alat</label>
                            <select name="alat_id" class="form-select" required>
                                <option value="" disabled selected>-- Pilih Alat Inventaris --</option>
                                <?php foreach ($daftar_alat as $alat): ?>
                                    <option value="<?= $alat['id']; ?>"><?= htmlspecialchars($alat['nama']); ?> (Stok: <?= $alat['stok'] ?? 0; ?>)</option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-secondary small fw-semibold">Jumlah Unit</label>
                            <input type="number" name="jumlah" class="form-control" placeholder="Jumlah unit..." min="1" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-secondary small fw-semibold">Tanggal Pinjam</label>
                            <input type="date" name="tgl_pinjam" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-secondary small fw-semibold">Tanggal Rencana Kembali</label>
                            <input type="date" name="tgl_kembali" class="form-control" required>
                        </div>
                        <div class="col-12 mt-4">
                            <button type="submit" name="ajukan_pinjam" class="btn btn-gold w-100">
                                <i class="bi bi-send-fill me-1"></i> Ajukan Peminjaman Sekarang
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        <?php endif; ?>

        <!-- Daftar Peminjaman Alat (Tabel) -->
        <div class="gold-card-wrapper">
            <div class="content-card">
                <h5 class="gold-title mb-4"><i class="bi bi-table me-2"></i> <?= ($role === 'peminjam') ? 'Riwayat Pengajuan Peminjaman Saya' : 'Daftar Peminjaman Alat (Panel ' . ucfirst($role) . ')'; ?></h5>

                <div class="table-responsive">
                    <table class="table table-dark-gold align-middle mb-0">
                        <thead>
                            <tr>
                                <th class="ps-3">#ID</th>
                                <th>Nama Alat</th>
                                <?php if ($role !== 'peminjam'): ?>
                                    <th>Peminjam</th>
                                <?php endif; ?>
                                <th class="text-center">Jumlah</th>
                                <th>Tgl Pinjam</th>
                                <th>Tgl Kembali</th>
                                <th class="text-center">Status</th>
                                <th class="text-center">Aksi Persetujuan</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($peminjaman_list)): ?>
                                <tr>
                                    <td colspan="<?= ($role !== 'peminjam') ? 8 : 7; ?>" class="text-center py-4 text-secondary">Belum ada data peminjaman alat.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($peminjaman_list as $row): ?>
                                <tr>
                                    <td class="ps-3 fw-bold text-warning">#<?= $row['id'] ?? 1; ?></td>
                                    <td class="fw-semibold"><?= htmlspecialchars($row['nama_alat'] ?? 'Alat'); ?></td>
                                    <?php if ($role !== 'peminjam'): ?>
                                        <td><?= htmlspecialchars($row['nama_peminjam'] ?? 'User'); ?></td>
                                    <?php endif; ?>
                                    <td class="text-center">
                                        <span class="badge bg-info bg-opacity-10 text-info fw-bold px-3 py-1"><?= $row['jumlah'] ?? 1; ?> Unit</span>
                                    </td>
                                    <td><?= htmlspecialchars($row['tanggal_pinjam'] ?? $row['tgl_pinjam'] ?? '-'); ?></td>
                                    <td><?= htmlspecialchars($row['tanggal_kembali'] ?? $row['tgl_kembali'] ?? '-'); ?></td>
                                    <td class="text-center">
                                        <?php 
                                            $status = $row['status'] ?? 'Pending';
                                            $badge_bg = 'bg-secondary';
                                            if (strtolower($status) === 'disetujui' || strtolower($status) === 'approved') {
                                                $badge_bg = 'bg-success';
                                            } elseif (strtolower($status) === 'dikembalikan' || strtolower($status) === 'returned') {
                                                $badge_bg = 'bg-success';
                                            } elseif (strtolower($status) === 'pending') {
                                                $badge_bg = 'bg-warning text-dark';
                                            } else {
                                                $badge_bg = 'bg-danger';
                                            }
                                        ?>
                                        <span class="badge <?= $badge_bg; ?> px-3 py-1 rounded-pill fw-bold"><?= htmlspecialchars($status); ?></span>
                                    </td>
                                    <td class="text-center">
                                        <?php if (in_array($role, ['admin', 'petugas'])): ?>
                                            <!-- Tombol Aksi khusus Admin / Petugas -->
                                            <?php if (strtolower($status) === 'pending'): ?>
                                                <a href="peminjaman.php?aksi=setuju&id=<?= $row['id']; ?>" class="btn btn-gold btn-sm px-3 rounded-pill fw-semibold shadow-sm" onclick="return confirm('Setujui peminjaman ini?')">
                                                    <i class="bi bi-check-lg me-1"></i> Setujui
                                                </a>
                                            <?php else: ?>
                                                <span class="text-success fw-semibold small"><i class="bi bi-check-circle-fill"></i> Selesai</span>
                                            <?php endif; ?>
                                        <?php else: ?>
                                            <span class="text-secondary small">Menunggu Proses</span>
                                        <?php endif; ?>
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
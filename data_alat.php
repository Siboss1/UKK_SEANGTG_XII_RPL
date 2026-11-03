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

// Proses Tambah Alat (Hanya untuk Admin, Petugas tidak boleh menambah)
if (isset($_POST['simpan_alat']) && $role === 'admin') {
    $nama = trim($_POST['nama']);
    $kondisi = trim($_POST['kondisi']);
    $stok = trim($_POST['stok']);

    if (!empty($nama) && !empty($kondisi) && $stok !== '') {
        try {
            $stmt = $pdo->prepare("INSERT INTO alat (nama, kondisi, stok) VALUES (?, ?, ?)");
            $stmt->execute([$nama, $kondisi, $stok]);
            $pesan_sukses = "Alat baru berhasil ditambahkan!";
        } catch (PDOException $e) {
            $pesan_error = "Gagal menambah alat: " . $e->getMessage();
        }
    } else {
        $pesan_error = "Semua kolom wajib diisi!";
    }
}

// Proses Hapus Alat (Hanya untuk Admin)
if (isset($_GET['hapus']) && $role === 'admin') {
    $id_alat = $_GET['hapus'];
    try {
        $stmt = $pdo->prepare("DELETE FROM alat WHERE id = ?");
        $stmt->execute([$id_alat]);
        $pesan_sukses = "Alat berhasil dihapus!";
    } catch (PDOException $e) {
        $pesan_error = "Gagal menghapus alat (kemungkinan masih berelasi dengan data peminjaman).";
    }
}

// Ambil data alat dari database dengan penanganan fleksibel
try {
    $stmt = $pdo->query("SELECT * FROM alat ORDER BY id DESC");
    $alat_list = $stmt->fetchAll();
} catch (PDOException $e) {
    $alat_list = [];
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar & Kelola Alat - Peminjaman Alat</title>
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
        /* Form Control Emas */
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
        /* Tombol Outline Emas */
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
        <!-- Header & Logo Naga di Sebelah Kanan -->
        <div class="d-flex justify-content-between align-items-center mb-4 pb-3 border-bottom border-secondary border-opacity-25">
            <div>
                <h2 class="gold-title mb-1"><i class="bi bi-tools me-2"></i> Daftar & Kelola Alat</h2>
                <p class="text-secondary small mb-0">Lihat daftar alat dan kelola inventaris dengan mudah (Role: <span class="text-warning text-uppercase fw-semibold"><?= htmlspecialchars($user['role']); ?></span>)</p>
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

        <!-- FORM TAMBAH ALAT: HANYA MUNCUL UNTUK ADMIN -->
        <?php if ($role === 'admin'): ?>
        <div class="gold-card-wrapper">
            <div class="content-card">
                <h5 class="gold-title mb-4"><i class="bi bi-plus-circle-fill me-2"></i> Tambah / Input Alat</h5>
                <form action="" method="POST">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label text-secondary small fw-semibold">Nama Alat</label>
                            <input type="text" name="nama" class="form-control" placeholder="Contoh: Kabel LAN" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label text-secondary small fw-semibold">Kondisi</label>
                            <select name="kondisi" class="form-select" required>
                                <option value="" disabled selected>Pilih Kondisi...</option>
                                <option value="Baik">Baik</option>
                                <option value="Rusak Ringan">Rusak Ringan</option>
                                <option value="Rusak Berat">Rusak Berat</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label text-secondary small fw-semibold">Stok</label>
                            <input type="number" name="stok" class="form-control" placeholder="Jumlah stok..." min="0" required>
                        </div>
                        <div class="col-12 mt-4">
                            <button type="submit" name="simpan_alat" class="btn btn-gold w-100">
                                <i class="bi bi-save-fill me-1"></i> Simpan Alat
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        <?php endif; ?>

        <!-- TABEL DAFTAR INVENTARIS ALAT -->
        <div class="gold-card-wrapper">
            <div class="content-card">
                <h5 class="gold-title mb-4"><i class="bi bi-table me-2"></i> Daftar Inventaris Alat</h5>

                <div class="table-responsive">
                    <table class="table table-dark-gold align-middle mb-0">
                        <thead>
                            <tr>
                                <th class="ps-3">#ID</th>
                                <th>Nama Alat</th>
                                <th>Kondisi</th>
                                <th class="text-center">Stok</th>
                                <?php if ($role === 'admin'): ?>
                                    <th class="text-center">Aksi</th>
                                <?php endif; ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($alat_list)): ?>
                                <tr>
                                    <td colspan="<?= ($role === 'admin') ? 5 : 4; ?>" class="text-center py-4 text-secondary">Belum ada data inventaris alat.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($alat_list as $row): ?>
                                <tr>
                                    <td class="ps-3 fw-bold text-warning">#<?= $row['id'] ?? 1; ?></td>
                                    <td class="fw-semibold"><?= htmlspecialchars($row['nama'] ?? 'Alat'); ?></td>
                                    <td>
                                        <?php 
                                            $kondisi = $row['kondisi'] ?? 'Baik';
                                            $badge_kondisi = 'bg-success';
                                            if (strtolower($kondisi) !== 'baik') {
                                                $badge_kondisi = 'bg-warning text-dark';
                                            }
                                        ?>
                                        <span class="badge <?= $badge_kondisi; ?> px-3 py-1 rounded-pill fw-bold"><?= htmlspecialchars($kondisi); ?></span>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-info bg-opacity-10 text-info fw-bold px-3 py-1"><?= $row['stok'] ?? 0; ?> Unit</span>
                                    </td>
                                    <?php if ($role === 'admin'): ?>
                                        <td class="text-center">
                                            <a href="data_alat.php?hapus=<?= $row['id']; ?>" class="btn btn-danger btn-sm px-3 rounded-pill fw-semibold" onclick="return confirm('Yakin ingin menghapus alat ini?')">
                                                <i class="bi bi-trash-fill me-1"></i> Hapus
                                            </a>
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
<?php
session_start();
require_once 'config.php';

// Validasi akses khusus admin
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    echo "<script>alert('Akses ditolak! Halaman ini khusus Admin.'); window.location='dashboard.php';</script>";
    exit;
}

// Proses Tambah Alat
if (isset($_POST['tambah_alat'])) {
    $nama     = trim($_POST['nama']);
    $kategori = trim($_POST['kategori']);
    $kondisi  = trim($_POST['kondisi']);
    $jumlah   = trim($_POST['jumlah']); 

    $stmt = $pdo->prepare("INSERT INTO alat (nama, kategori, kondisi, jumlah) VALUES (?, ?, ?, ?)");
    $stmt->execute([$nama, $kategori, $kondisi, $jumlah]);
    header("Location: data_alat.php");
    exit;
}

// Proses Hapus Alat
if (isset($_GET['hapus'])) {
    $id = $_GET['hapus'];
    $stmt = $pdo->prepare("DELETE FROM alat WHERE id = ?");
    $stmt->execute([$id]);
    header("Location: data_alat.php");
    exit;
}

// Ambil data alat dari database
$stmt = $pdo->query("SELECT * FROM alat");
$daftar_alat = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Data Alat</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- FontAwesome untuk ikon -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-light">

    <div class="container py-5">
        <!-- Header & Tombol Kembali -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="fw-bold text-dark mb-1"><i class="fa-solid fa-boxes-stacked text-primary me-2"></i>Kelola Data Alat</h2>
                <p class="text-muted mb-0">Tambah, pantau, dan kelola inventaris alat dengan mudah.</p>
            </div>
            <a href="dashboard.php" class="btn btn-outline-secondary"><i class="fa-solid fa-arrow-left me-1"></i> Kembali ke Dashboard</a>
        </div>

        <div class="row g-4">
            <!-- Form Tambah Alat -->
            <div class="col-lg-4">
                <div class="card shadow-sm border-0 rounded-4">
                    <div class="card-body p-4">
                        <h4 class="card-title fw-bold mb-3 fs-5 text-primary"><i class="fa-solid fa-circle-plus me-1"></i> Tambah Alat Baru</h4>
                        <form action="" method="POST">
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Nama Alat</label>
                                <input type="text" name="nama" class="form-control" placeholder="Contoh: Kabel LAN" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Kategori</label>
                                <input type="text" name="kategori" class="form-control" placeholder="Contoh: Jaringan" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Kondisi</label>
                                <select name="kondisi" class="form-select" required>
                                    <option value="" disabled selected>Pilih Kondisi</option>
                                    <option value="Baik">Baik</option>
                                    <option value="Perbaikan">Perbaikan</option>
                                    <option value="Rusak">Rusak</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Jumlah</label>
                                <input type="number" name="jumlah" class="form-control" placeholder="0" min="1" required>
                            </div>
                            <button type="submit" name="tambah_alat" class="btn btn-primary w-100 py-2 fw-semibold shadow-sm">
                                <i class="fa-solid fa-save me-1"></i> Simpan Alat
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Tabel Daftar Alat -->
            <div class="col-lg-8">
                <div class="card shadow-sm border-0 rounded-4">
                    <div class="card-body p-4">
                        <h4 class="card-title fw-bold mb-3 fs-5 text-dark"><i class="fa-solid fa-list me-1"></i> Daftar Inventaris Alat</h4>
                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead class="table-dark">
                                    <tr>
                                        <th class="py-3">#</th>
                                        <th class="py-3">Nama Alat</th>
                                        <th class="py-3">Kategori</th>
                                        <th class="py-3">Kondisi</th>
                                        <th class="py-3">Jumlah</th>
                                        <th class="py-3 text-center">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($daftar_alat)): ?>
                                        <tr>
                                            <td colspan="6" class="text-center py-4 text-muted">Belum ada data alat yang tersedia.</td>
                                        </tr>
                                    <?php else: ?>
                                        <?php $no = 1; foreach ($daftar_alat as $alat): ?>
                                        <tr>
                                            <td class="fw-semibold"><?= $no++; ?></td>
                                            <td class="fw-bold text-dark"><?= htmlspecialchars($alat['nama'] ?? ''); ?></td>
                                            <td><span class="badge bg-secondary bg-opacity-10 text-secondary px-2 py-1"><?= htmlspecialchars($alat['kategori'] ?? ''); ?></span></td>
                                            <td>
                                                <?php 
                                                    $kondisi = $alat['kondisi'] ?? '';
                                                    $badge_color = ($kondisi == 'Baik') ? 'success' : (($kondisi == 'Perbaikan') ? 'warning text-dark' : 'danger');
                                                ?>
                                                <span class="badge bg-<?= $badge_color ?>"><?= htmlspecialchars($kondisi); ?></span>
                                            </td>
                                            <td><?= htmlspecialchars($alat['jumlah'] ?? ''); ?></td>
                                            <td class="text-center">
                                                <a href="data_alat.php?hapus=<?= $alat['id']; ?>" class="btn btn-sm btn-outline-danger px-3" onclick="return confirm('Yakin ingin menghapus alat ini?')">
                                                    <i class="fa-solid fa-trash-can me-1"></i> Hapus
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
        </div>
    </div>

    <!-- Bootstrap 5 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
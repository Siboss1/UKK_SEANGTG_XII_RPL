<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}

$role = $_SESSION['user']['role'];

// Ambil data peminjaman aktif
$query = "SELECT p.*, a.nama AS nama_alat, u.nama AS nama_peminjam 
          FROM peminjaman p 
          LEFT JOIN alat a ON p.alat_id = a.id 
          LEFT JOIN users u ON p.user_id = u.id 
          ORDER BY p.id DESC";
$peminjaman = $pdo->query($query)->fetchAll();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Pengembalian Alat - Peminjaman Alat</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container my-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Memantau Pengembalian Alat</h2>
            <a href="dashboard.php" class="btn btn-secondary">Kembali ke Dashboard</a>
        </div>

        <div class="card shadow-sm">
            <div class="card-header bg-dark text-white">Daftar Peminjaman Aktif</div>
            <div class="card-body">
                <table class="table table-bordered table-striped align-middle">
                    <thead class="table-dark">
                        <tr>
                            <th>ID</th>
                            <th>Nama Alat</th>
                            <th>Peminjam</th>
                            <th>Jumlah</th>
                            <th>Tanggal Pinjam</th>
                            <th>Tanggal Harus Kembali</th>
                            <th>Status</th>
                            <?php if ($role === 'peminjam'): ?>
                                <th>Aksi</th>
                            <?php endif; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($peminjaman as $p): ?>
                        <tr>
                            <td><?= $p['id']; ?></td>
                            <td><?= htmlspecialchars($p['nama_alat'] ?? '-'); ?></td>
                            <td><?= htmlspecialchars($p['nama_peminjam'] ?? '-'); ?></td>
                            <td><?= $p['jumlah'] ?? 1; ?></td>
                            <td><?= $p['tanggal_pinjam'] ?? '-'; ?></td>
                            <td><?= $p['tanggal_kembali'] ?? '-'; ?></td>
                            <td><span class="badge bg-warning text-dark"><?= $p['status'] ?? 'Disetujui'; ?></span></td>
                            
                            <!-- Kolom Aksi hanya muncul untuk Peminjam (jika ingin melakukan pengembalian), hilang untuk Petugas/Admin -->
                            <?php if ($role === 'peminjam'): ?>
                            <td>
                                <a href="pengembalian.php?proses=<?= $p['id']; ?>" class="btn btn-success btn-sm">Kembalikan</a>
                            </td>
                            <?php endif; ?>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>
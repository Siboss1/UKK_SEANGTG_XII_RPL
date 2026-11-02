<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}

$role = $_SESSION['user']['role'];

// VALIDASI: Admin dilarang mengakses halaman ini
// VALIDASI: Hanya Petugas yang boleh mengakses, Admin dilarang
if ($role !== 'petugas') {
    echo "<script>alert('Akses ditolak! Halaman ini khusus untuk Petugas.'); window.location='dashboard.php';</script>";
    exit;
}
// Proses Update Status (Jika diperlukan oleh Petugas, atau bisa disesuaikan)
if (isset($_POST['ubah_status']) && $role === 'petugas') {
    $id_peminjaman = $_POST['id_peminjaman'];
    $status_baru = $_POST['status'];

    $stmt = $pdo->prepare("UPDATE peminjaman SET status = ? WHERE id = ?");
    $stmt->execute([$status_baru, $id_peminjaman]);
    header("Location: peminjaman.php");
    exit;
}

// Ambil data peminjaman beserta nama alat dan peminjam
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
    <title>Daftar Peminjaman - Peminjaman Alat</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container my-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Daftar Peminjaman Alat</h2>
            <a href="dashboard.php" class="btn btn-secondary">Kembali ke Dashboard</a>
        </div>

        <!-- Tabel Daftar Peminjaman -->
        <div class="card shadow-sm">
            <div class="card-header bg-dark text-white">Informasi Status Peminjaman Alat</div>
            <div class="card-body">
                <table class="table table-bordered table-striped align-middle">
                    <thead class="table-dark">
                        <tr>
                            <th>ID</th>
                            <th>Nama Alat</th>
                            <th>Peminjam</th>
                            <th>Jumlah</th>
                            <th>Tgl Pinjam</th>
                            <th>Tgl Kembali</th>
                            <th>Status Saat Ini</th>
                            <?php if ($role === 'petugas'): ?>
                                <th>Aksi / Ubah Status</th>
                            <?php endif; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($peminjaman)): ?>
                            <tr>
                                <td colspan="<?= ($role === 'petugas') ? 8 : 7; ?>" class="text-center text-muted">Belum ada data peminjaman.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($peminjaman as $p): ?>
                            <tr>
                                <td><?= $p['id']; ?></td>
                                <td><?= htmlspecialchars($p['nama_alat'] ?? '-'); ?></td>
                                <td><?= htmlspecialchars($p['nama_peminjam'] ?? '-'); ?></td>
                                <td><?= $p['jumlah'] ?? 1; ?></td>
                                <td><?= $p['tanggal_pinjam'] ?? '-'; ?></td>
                                <td><?= $p['tanggal_kembali'] ?? '-'; ?></td>
                                <td>
                                    <?php 
                                        $status = $p['status'] ?? 'Pending';
                                        $badge_bg = 'bg-secondary';
                                        if ($status === 'Disetujui') $badge_bg = 'bg-success';
                                        elseif ($status === 'Tidak Disetujui') $badge_bg = 'bg-danger';
                                        elseif ($status === 'Pending') $badge_bg = 'bg-warning text-dark';
                                    ?>
                                    <span class="badge <?= $badge_bg; ?>"><?= $status; ?></span>
                                </td>
                                
                                <!-- Kolom Aksi hanya muncul jika yang login Petugas, Peminjam hanya bisa melihat -->
                                <?php if ($role === 'petugas'): ?>
                                <td>
                                    <form method="POST" class="d-flex gap-2">
                                        <input type="hidden" name="id_peminjaman" value="<?= $p['id']; ?>">
                                        <select name="status" class="form-select form-select-sm" required>
                                            <option value="Disetujui" <?= ($status === 'Disetujui') ? 'selected' : ''; ?>>Disetujui</option>
                                            <option value="Tidak Disetujui" <?= ($status === 'Tidak Disetujui') ? 'selected' : ''; ?>>Tidak Disetujui</option>
                                        </select>
                                        <button type="submit" name="ubah_status" class="btn btn-primary btn-sm text-nowrap">Simpan</button>
                                    </form>
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
</body>
</html>
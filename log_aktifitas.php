<?php
session_start();
require_once 'config.php';

// Validasi akses (Hanya Petugas dan Admin yang boleh mencetak laporan)
if (!isset($_SESSION['user']) || !in_array($_SESSION['user']['role'], ['admin', 'petugas'])) {
    echo "<script>alert('Akses ditolak!'); window.location='dashboard.php';</script>";
    exit;
}

// Filter tanggal jika ada
$tgl_mulai = $_GET['tgl_mulai'] ?? '';
$tgl_selesai = $_GET['tgl_selesai'] ?? '';

$sql = "SELECT p.*, a.nama AS nama_alat, u.nama AS nama_peminjam 
        FROM peminjaman p 
        LEFT JOIN alat a ON p.alat_id = a.id 
        LEFT JOIN users u ON p.user_id = u.id";

$params = [];
if (!empty($tgl_mulai) && !empty($tgl_selesai)) {
    $sql .= " WHERE p.tanggal_pinjam BETWEEN ? AND ?";
    $params = [$tgl_mulai, $tgl_selesai];
}

$sql .= " ORDER BY p.id DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$laporan = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Mencetak Laporan - Peminjaman Alat</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        /* Sembunyikan elemen navigasi & filter saat mencetak/simpan ke PDF */
        @media print {
            .no-print {
                display: none !important;
            }
            body {
                background-color: #fff !important;
            }
            .card {
                border: none !important;
                box-shadow: none !important;
            }
        }
    </style>
</head>
<body class="bg-light">
    <div class="container my-5">
        
        <!-- Header & Navigasi (Hilang saat diprint) -->
        <div class="d-flex justify-content-between align-items-center mb-4 no-print">
            <h2>Laporan Peminjaman Alat</h2>
            <div>
                <button onclick="window.print()" class="btn btn-danger me-2">🖨️ Cetak / Ekspor PDF</button>
                <a href="dashboard.php" class="btn btn-secondary">Kembali ke Dashboard</a>
            </div>
        </div>

        <!-- Form Filter Tanggal (Hilang saat diprint) -->
        <div class="card mb-4 shadow-sm no-print">
            <div class="card-body">
                <form method="GET" class="row g-3 align-items-end">
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Tanggal Mulai</label>
                        <input type="date" name="tgl_mulai" class="form-control" value="<?= htmlspecialchars($tgl_mulai) ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Tanggal Selesai</label>
                        <input type="date" name="tgl_selesai" class="form-control" value="<?= htmlspecialchars($tgl_selesai) ?>">
                    </div>
                    <div class="col-md-4 d-flex gap-2">
                        <button type="submit" class="btn btn-primary w-100">Filter Data</button>
                        <a href="laporan.php" class="btn btn-outline-secondary">Reset</a>
                    </div>
                </form>
            </div>
        </div>

        <!-- Kop Laporan (Hanya tampil saat dicetak ke PDF) -->
        <div class="text-center mb-4">
            <h3 class="fw-bold">LAPORAN PEMINJAMAN ALAT</h3>
            <p class="text-muted mb-1">Sistem Informasi Peminjaman Alat Perusahaan / Sekolah</p>
            <?php if (!empty($tgl_mulai) && !empty($tgl_selesai)): ?>
                <small class="fw-bold">Periode: <?= date('d/m/Y', strtotime($tgl_mulai)) ?> s/d <?= date('d/m/Y', strtotime($tgl_selesai)) ?></small>
            <?php endif; ?>
            <hr class="mt-2">
        </div>

        <!-- Tabel Rekap Data -->
        <div class="card shadow-sm">
            <div class="card-body p-0">
                <table class="table table-bordered table-striped align-middle mb-0">
                    <thead class="table-dark">
                        <tr>
                            <th class="text-center" width="5%">No</th>
                            <th>Nama Alat</th>
                            <th>Peminjam</th>
                            <th class="text-center" width="8%">Jumlah</th>
                            <th class="text-center">Tgl Pinjam</th>
                            <th class="text-center">Tgl Kembali</th>
                            <th class="text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($laporan)): ?>
                            <tr>
                                <td colspan="7" class="text-center text-muted py-3">Tidak ada data peminjaman yang ditemukan.</td>
                            </tr>
                        <?php else: ?>
                            <?php $no = 1; foreach ($laporan as $row): ?>
                            <tr>
                                <td class="text-center"><?= $no++; ?></td>
                                <td><?= htmlspecialchars($row['nama_alat'] ?? '-'); ?></td>
                                <td><?= htmlspecialchars($row['nama_peminjam'] ?? '-'); ?></td>
                                <td class="text-center"><?= $row['jumlah'] ?? 1; ?></td>
                                <td class="text-center"><?= $row['tanggal_pinjam'] ?? '-'; ?></td>
                                <td class="text-center"><?= $row['tanggal_kembali'] ?? '-'; ?></td>
                                <td class="text-center">
                                    <?php 
                                        $status = $row['status'] ?? 'Pending';
                                        $badge = 'bg-secondary';
                                        if ($status === 'Disetujui') $badge = 'bg-success';
                                        elseif ($status === 'Tidak Disetujui') $badge = 'bg-danger';
                                        elseif ($status === 'Pending') $badge = 'bg-warning text-dark';
                                    ?>
                                    <span class="badge <?= $badge; ?>"><?= $status; ?></span>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Tanda Tangan Cetak (Muncul saat diprint/PDF) -->
        <div class="row mt-5 d-none d-print-flex">
            <div class="col-8"></div>
            <div class="col-4 text-center">
                <p>Mengetahui,</p>
                <p class="fw-bold">Petugas Ruangan</p>
                <br><br><br>
                <p class="border-bottom d-inline-block px-4 fw-bold"><?= htmlspecialchars($_SESSION['user']['nama']); ?></p>
            </div>
        </div>

    </div>
</body>
</html>
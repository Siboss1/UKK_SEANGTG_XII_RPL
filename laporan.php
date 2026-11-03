<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}

$role = strtolower($_SESSION['user']['role']);

// VALIDASI: Hanya Admin dan Petugas yang boleh mengakses halaman ini
if ($role !== 'admin' && $role !== 'petugas') {
    echo "<script>alert('Akses ditolak! Halaman ini khusus untuk Admin dan Petugas.'); window.location='dashboard.php';</script>";
    exit;
}

$pesan_error = "";
$pesan_sukses = "";

// 1. PROSES UPLOAD LAPORAN PDF (KHUSUS PETUGAS)
if (isset($_POST['upload_laporan']) && $role === 'petugas') {
    $judul = trim($_POST['judul_laporan']);
    $tanggal = date('Y-m-d');
    
    if (isset($_FILES['file_pdf']) && $_FILES['file_pdf']['error'] === 0) {
        $nama_file = $_FILES['file_pdf']['name'];
        $ukuran_file = $_FILES['file_pdf']['size'];
        $tmp_file = $_FILES['file_pdf']['tmp_name'];
        $ekstensi = strtolower(pathinfo($nama_file, PATHINFO_EXTENSION));

        if ($ekstensi === 'pdf') {
            if ($ukuran_file <= 5 * 1024 * 1024) { // Maksimal 5MB
                $nama_baru = 'laporan_' . time() . '_' . rand(100, 999) . '.pdf';
                $folder_tujuan = 'uploads/';

                if (!is_dir($folder_tujuan)) {
                    mkdir($folder_tujuan, 0777, true);
                }

                if (move_uploaded_file($tmp_file, $folder_tujuan . $nama_baru)) {
                    $stmt = $pdo->prepare("INSERT INTO laporan (judul, nama_file, tanggal, diunggah_oleh) VALUES (?, ?, ?, ?)");
                    $stmt->execute([$judul, $nama_baru, $tanggal, $_SESSION['user']['nama']]);
                    $pesan_sukses = "Laporan PDF berhasil diunggah!";
                } else {
                    $pesan_error = "Gagal mengunggah file ke server.";
                }
            } else {
                $pesan_error = "Ukuran file terlalu besar! Maksimal 5MB.";
            }
        } else {
            $pesan_error = "Format file tidak valid! Harap unggah file berformat .pdf.";
        }
    } else {
        $pesan_error = "Pilih file PDF terlebih dahulu!";
    }
}

// 2. PROSES HAPUS LAPORAN (KHUSUS PETUGAS)
if (isset($_GET['hapus']) && $role === 'petugas') {
    $id_lap = $_GET['hapus'];
    $get_file = $pdo->prepare("SELECT nama_file FROM laporan WHERE id = ?");
    $get_file->execute([$id_lap]);
    $data_lap = $get_file->fetch();
    
    if ($data_lap) {
        $file_path = 'uploads/' . $data_lap['nama_file'];
        if (file_exists($file_path)) {
            unlink($file_path);
        }
        $del = $pdo->prepare("DELETE FROM laporan WHERE id = ?");
        $del->execute([$id_lap]);
        $pesan_sukses = "Laporan berhasil dihapus!";
    }
}

// Tangkap filter tanggal jika ada
$tgl_mulai = $_GET['tgl_mulai'] ?? '';
$tgl_selesai = $_GET['tgl_selesai'] ?? '';

// Ambil data rekapitulasi peminjaman
try {
    $query = "SELECT p.*, a.nama AS nama_alat, u.nama AS nama_peminjam 
              FROM peminjaman p 
              LEFT JOIN alat a ON p.alat_id = a.id 
              LEFT JOIN users u ON p.user_id = u.id";
              
    if (!empty($tgl_mulai) && !empty($tgl_selesai)) {
        $query .= " WHERE p.tanggal_pinjam BETWEEN :tgl_mulai AND :tgl_selesai";
    }
    $query .= " ORDER BY p.id DESC";

    $stmt = $pdo->prepare($query);
    if (!empty($tgl_mulai) && !empty($tgl_selesai)) {
        $stmt->execute(['tgl_mulai' => $tgl_mulai, 'tgl_selesai' => $tgl_selesai]);
    } else {
        $stmt->execute();
    }
    $laporan_list = $stmt->fetchAll();
} catch (PDOException $e) {
    $laporan_list = [];
}

// Ambil data file PDF yang diunggah jika tabelnya ada
try {
    $pdf_list = $pdo->query("SELECT * FROM laporan ORDER BY id DESC")->fetchAll();
} catch (PDOException $e) {
    $pdf_list = [];
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Peminjaman Alat</title>
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
        .form-control {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(191, 149, 63, 0.3);
            color: #ffffff !important;
            border-radius: 0.75rem;
            padding: 0.75rem 1rem;
        }
        .form-control:focus {
            background: rgba(255, 255, 255, 0.08);
            border-color: #fcf6ba;
            color: #ffffff !important;
            box-shadow: 0 0 10px rgba(252, 246, 186, 0.2);
        }
        .form-control::placeholder {
            color: rgba(255, 255, 255, 0.4);
        }
        input[type="date"]::-webkit-calendar-picker-indicator {
            filter: invert(1) brightness(1.5);
            cursor: pointer;
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
        /* CSS Khusus Saat Cetak / Print */
        @media print {
            .btn, .header-banner, form, .no-print, .navbar, .gold-card-wrapper {
                display: none !important;
            }
            body {
                background-color: white !important;
                color: black !important;
            }
        }
    </style>
</head>
<body>
    <div class="container my-4">
        <!-- Header Section -->
        <div class="d-flex justify-content-between align-items-center mb-4 pb-3 border-bottom border-secondary border-opacity-25">
            <div>
                <h2 class="gold-title mb-1"><i class="bi bi-printer-fill me-2"></i> Laporan Peminjaman Alat</h2>
                <p class="text-secondary small mb-0">Sistem Informasi Peminjaman Alat (Admin: Pantau | Petugas: Kelola & Cetak)</p>
            </div>
            <div class="d-flex align-items-center gap-3">
                <div class="dragon-badge" title="Thevorix Dragon Emblem">
                    <i class="bi bi-fire"></i>
                </div>
                <div class="d-flex gap-2 align-self-start align-self-md-auto no-print">
                    <button onclick="window.print()" class="btn btn-gold px-4 py-2 rounded-pill shadow-sm">
                        <i class="bi bi-printer-fill me-1"></i> Cetak / Ekspor PDF
                    </button>
                    <a href="dashboard.php" class="btn btn-outline-gold px-4 py-2 rounded-pill shadow-sm">
                        <i class="bi bi-arrow-left me-1"></i> Dashboard
                    </a>
                </div>
            </div>
        </div>

        <!-- Notifikasi Pesan -->
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

        <!-- FORM UPLOAD PDF (KHUSUS PETUGAS) -->
        <?php if ($role === 'petugas'): ?>
        <div class="gold-card-wrapper no-print">
            <div class="content-card">
                <h5 class="gold-title mb-4"><i class="bi bi-cloud-arrow-up-fill me-2"></i> Unggah Berkas Laporan PDF</h5>
                <form action="" method="POST" enctype="multipart/form-data" class="row g-3">
                    <div class="col-md-5">
                        <label class="form-label fw-semibold small text-secondary">Judul / Keterangan Laporan</label>
                        <input type="text" name="judul_laporan" class="form-control" placeholder="Contoh: Laporan Bulanan Peminjaman" required>
                    </div>
                    <div class="col-md-5">
                        <label class="form-label fw-semibold small text-secondary">Pilih Berkas PDF</label>
                        <input type="file" name="file_pdf" class="form-control" accept=".pdf" required>
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <button type="submit" name="upload_laporan" class="btn btn-gold w-100 py-2">
                            <i class="bi bi-upload me-1"></i> Upload
                        </button>
                    </div>
                </form>
            </div>
        </div>
        <?php endif; ?>

        <!-- Filter Tanggal -->
        <div class="gold-card-wrapper no-print">
            <div class="content-card py-3">
                <form method="GET" class="row g-3 align-items-end">
                    <div class="col-md-4">
                        <label class="form-label small fw-semibold text-secondary">Tanggal Mulai</label>
                        <input type="date" name="tgl_mulai" value="<?= htmlspecialchars($tgl_mulai); ?>" class="form-control">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-semibold text-secondary">Tanggal Selesai</label>
                        <input type="date" name="tgl_selesai" value="<?= htmlspecialchars($tgl_selesai); ?>" class="form-control">
                    </div>
                    <div class="col-md-4 d-flex gap-2">
                        <button type="submit" class="btn btn-gold flex-grow-1">
                            <i class="bi bi-filter me-1"></i> Filter Data
                        </button>
                        <a href="laporan.php" class="btn btn-outline-gold px-3 d-flex align-items-center justify-content-center" title="Reset Filter">
                            <i class="bi bi-arrow-counterclockwise"></i>
                        </a>
                    </div>
                </form>
            </div>
        </div>

        <!-- Tabel Rekapitulasi Data Peminjaman -->
        <div class="gold-card-wrapper">
            <div class="content-card">
                <h5 class="gold-title mb-4"><i class="bi bi-table me-2"></i> Rekapitulasi Data Peminjaman</h5>
                <div class="table-responsive">
                    <table class="table table-dark-gold align-middle mb-0">
                        <thead>
                            <tr>
                                <th class="py-3 ps-3">No</th>
                                <th class="py-3">Nama Alat</th>
                                <th class="py-3">Peminjam</th>
                                <th class="py-3 text-center">Jumlah</th>
                                <th class="py-3">Tgl Pinjam</th>
                                <th class="py-3">Tgl Kembali</th>
                                <th class="py-3 text-center">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($laporan_list)): ?>
                                <tr>
                                    <td colspan="7" class="text-center py-5 text-secondary">
                                        <i class="bi bi-inbox fs-1 d-block mb-2 text-secondary opacity-50"></i> Tidak ada data laporan pada rentang tanggal tersebut.
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($laporan_list as $index => $row): ?>
                                <tr>
                                    <td class="fw-bold text-warning ps-3"><?= $index + 1; ?></td>
                                    <td class="fw-semibold text-white"><?= htmlspecialchars($row['nama_alat'] ?? '-'); ?></td>
                                    <td>
                                        <span class="badge bg-secondary bg-opacity-25 text-light border border-secondary px-2 py-1">
                                            <i class="bi bi-person-fill text-warning me-1"></i> <?= htmlspecialchars($row['nama_peminjam'] ?? '-'); ?>
                                        </span>
                                    </td>
                                    <td class="text-center"><span class="badge bg-info bg-opacity-10 text-info fw-bold px-3 py-1"><?= $row['jumlah'] ?? 1; ?> Unit</span></td>
                                    <td class="small text-secondary"><?= $row['tanggal_pinjam'] ?? '-'; ?></td>
                                    <td class="small text-warning fw-semibold"><?= $row['tanggal_kembali'] ?? '-'; ?></td>
                                    <td class="text-center">
                                        <?php 
                                            $status = $row['status'] ?? 'Pending';
                                            $badge_bg = 'bg-secondary';
                                            if ($status === 'Disetujui' || $status === 'Dikembalikan') $badge_bg = 'bg-success';
                                            elseif ($status === 'Tidak Disetujui') $badge_bg = 'bg-danger';
                                            elseif ($status === 'Pending') $badge_bg = 'bg-warning text-dark';
                                        ?>
                                        <span class="badge <?= $badge_bg; ?> px-3 py-1 rounded-pill fw-bold shadow-sm"><?= htmlspecialchars($status); ?></span>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Tabel Arsip Dokumen PDF yang Diunggah -->
        <?php if (!empty($pdf_list)): ?>
        <div class="gold-card-wrapper">
            <div class="content-card">
                <h5 class="gold-title mb-4"><i class="bi bi-file-earmark-pdf-fill me-2"></i> Arsip Berkas PDF</h5>
                <div class="table-responsive">
                    <table class="table table-dark-gold align-middle mb-0">
                        <thead>
                            <tr>
                                <th class="py-3 ps-3">#ID</th>
                                <th class="py-3">Judul Laporan</th>
                                <th class="py-3">Tanggal Upload</th>
                                <th class="py-3">Diunggah Oleh</th>
                                <th class="py-3 text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($pdf_list as $pdf): ?>
                            <tr>
                                <td class="fw-bold text-warning ps-3">#<?= $pdf['id']; ?></td>
                                <td class="fw-semibold text-white"><?= htmlspecialchars($pdf['judul']); ?></td>
                                <td class="small text-secondary"><?= $pdf['tanggal']; ?></td>
                                <td>
                                    <span class="badge bg-secondary bg-opacity-25 text-light border border-secondary px-2 py-1">
                                        <i class="bi bi-person-fill text-warning me-1"></i> <?= htmlspecialchars($pdf['diunggah_oleh']); ?>
                                    </span>
                                </td>
                                <td class="text-center">
                                    <div class="d-flex justify-content-center gap-2">
                                        <a href="uploads/<?= $pdf['nama_file']; ?>" target="_blank" class="btn btn-gold btn-sm px-3 shadow-sm rounded-pill fw-semibold">
                                            <i class="bi bi-eye-fill me-1"></i> Lihat PDF
                                        </a>
                                        <?php if ($role === 'petugas'): ?>
                                            <a href="laporan.php?hapus=<?= $pdf['id']; ?>" class="btn btn-danger btn-sm px-3 shadow-sm rounded-pill no-print fw-semibold" onclick="return confirm('Yakin ingin menghapus arsip PDF ini?')">
                                                <i class="bi bi-trash-fill me-1"></i> Hapus
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <?php endif; ?>

    </div>

    <!-- Bootstrap JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
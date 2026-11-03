<?php
session_start();
require_once 'config.php';

// Validasi Keamanan: Pastikan user sudah login
if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}

$pesan_sukses = '';
$pesan_error = '';

// Proses Tambah Kategori (Otomatis menyesuaikan nama kolom database)
if (isset($_POST['tambah_kategori'])) {
    $nama_kategori = trim($_POST['nama_kategori']);

    if (!empty($nama_kategori)) {
        try {
            // Coba simpan ke kolom 'kategori'
            $stmt = $pdo->prepare("INSERT INTO kategori (kategori) VALUES (?)");
            $stmt->execute([$nama_kategori]);
            $pesan_sukses = "Kategori baru berhasil ditambahkan!";
        } catch (PDOException $e) {
            try {
                // Jika gagal, coba simpan ke kolom 'nama'
                $stmt = $pdo->prepare("INSERT INTO kategori (nama) VALUES (?)");
                $stmt->execute([$nama_kategori]);
                $pesan_sukses = "Kategori baru berhasil ditambahkan!";
            } catch (PDOException $ex) {
                try {
                    // Jika gagal juga, coba kolom 'nama_kategori'
                    $stmt = $pdo->prepare("INSERT INTO kategori (nama_kategori) VALUES (?)");
                    $stmt->execute([$nama_kategori]);
                    $pesan_sukses = "Kategori baru berhasil ditambahkan!";
                } catch (PDOException $exc) {
                    $pesan_error = "Gagal menambah kategori: " . $exc->getMessage();
                }
            }
        }
    } else {
        $pesan_error = "Nama kategori tidak boleh kosong!";
    }
}

// Proses Hapus Kategori
if (isset($_GET['hapus'])) {
    $id_kategori = $_GET['hapus'];
    try {
        $stmt = $pdo->prepare("DELETE FROM kategori WHERE id = ?");
        $stmt->execute([$id_kategori]);
        header('Location: kategori.php');
        exit;
    } catch (PDOException $e) {
        try {
            $stmt = $pdo->prepare("DELETE FROM kategori WHERE id_kategori = ?");
            $stmt->execute([$id_kategori]);
            header('Location: kategori.php');
            exit;
        } catch (PDOException $ex) {
            $pesan_error = "Gagal menghapus kategori! Kategori mungkin sedang digunakan.";
        }
    }
}

// Ambil daftar kategori dari database
try {
    $stmt = $pdo->query("SELECT * FROM kategori ORDER BY id DESC");
    $kategori_list = $stmt->fetchAll();
} catch (PDOException $e) {
    try {
        $stmt = $pdo->query("SELECT * FROM kategori ORDER BY id_kategori DESC");
        $kategori_list = $stmt->fetchAll();
    } catch (PDOException $ex) {
        $kategori_list = [];
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Kategori - Peminjaman Alat</title>
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
        /* Kartu kerangka emas mewah */
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
        /* Form Control Custom Emas */
        .form-control {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(191, 149, 63, 0.3);
            color: #fff;
            border-radius: 0.75rem;
            padding: 0.75rem 1rem;
        }
        .form-control:focus {
            background: rgba(255, 255, 255, 0.08);
            border-color: #fcf6ba;
            color: #fff;
            box-shadow: 0 0 10px rgba(252, 246, 186, 0.2);
        }
        .form-control::placeholder {
            color: rgba(255, 255, 255, 0.4);
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
        /* Tabel Custom */
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
        /* Styling Badge / Logo Naga Kecil di Header */
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
                <h2 class="gold-title mb-1"><i class="bi bi-tags-fill me-2"></i> Data Kategori</h2>
                <p class="text-secondary small mb-0">Kelola kategori alat inventaris dengan mudah</p>
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

        <!-- Form Tambah Kategori -->
        <div class="gold-card-wrapper">
            <div class="content-card">
                <h5 class="gold-title mb-4"><i class="bi bi-plus-circle-fill me-2"></i> Tambah Kategori</h5>
                
                <form action="" method="POST">
                    <div class="row g-3 align-items-center">
                        <div class="col-md-9">
                            <input type="text" name="nama_kategori" class="form-control" placeholder="Masukkan nama kategori..." required>
                        </div>
                        <div class="col-md-3">
                            <button type="submit" name="tambah_kategori" class="btn btn-gold w-100 py-2">
                                <i class="bi bi-save2-fill me-1"></i> Tambah Kategori
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Daftar Kategori (Tabel) -->
        <div class="gold-card-wrapper">
            <div class="content-card">
                <h5 class="gold-title mb-4"><i class="bi bi-table me-2"></i> Daftar Kategori</h5>

                <div class="table-responsive">
                    <table class="table table-dark-gold align-middle mb-0">
                        <thead>
                            <tr>
                                <th class="ps-3" style="width: 10%;">ID</th>
                                <th>Nama Kategori</th>
                                <th class="text-center" style="width: 20%;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($kategori_list)): ?>
                                <tr>
                                    <td colspan="3" class="text-center py-4 text-secondary">Belum ada data kategori.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($kategori_list as $row): ?>
                                <tr>
                                    <td class="ps-3 fw-bold text-warning">#<?= $row['id'] ?? $row['id_kategori'] ?? 1; ?></td>
                                    <td class="fw-semibold"><?= htmlspecialchars($row['nama_kategori'] ?? $row['kategori'] ?? $row['nama'] ?? '-'); ?></td>
                                    <td class="text-center">
                                        <a href="kategori.php?hapus=<?= $row['id'] ?? $row['id_kategori']; ?>" class="btn btn-danger btn-sm px-3 py-1 rounded-pill fw-semibold shadow-sm" onclick="return confirm('Yakin ingin menghapus kategori ini?')">
                                            <i class="bi bi-trash-fill me-1"></i> Hapus
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

    <!-- Bootstrap JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
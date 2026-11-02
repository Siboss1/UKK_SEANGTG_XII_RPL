<?php
require_once 'config.php';
wajib_login();

$pesan = '';
$tipe_pesan = 'sukses'; // sukses atau error

// ============================================
// 1. PROSES TAMBAH ALAT BARU
// ============================================
if (isset($_POST['tambah_alat'])) {
    $nama        = trim($_POST['nama']);
    $stok        = (int) $_POST['stok'];
    $kondisi     = $_POST['kondisi'];
    $keterangan  = trim($_POST['keterangan']);

    // Perbaiki pengisian kategori_id
    $kategori_id = null;
    if (!empty($_POST['kategori_id']) && is_numeric($_POST['kategori_id'])) {
        $kategori_id = (int) $_POST['kategori_id'];
    }

    if ($nama !== '' && $stok >= 0) {
        try {
            $sql = "INSERT INTO alat (kategori_id, nama, stok, kondisi, keterangan)
                    VALUES (?, ?, ?, ?, ?)";

            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                $kategori_id,
                $nama,
                $stok,
                $kondisi,
                $keterangan
            ]);

            $pesan = "Alat baru <strong>" . htmlspecialchars($nama) . "</strong> berhasil ditambahkan!";
            $tipe_pesan = 'sukses';
        } catch (PDOException $e) {
            // Cek apakah error karena foreign key (kategori tidak ditemukan)
            if ($e->getCode() == 23000) {
                $pesan = "Gagal menambahkan alat! ID Kategori yang kamu masukkan <strong>tidak ditemukan</strong> di database. Kosongkan saja jika tidak punya kategori.";
            } else {
                $pesan = "Terjadi kesalahan: " . $e->getMessage();
            }
            $tipe_pesan = 'error';
        }
    } else {
        $pesan = "Nama alat dan stok harus diisi dengan benar!";
        $tipe_pesan = 'error';
    }
}

// ============================================
// 2. PROSES TAMBAH STOK
// ============================================
if (isset($_POST['tambah_stok'])) {
    $alat_id = (int) $_POST['alat_id'];
    $jumlah  = (int) $_POST['jumlah'];

    if ($alat_id > 0 && $jumlah > 0) {
        $stmt = $pdo->prepare("SELECT stok, nama FROM alat WHERE id = ?");
        $stmt->execute([$alat_id]);
        $alat = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($alat) {
            $stok_baru = $alat['stok'] + $jumlah;

            $update = $pdo->prepare("UPDATE alat SET stok = ? WHERE id = ?");
            $update->execute([$stok_baru, $alat_id]);

            $pesan = "Stok <strong>" . htmlspecialchars($alat['nama']) . "</strong> berhasil ditambah sebanyak $jumlah. Stok sekarang: <strong>$stok_baru</strong>";
            $tipe_pesan = 'sukses';
        } else {
            $pesan = "Alat tidak ditemukan!";
            $tipe_pesan = 'error';
        }
    } else {
        $pesan = "Pilih alat dan isi jumlah stok dengan benar!";
        $tipe_pesan = 'error';
    }
}

// Ambil semua data alat
$data = $pdo->query("SELECT * FROM alat ORDER BY id DESC")
            ->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Data Alat</title>

    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f1f5f9;
            padding: 30px;
        }

        .container {
            max-width: 950px;
            margin: auto;
            background: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }

        h2, h3 {
            margin-top: 0;
        }

        .pesan {
            padding: 12px 15px;
            border-radius: 6px;
            margin-bottom: 20px;
            border-left: 5px solid;
        }

        .pesan.sukses {
            background: #dcfce7;
            color: #166534;
            border-color: #22c55e;
        }

        .pesan.error {
            background: #fee2e2;
            color: #991b1b;
            border-color: #ef4444;
        }

        .form-section {
            background: #f8fafc;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 25px;
            border: 1px solid #e2e8f0;
        }

        label {
            display: block;
            margin-top: 10px;
            font-weight: 600;
            font-size: 14px;
        }

        input, select, textarea, button {
            padding: 10px;
            margin: 6px 0 12px 0;
            width: 100%;
            box-sizing: border-box;
            border: 1px solid #cbd5e1;
            border-radius: 6px;
            font-size: 14px;
        }

        button {
            background: #2563eb;
            color: white;
            border: none;
            cursor: pointer;
            font-weight: 600;
            margin-top: 10px;
        }

        button:hover {
            background: #1d4ed8;
        }

        .btn-stok {
            background: #059669;
        }

        .btn-stok:hover {
            background: #047857;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        th, td {
            border: 1px solid #e2e8f0;
            padding: 10px 12px;
            text-align: left;
        }

        th {
            background: #2563eb;
            color: white;
        }

        tr:nth-child(even) {
            background: #f8fafc;
        }
    </style>
</head>
<body>

<div class="container">

    <h2>Data Alat</h2>

    <?php if ($pesan): ?>
        <div class="pesan <?= $tipe_pesan ?>">
            <?= $pesan ?>
        </div>
    <?php endif; ?>

    <!-- ==================== FORM TAMBAH ALAT ==================== -->
    <div class="form-section">
        <h3>Tambah Alat Baru</h3>

        <form method="POST">
            <label>Nama Alat</label>
            <input type="text" name="nama" required placeholder="Contoh: Obeng Set">

            <label>ID Kategori (opsional - kosongkan saja jika tidak ada)</label>
            <input type="number" name="kategori_id" placeholder="Kosongkan jika tidak punya kategori">

            <label>Stok Awal</label>
            <input type="number" name="stok" min="0" required value="0">

            <label>Kondisi</label>
            <select name="kondisi">
                <option value="Baik">Baik</option>
                <option value="Rusak">Rusak</option>
                <option value="Perbaikan">Perbaikan</option>
            </select>

            <label>Keterangan</label>
            <textarea name="keterangan" rows="2" placeholder="Keterangan tambahan..."></textarea>

            <button type="submit" name="tambah_alat">
                + Tambah Alat
            </button>
        </form>
    </div>

    <!-- ==================== FORM TAMBAH STOK ==================== -->
    <div class="form-section">
        <h3>Tambah Stok</h3>

        <form method="POST">
            <label>Pilih Alat</label>
            <select name="alat_id" required>
                <option value="">-- Pilih Alat --</option>
                <?php foreach ($data as $alat): ?>
                    <option value="<?= $alat['id'] ?>">
                        ID <?= $alat['id'] ?> - <?= htmlspecialchars($alat['nama']) ?> 
                        (Stok saat ini: <?= $alat['stok'] ?>)
                    </option>
                <?php endforeach; ?>
            </select>

            <label>Jumlah Stok yang Ditambahkan</label>
            <input type="number" name="jumlah" min="1" required placeholder="Contoh: 5">

            <button type="submit" name="tambah_stok" class="btn-stok">
                + Tambah Stok
            </button>
        </form>
    </div>

    <!-- ==================== DAFTAR ALAT ==================== -->
    <h3>Daftar Alat</h3>

    <table>
        <tr>
            <th>ID</th>
            <th>Nama Alat</th>
            <th>Stok</th>
            <th>Kondisi</th>
            <th>Keterangan</th>
        </tr>

        <?php foreach ($data as $alat): ?>
        <tr>
            <td><?= $alat['id'] ?></td>
            <td><?= htmlspecialchars($alat['nama']) ?></td>
            <td><strong><?= $alat['stok'] ?></strong></td>
            <td><?= htmlspecialchars($alat['kondisi']) ?></td>
            <td><?= htmlspecialchars($alat['keterangan'] ?? '') ?></td>
        </tr>
        <?php endforeach; ?>
    </table>

</div>

</body>
</html>
<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once 'config.php';
wajib_login();

// Ambil semua data peminjaman
$riwayat = $pdo->query("
    SELECT 
        p.*, 
        a.nama AS nama_alat, 
        u.nama AS nama_user
    FROM peminjaman p
    JOIN alat a ON a.id = p.alat_id
    JOIN users u ON u.id = p.user_id
    ORDER BY p.id DESC
")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Riwayat Peminjaman</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f1f5f9;
            padding: 30px;
            margin: 0;
        }
        .container {
            max-width: 1100px;
            margin: auto;
            background: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        h2 {
            margin-top: 0;
            color: #1e293b;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            border: 1px solid #e2e8f0;
            padding: 10px 12px;
            text-align: left;
            font-size: 14px;
        }
        th {
            background: #2563eb;
            color: white;
        }
        tr:nth-child(even) {
            background: #f8fafc;
        }
        .status-disetujui {
            background: #dbeafe;
            color: #1e40af;
            padding: 4px 10px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 600;
        }
        .status-dikembalikan {
            background: #dcfce7;
            color: #166534;
            padding: 4px 10px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 600;
        }
        .status-lain {
            background: #fef3c7;
            color: #92400e;
            padding: 4px 10px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 600;
        }
        .back {
            display: inline-block;
            margin-bottom: 20px;
            color: #2563eb;
            text-decoration: none;
            font-weight: 600;
        }
        .back:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>

<div class="container">

    <a href="dashboard.php" class="back">← Kembali ke Dashboard</a>

    <h2>Riwayat Peminjaman</h2>

    <?php if (count($riwayat) === 0): ?>
        <p>Belum ada data peminjaman.</p>
    <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nama Alat</th>
                    <th>Peminjam</th>
                    <th>Jumlah</th>
                    <th>Tanggal Pinjam</th>
                    <th>Harus Kembali</th>
                    <th>Tanggal Dikembalikan</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($riwayat as $r): ?>
                <tr>
                    <td><?= $r['id'] ?></td>
                    <td><?= htmlspecialchars($r['nama_alat']) ?></td>
                    <td><?= htmlspecialchars($r['nama_user']) ?></td>
                    <td><?= $r['jumlah'] ?></td>
                    <td><?= date('d/m/Y', strtotime($r['tanggal_pinjam'])) ?></td>
                    <td><?= date('d/m/Y', strtotime($r['tanggal_kembali'])) ?></td>
                    <td>
                        <?php if (!empty($r['tanggal_realisasi_kembali'])): ?>
                            <?= date('d/m/Y', strtotime($r['tanggal_realisasi_kembali'])) ?>
                        <?php else: ?>
                            -
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if ($r['status'] == 'Disetujui'): ?>
                            <span class="status-disetujui">Disetujui</span>
                        <?php elseif ($r['status'] == 'Dikembalikan'): ?>
                            <span class="status-dikembalikan">Dikembalikan</span>
                        <?php else: ?>
                            <span class="status-lain"><?= htmlspecialchars($r['status']) ?></span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>

</div>

</body>
</html>
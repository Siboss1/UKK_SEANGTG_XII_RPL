<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

require_once 'config.php';
// Tambah kategori
if (isset($_POST['tambah'])) {
    $nama = trim($_POST['nama_kategori']);

    if ($nama !== '') {
        $stmt = $pdo->prepare(
            "INSERT INTO kategori (nama) VALUES (?)"
        );
        $stmt->execute([$nama]);

        header("Location: kategori.php");
        exit;
    }
}

// Hapus kategori
if (isset($_GET['hapus'])) {
    $id = (int) $_GET['hapus'];

    $stmt = $pdo->prepare(
        "DELETE FROM kategori WHERE id = ?"
    );
    $stmt->execute([$id]);

    header("Location: kategori.php");
    exit;
}

// Ambil data kategori
$stmt = $pdo->query("SELECT * FROM kategori ORDER BY id DESC");
$data = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Data Kategori</title>

    <style>
        body {
            font-family: Arial;
            background: #f4f6f9;
            padding: 30px;
        }

        .container {
            background: white;
            padding: 25px;
            border-radius: 10px;
            max-width: 900px;
            margin: auto;
        }

        input {
            padding: 10px;
            width: 70%;
            margin-bottom: 10px;
        }

        button {
            padding: 10px 20px;
            background: #0875e1;
            color: white;
            border: none;
            cursor: pointer;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        th, td {
            border: 1px solid #ddd;
            padding: 12px;
            text-align: left;
        }

        th {
            background: #0875e1;
            color: white;
        }

        a {
            color: red;
        }
    </style>
</head>

<body>

<div class="container">

    <h2>Data Kategori</h2>

    <h3>Tambah Kategori</h3>

    <form method="POST">
        <input
            type="text"
            name="nama_kategori"
            placeholder="Masukkan nama kategori"
            required
        >

        <button type="submit" name="tambah">
            Tambah Kategori
        </button>
    </form>

    <h3>Daftar Kategori</h3>

    <table>
        <tr>
            <th>ID</th>
            <th>Nama Kategori</th>
            <th>Aksi</th>
        </tr>

        <?php foreach ($data as $kategori): ?>
        <tr>
            <td><?= htmlspecialchars($kategori['id']) ?></td>

            <td>
                <?= htmlspecialchars($kategori['nama']) ?>
            </td>

            <td>
                <a
                    href="kategori.php?hapus=<?= $kategori['id'] ?>"
                    onclick="return confirm('Yakin ingin menghapus kategori ini?')"
                >
                    Hapus
                </a>
            </td>
        </tr>
        <?php endforeach; ?>

    </table>

    <br>

    <a href="dashboard.php" style="color:blue;">
        Kembali ke Dashboard
    </a>

</div>

</body>
</html>
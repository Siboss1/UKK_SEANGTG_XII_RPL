<?php
require_once 'config.php';
wajib_login();

// Ambil data pengguna
$stmt = $pdo->query("SELECT * FROM users");
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Sembunyikan kolom password
$kolom = [];

if (!empty($users)) {
    $kolom = array_keys($users[0]);

    $kolom = array_filter($kolom, function($nama) {
        return !in_array(strtolower($nama), [
            'password',
            'token'
        ]);
    });
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Data User</title>

<style>
body {
    font-family: Arial, sans-serif;
    background: #f1f5f9;
    padding: 30px;
}

.container {
    max-width: 1000px;
    margin: auto;
    background: white;
    padding: 25px;
    border-radius: 10px;
}

h2 {
    color: #1e293b;
}

table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 20px;
}

th {
    background: #2563eb;
    color: white;
}

th, td {
    padding: 12px;
    border: 1px solid #ddd;
    text-align: left;
}

a {
    display: inline-block;
    background: #2563eb;
    color: white;
    padding: 10px 15px;
    text-decoration: none;
    border-radius: 5px;
    margin-top: 15px;
}
</style>
</head>

<body>

<div class="container">

<h2>Data User</h2>

<p>Daftar Pengguna Sistem Peminjaman Alat</p>

<table>

<tr>
<?php foreach ($kolom as $nama): ?>
    <th><?= htmlspecialchars($nama) ?></th>
<?php endforeach; ?>
</tr>

<?php foreach ($users as $user): ?>
<tr>
    <?php foreach ($kolom as $nama): ?>
        <td>
            <?= htmlspecialchars((string)($user[$nama] ?? '')) ?>
        </td>
    <?php endforeach; ?>
</tr>
<?php endforeach; ?>

</table>

<?php if (empty($users)): ?>
    <p>Belum ada data pengguna.</p>
<?php endif; ?>

<a href="dashboard.php">Kembali ke Dashboard</a>

</div>

</body>
</html>
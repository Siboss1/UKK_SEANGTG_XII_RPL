<?php
session_start();
require_once 'config.php';

// Validasi akses khusus admin
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    echo "<script>alert('Akses ditolak! Halaman ini khusus Admin.'); window.location='dashboard.php';</script>";
    exit;
}

// Proses Tambah User
if (isset($_POST['tambah_user'])) {
    $nama = trim($_POST['nama']);
    $username = trim($_POST['username']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $role = $_POST['role'];

    $stmt = $pdo->prepare("INSERT INTO users (nama, username, password, role) VALUES (?, ?, ?, ?)");
    $stmt->execute([$nama, $username, $password, $role]);
    header("Location: data_user.php");
    exit;
}

// Proses Hapus User
if (isset($_GET['hapus'])) {
    $id = $_GET['hapus'];
    $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
    $stmt->execute([$id]);
    header("Location: data_user.php");
    exit;
}

$users = $pdo->query("SELECT * FROM users ORDER BY id DESC")->fetchAll();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>CRUD User - Peminjaman Alat</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container my-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Kelola Data User</h2>
            <a href="dashboard.php" class="btn btn-secondary">Kembali ke Dashboard</a>
        </div>

        <!-- Form Tambah User -->
        <div class="card mb-4 shadow-sm">
            <div class="card-header bg-success text-white">Tambah User Baru</div>
            <div class="card-body">
                <form method="POST">
                    <div class="mb-3">
                        <label class="form-label">Nama Lengkap</label>
                        <input type="text" name="nama" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Username</label>
                        <input type="text" name="username" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Password</label>
                        <input type="password" name="password" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Role / Level</label>
                        <select name="role" class="form-select" required>
                            <option value="admin">Admin</option>
                            <option value="petugas">Petugas</option>
                            <option value="peminjam">Peminjam</option>
                        </select>
                    </div>
                    <button type="submit" name="tambah_user" class="btn btn-success w-100">Simpan User</button>
                </form>
            </div>
        </div>

        <!-- Tabel Data User -->
        <div class="card shadow-sm">
            <div class="card-header bg-dark text-white">Daftar Pengguna Sistem</div>
            <div class="card-body">
                <table class="table table-bordered table-striped align-middle">
                    <thead class="table-dark">
                        <tr>
                            <th>ID</th>
                            <th>Nama</th>
                            <th>Username</th>
                            <th>Role</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $u): ?>
                        <tr>
                            <td><?= $u['id']; ?></td>
                            <td><?= htmlspecialchars($u['nama']); ?></td>
                            <td><?= htmlspecialchars($u['username']); ?></td>
                            <td><span class="badge bg-info text-dark"><?= ucfirst($u['role']); ?></span></td>
                            <td>
                                <a href="data_user.php?hapus=<?= $u['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Hapus user ini?')">Hapus</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>
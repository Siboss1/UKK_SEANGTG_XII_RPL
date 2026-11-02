<?php
$host = 'localhost';
$db   = 'peminjaman_alat';
$user = 'root';
$pass = '';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";

$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (PDOException $e) {
    exit('Koneksi database gagal. Pastikan MySQL Laragon aktif dan database sudah dibuat.');
}

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function e($value) {
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function wajib_login() {
    if (empty($_SESSION['user'])) {
        header('Location: login.php');
        exit;
    }
}

function peran(...$roles) {
    wajib_login();

    if (!in_array($_SESSION['user']['role'], $roles, true)) {
        http_response_code(403);
        exit('Akses ditolak.');
    }
}

function rupiah($n) {
    return 'Rp' . number_format((int) $n, 0, ',', '.');
}
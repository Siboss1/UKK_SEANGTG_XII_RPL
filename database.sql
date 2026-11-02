CREATE DATABASE IF NOT EXISTS peminjaman_alat;
USE peminjaman_alat;

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama VARCHAR(100) NOT NULL,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'petugas', 'peminjam') NOT NULL DEFAULT 'peminjam'
);

CREATE TABLE kategori (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama VARCHAR(100) NOT NULL
);

CREATE TABLE alat (
    id INT AUTO_INCREMENT PRIMARY KEY,
    kategori_id INT NULL,
    nama VARCHAR(120) NOT NULL,
    stok INT NOT NULL DEFAULT 0,
    kondisi VARCHAR(50) NOT NULL DEFAULT 'Baik',
    keterangan TEXT,
    FOREIGN KEY (kategori_id) REFERENCES kategori(id)
        ON DELETE SET NULL
);

CREATE TABLE peminjaman (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    alat_id INT NOT NULL,
    jumlah INT NOT NULL,
    tanggal_pinjam DATE NOT NULL,
    tanggal_kembali DATE NOT NULL,
    status ENUM(
        'Menunggu',
        'Disetujui',
        'Ditolak',
        'Dikembalikan'
    ) NOT NULL DEFAULT 'Menunggu',
    tanggal_realisasi_kembali DATE NULL,
    denda INT NOT NULL DEFAULT 0,
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (alat_id) REFERENCES alat(id)
);

INSERT INTO users (nama, username, password, role) VALUES
('Administrator', 'admin',
'$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
'admin'),
('Petugas Sekolah', 'petugas',
'$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
'petugas'),
('Peminjam Demo', 'peminjam',
'$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
'peminjam');

INSERT INTO kategori (nama) VALUES
('Elektronik'),
('Peralatan Praktik'),
('Kabel dan Aksesori');

INSERT INTO alat (kategori_id, nama, stok, kondisi, keterangan) VALUES
(1, 'Laptop', 10, 'Baik', 'Laptop untuk praktik'),
(2, 'Obeng Set', 15, 'Baik', 'Peralatan praktik'),
(3, 'Kabel LAN', 20, 'Baik', 'Kabel jaringan');
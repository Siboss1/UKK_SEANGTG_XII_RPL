<?php
function catat_log($koneksi, $user_id, $aktivitas) {
    $aktivitas = mysqli_real_escape_string($koneksi, $aktivitas);
    mysqli_query($koneksi, "INSERT INTO log_aktifitas (user_id, aktivitas) VALUES ('$user_id', '$aktivitas')");
}
?>
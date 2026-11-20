<?php
$connect = mysqli_connect("localhost", "root", "", "db_toko");

if (!$connect) {
    die("Koneksi database gagal: " . mysqli_connect_error());
}
?>

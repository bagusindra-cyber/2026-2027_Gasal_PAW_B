<?php
// Disesuaikan dengan database "db_toko" yang sudah ada (tabel: barang,
// pelanggan, pembayaran, supplier, transaksi, transaksi_detail, user).
$connect = mysqli_connect("localhost", "root", "", "db_toko");

if (!$connect) {
    die("Koneksi database gagal: " . mysqli_connect_error());
}
?>

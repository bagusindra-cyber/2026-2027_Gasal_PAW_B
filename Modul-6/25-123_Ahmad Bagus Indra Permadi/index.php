<?php
require 'koneksi.php';

$jml_barang    = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) AS n FROM barang"))['n'];
$jml_transaksi = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) AS n FROM transaksi"))['n'];
$jml_detail    = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) AS n FROM transaksi_detail"))['n'];
$jml_pelanggan = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) AS n FROM pelanggan"))['n'];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Sistem Penjualan - Dashboard</title>
</head>
<body>

<h1>Sistem Penjualan</h1>
<p>
    <a href="index.php">Dashboard</a> |
    <a href="barang.php">Data Barang</a> |
    <a href="transaksi.php">Transaksi</a>
</p>
<hr>

<h2>Ringkasan</h2>
<table border="1" cellpadding="6" cellspacing="0">
    <tr><td>Jumlah data barang</td><td><?= $jml_barang ?></td></tr>
    <tr><td>Jumlah pelanggan</td><td><?= $jml_pelanggan ?></td></tr>
    <tr><td>Jumlah transaksi</td><td><?= $jml_transaksi ?></td></tr>
    <tr><td>Jumlah baris detail transaksi</td><td><?= $jml_detail ?></td></tr>
</table>

<h2>Menu</h2>
<p>
    <a href="barang.php">Kelola Data Barang</a> |
    <a href="transaksi.php">Kelola Transaksi (Master-Detail)</a>
</p>
<p>
    Halaman "Kelola Transaksi" menampilkan daftar transaksi (master). Klik salah satu
    transaksi untuk membuka halaman detail, di mana Anda bisa menambahkan barang yang dibeli
    pada transaksi tersebut. Total transaksi akan terhitung otomatis.
</p>

</body>
</html>

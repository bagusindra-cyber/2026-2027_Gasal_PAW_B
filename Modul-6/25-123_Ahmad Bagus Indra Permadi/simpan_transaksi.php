<?php
require 'koneksi.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: transaksi.php');
    exit;
}

$waktu_transaksi = $_POST['waktu_transaksi'] ?? '';
$pelanggan_id     = $_POST['pelanggan_id'] ?? '';
$keterangan       = trim($_POST['keterangan'] ?? '');

if ($waktu_transaksi === '' || $pelanggan_id === '') {
    header('Location: transaksi.php?gagal=' . urlencode('Tanggal dan pelanggan wajib diisi.'));
    exit;
}

$stmt = mysqli_prepare($koneksi,
    "INSERT INTO transaksi (waktu_transaksi, keterangan, total, pelanggan_id) VALUES (?, ?, 0, ?)");
mysqli_stmt_bind_param($stmt, 'sss', $waktu_transaksi, $keterangan, $pelanggan_id);
mysqli_stmt_execute($stmt);
$newId = mysqli_insert_id($koneksi);

header('Location: transaksi_detail.php?id=' . $newId . '&sukses=' . urlencode('Transaksi baru berhasil dibuat. Silakan tambahkan item barang.'));
exit;

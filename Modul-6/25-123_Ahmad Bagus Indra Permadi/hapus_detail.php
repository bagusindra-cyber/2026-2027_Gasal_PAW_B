<?php
require 'koneksi.php';
require 'fungsi_total.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: transaksi.php');
    exit;
}

$transaksi_id = isset($_POST['transaksi_id']) ? (int) $_POST['transaksi_id'] : 0;
$barang_id    = isset($_POST['barang_id']) ? (int) $_POST['barang_id'] : 0;

if ($transaksi_id > 0 && $barang_id > 0) {
    $stmt = mysqli_prepare($koneksi, "DELETE FROM transaksi_detail WHERE transaksi_id = ? AND barang_id = ?");
    mysqli_stmt_bind_param($stmt, 'ii', $transaksi_id, $barang_id);
    mysqli_stmt_execute($stmt);

    hitungUlangTotalTransaksi($koneksi, $transaksi_id);
}

header('Location: transaksi_detail.php?id=' . $transaksi_id . '&sukses=' . urlencode('Item berhasil dihapus dan total transaksi diperbarui.'));
exit;

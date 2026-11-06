<?php
require 'koneksi.php';
require 'fungsi_total.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: transaksi.php');
    exit;
}

$transaksi_id = isset($_POST['transaksi_id']) ? (int) $_POST['transaksi_id'] : 0;
$barang_id    = isset($_POST['barang_id']) ? (int) $_POST['barang_id'] : 0;
$qty          = isset($_POST['qty']) ? (int) $_POST['qty'] : 0;

if ($transaksi_id <= 0 || $barang_id <= 0 || $qty <= 0) {
    header('Location: transaksi_detail.php?id=' . $transaksi_id . '&gagal=' . urlencode('Data tidak lengkap, periksa kembali isian Anda.'));
    exit;
}
$stmtCek = mysqli_prepare($koneksi, "SELECT 1 FROM transaksi_detail WHERE transaksi_id = ? AND barang_id = ?");
mysqli_stmt_bind_param($stmtCek, 'ii', $transaksi_id, $barang_id);
mysqli_stmt_execute($stmtCek);
$sudahAda = mysqli_stmt_get_result($stmtCek)->num_rows > 0;

if ($sudahAda) {
    header('Location: transaksi_detail.php?id=' . $transaksi_id . '&gagal=' . urlencode('Barang ini sudah ada pada transaksi tersebut, tidak bisa ditambahkan dua kali.'));
    exit;
}

$stmtHarga = mysqli_prepare($koneksi, "SELECT harga, nama_barang FROM barang WHERE id = ?");
mysqli_stmt_bind_param($stmtHarga, 'i', $barang_id);
mysqli_stmt_execute($stmtHarga);
$barang = mysqli_fetch_assoc(mysqli_stmt_get_result($stmtHarga));

if (!$barang) {
    header('Location: transaksi_detail.php?id=' . $transaksi_id . '&gagal=' . urlencode('Barang tidak ditemukan.'));
    exit;
}
$harga = $barang['harga'];

try {
    $stmtInsert = mysqli_prepare($koneksi,
        "INSERT INTO transaksi_detail (transaksi_id, barang_id, harga, qty) VALUES (?, ?, ?, ?)");
    mysqli_stmt_bind_param($stmtInsert, 'iiii', $transaksi_id, $barang_id, $harga, $qty);
    mysqli_stmt_execute($stmtInsert);

    hitungUlangTotalTransaksi($koneksi, $transaksi_id);

    $pesan = 'Item "' . $barang['nama_barang'] . '" berhasil ditambahkan dan total transaksi sudah diperbarui.';
    header('Location: transaksi_detail.php?id=' . $transaksi_id . '&sukses=' . urlencode($pesan));
} catch (mysqli_sql_exception $e) {
    header('Location: transaksi_detail.php?id=' . $transaksi_id . '&gagal=' . urlencode('Barang ini sudah ada pada transaksi tersebut.'));
}
exit;

<?php
require 'koneksi.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_POST['id'])) {
    header('Location: barang.php');
    exit;
}

$id = (int) $_POST['id'];

$stmtNama = mysqli_prepare($koneksi, "SELECT nama_barang FROM barang WHERE id = ?");
mysqli_stmt_bind_param($stmtNama, 'i', $id);
mysqli_stmt_execute($stmtNama);
$namaBarang = mysqli_fetch_assoc(mysqli_stmt_get_result($stmtNama))['nama_barang'] ?? 'Barang';

$stmtCek = mysqli_prepare($koneksi, "SELECT COUNT(*) AS jml FROM transaksi_detail WHERE barang_id = ?");
mysqli_stmt_bind_param($stmtCek, 'i', $id);
mysqli_stmt_execute($stmtCek);
$jmlDipakai = mysqli_fetch_assoc(mysqli_stmt_get_result($stmtCek))['jml'];

if ($jmlDipakai > 0) {
    $pesan = "Barang \"$namaBarang\" tidak dapat dihapus karena masih digunakan pada $jmlDipakai transaksi.";
    header('Location: barang.php?gagal=' . urlencode($pesan));
    exit;
}

try {
    $stmtHapus = mysqli_prepare($koneksi, "DELETE FROM barang WHERE id = ?");
    mysqli_stmt_bind_param($stmtHapus, 'i', $id);
    mysqli_stmt_execute($stmtHapus);

    if (mysqli_stmt_affected_rows($stmtHapus) > 0) {
        $pesan = "Barang \"$namaBarang\" berhasil dihapus.";
        header('Location: barang.php?sukses=' . urlencode($pesan));
    } else {
        header('Location: barang.php?gagal=' . urlencode('Barang tidak ditemukan.'));
    }
} catch (mysqli_sql_exception $e) {
    $pesan = "Barang \"$namaBarang\" tidak dapat dihapus karena masih terkait dengan data transaksi.";
    header('Location: barang.php?gagal=' . urlencode($pesan));
}
exit;

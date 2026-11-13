<?php
include 'koneksi.php';

header("Content-Type: application/vnd.ms-excel");
header("Content-Disposition: attachment; filename=Rekap_Laporan_Penjualan.xls");
header("Pragma: no-cache");
header("Expires: 0");

$mulai   = $_GET['mulai'] ?? '';
$selesai = $_GET['selesai'] ?? '';

if (!$mulai || !$selesai) {
    die("Rentang tanggal tidak valid.");
}

$mulai_escaped   = mysqli_real_escape_string($koneksi, $mulai);
$selesai_escaped = mysqli_real_escape_string($koneksi, $selesai);

$query = "SELECT DATE(waktu_transaksi) AS tgl, SUM(total) AS total_harian
          FROM transaksi 
          WHERE DATE(waktu_transaksi) BETWEEN '$mulai_escaped' AND '$selesai_escaped'
          GROUP BY DATE(waktu_transaksi) 
          ORDER BY tgl ASC";
$result = mysqli_query($koneksi, $query);

$query_total = "SELECT COUNT(DISTINCT pelanggan_id) AS jml_pelanggan, SUM(total) AS total_semua
                FROM transaksi
                WHERE DATE(waktu_transaksi) BETWEEN '$mulai_escaped' AND '$selesai_escaped'";
$row_total = mysqli_fetch_assoc(mysqli_query($koneksi, $query_total));
?>

<table border="1">
    <thead>
        <tr>
            <th colspan="3">Rekap Laporan Penjualan (<?= $mulai ?> s/d <?= $selesai ?>)</th>
        </tr>
        <tr>
            <th>No</th>
            <th>Total Pendapatan</th>
            <th>Tanggal Transaksi</th>
        </tr>
    </thead>
    <tbody>
        <?php $no = 1; while ($baris = mysqli_fetch_assoc($result)): ?>
        <tr>
            <td><?= $no++ ?></td>
            <td><?= $baris['total_harian'] ?></td>
            <td><?= $baris['tgl'] ?></td>
        </tr>
        <?php endwhile; ?>
        <tr>
            <th colspan="2">Jumlah Pelanggan Unik</th>
            <th><?= $row_total['jml_pelanggan'] ?? 0 ?> Orang</th>
        </tr>
        <tr>
            <th colspan="2">Total Pendapatan Keseluruhan</th>
            <th>Rp <?= number_format($row_total['total_semua'] ?? 0, 0, ',', '.') ?></th>
        </tr>
    </tbody>
</table>
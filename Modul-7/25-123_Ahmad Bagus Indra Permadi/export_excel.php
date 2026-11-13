<?php
// Manipulasi HTTP Headers untuk Export ke Excel
header("Content-Type: application/vnd.ms-excel");
header("Content-Disposition: attachment; filename=Rekap_Laporan_Penjualan.xls");
header("Pragma: no-cache");
header("Expires: 0");

// Koneksi ke Database db_toko
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "db_toko"; 
$koneksi = mysqli_connect($servername, $username, $password, $dbname);

// Mengambil parameter GET dari URL
$mulai = $_GET['mulai'] ?? '';
$selesai = $_GET['selesai'] ?? '';

if (!$mulai || !$selesai) {
    die("Pilih rentang tanggal terlebih dahulu.");
}

$mulai_escaped = mysqli_real_escape_string($koneksi, $mulai);
$selesai_escaped = mysqli_real_escape_string($koneksi, $selesai);

// Query Tabel Harian
$query = "SELECT DATE(waktu_transaksi) AS tgl, SUM(total) AS total_harian
          FROM transaksi 
          WHERE DATE(waktu_transaksi) BETWEEN '$mulai_escaped' AND '$selesai_escaped'
          GROUP BY DATE(waktu_transaksi) 
          ORDER BY tgl ASC";
$result = mysqli_query($koneksi, $query);

// Query Kumulatif
$query_total = "SELECT COUNT(DISTINCT pelanggan_id) AS jml_pelanggan, SUM(total) AS total_semua
                FROM transaksi
                WHERE DATE(waktu_transaksi) BETWEEN '$mulai_escaped' AND '$selesai_escaped'";
$result_total = mysqli_query($koneksi, $query_total);
$row_total = mysqli_fetch_assoc($result_total);
?>

<!-- Format Output Tabel yang akan diekspor menjadi format Excel -->
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
        <?php
        $no = 1;
        while ($baris = mysqli_fetch_assoc($result)) {
            echo "<tr>";
            echo "<td>" . $no++ . "</td>";
            // Tidak menggunakan number_format agar di Excel terbaca sebagai angka yang bisa di-sum
            echo "<td>" . $baris['total_harian'] . "</td>"; 
            echo "<td>" . $baris['tgl'] . "</td>";
            echo "</tr>";
        }
        ?>
        <tr>
            <th colspan="2">Jumlah Pelanggan Unik</th>
            <th><?= $row_total['jml_pelanggan'] ?> Orang</th>
        </tr>
        <tr>
            <th colspan="2">Total Pendapatan Keseluruhan</th>
            <th>Rp <?= number_format($row_total['total_semua'], 0, ',', '.') ?></th>
        </tr>
    </tbody>
</table>
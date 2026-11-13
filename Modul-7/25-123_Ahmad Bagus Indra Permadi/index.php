<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Penjualan Toko</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        table { border-collapse: collapse; width: 100%; margin-top: 15px; }
        th, td { border: 1px solid #000; padding: 8px; text-align: left; }
        .card { border: 1px solid #ccc; padding: 15px; margin-top: 20px; background: #f9f9f9; border-radius: 5px; }
        .btn { padding: 8px 12px; cursor: pointer; margin-right: 5px; margin-top: 10px; }
        @media print {
            .no-print { display: none !important; }
        }
    </style>
</head>
<body>

    <div class="no-print">
        <h2>Filter Laporan Penjualan</h2>
        <form method="GET">
            Mulai: <input type="date" name="tanggal_mulai" value="<?= $_GET['tanggal_mulai'] ?? '' ?>" required>
            Sampai: <input type="date" name="tanggal_selesai" value="<?= $_GET['tanggal_selesai'] ?? '' ?>" required>
            <button type="submit" class="btn">Tampilkan</button>
        </form>

        <!-- Tombol Cetak & Excel -->
        <button onclick="window.print()" class="btn">Cetak</button>
        <?php if (!empty($_GET['tanggal_mulai']) && !empty($_GET['tanggal_selesai'])): ?>
            <a href="export_excel.php?mulai=<?= $_GET['tanggal_mulai'] ?>&selesai=<?= $_GET['tanggal_selesai'] ?>">
                <button class="btn">Excel</button>
            </a>
        <?php endif; ?>
        <hr>
    </div>

    <?php
    $servername = "localhost";
    $username = "root";
    $password = "";
    $dbname = "db_toko"; 
    $koneksi = mysqli_connect($servername, $username, $password, $dbname);

    if (mysqli_connect_error()) {
        die("Koneksi database gagal: " . mysqli_connect_error());
    }

    $labels = [];
    $data_total = [];
    $rekap = [];
    $total_pendapatan = 0;
    $total_pelanggan = 0;

    if (!empty($_GET['tanggal_mulai']) && !empty($_GET['tanggal_selesai'])) {
        $mulai = mysqli_real_escape_string($koneksi, $_GET['tanggal_mulai']);
        $selesai = mysqli_real_escape_string($koneksi, $_GET['tanggal_selesai']);

        // 1. Query Grafik dan Tabel Harian (Menjumlahkan total per hari)
        $query = "SELECT DATE(waktu_transaksi) AS tgl, SUM(total) AS total_harian
                  FROM transaksi 
                  WHERE DATE(waktu_transaksi) BETWEEN '$mulai' AND '$selesai'
                  GROUP BY DATE(waktu_transaksi) 
                  ORDER BY tgl ASC";
        
        $result = mysqli_query($koneksi, $query);
        while ($row = mysqli_fetch_assoc($result)) {
            $labels[] = $row['tgl'];
            $data_total[] = $row['total_harian'];
            $rekap[] = $row;
        }
        $query_total = "SELECT COUNT(DISTINCT pelanggan_id) AS jml_pelanggan, SUM(total) AS total_semua
                        FROM transaksi
                        WHERE DATE(waktu_transaksi) BETWEEN '$mulai' AND '$selesai'";
        
        $result_total = mysqli_query($koneksi, $query_total);
        $row_total = mysqli_fetch_assoc($result_total);
        $total_pelanggan = $row_total['jml_pelanggan'] ?? 0;
        $total_pendapatan = $row_total['total_semua'] ?? 0;
    }
    ?>

    <?php if (!empty($labels)): ?>
        <h2>Grafik Penjualan</h2>
        <!-- Tempat merender Chart.js -->
        <div style="width: 80%; margin: auto;">
            <canvas id="grafikPenjualan"></canvas>
        </div>
        
        <script>
            const ctx = document.getElementById('grafikPenjualan').getContext('2d');
            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: <?= json_encode($labels) ?>,
                    datasets: [{
                        label: 'Total Pendapatan (Rp)',
                        data: <?= json_encode($data_total) ?>,
                        backgroundColor: 'rgba(54, 162, 235, 0.6)',
                        borderColor: 'rgba(54, 162, 235, 1)',
                        borderWidth: 1
                    }]
                },
                options: {
                    scales: { y: { beginAtZero: true } }
                }
            });
        </script>

        <h2>Tabel Rekapitulasi Data Penjualan Harian</h2>
        <table>
            <thead>
                <tr>
                    <th>No</th>
                    <th>Total Pendapatan</th>
                    <th>Tanggal Transaksi</th>
                </tr>
            </thead>
            <tbody>
                <?php $no = 1; foreach ($rekap as $baris): ?>
                <tr>
                    <td><?= $no++ ?></td>
                    <td>Rp <?= number_format($baris['total_harian'], 0, ',', '.') ?></td>
                    <td><?= $baris['tgl'] ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <div class="card">
            <h3>Ringkasan Informasi</h3>
            <p><b>Jumlah Pelanggan Unik:</b> <?= $total_pelanggan ?> Orang</p>
            <p><b>Total Pendapatan Keseluruhan:</b> Rp <?= number_format($total_pendapatan, 0, ',', '.') ?></p>
        </div>
        
    <?php elseif (isset($_GET['tanggal_mulai'])): ?>
        <p style="color: red; margin-top: 20px;">Tidak ada transaksi pada rentang tanggal tersebut.</p>
    <?php endif; ?>

</body>
</html>
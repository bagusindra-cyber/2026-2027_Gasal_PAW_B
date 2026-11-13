<?php include 'koneksi.php'; ?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Soal 4 - Reporting Lengkap (Print & Excel)</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        table { border-collapse: collapse; width: 100%; margin-top: 20px; }
        th, td { border: 1px solid #000; padding: 8px; text-align: left; }
        .card { border: 1px solid #ccc; padding: 15px; margin-top: 20px; background: #f4f4f4; border-radius: 5px; }
        .btn { padding: 8px 14px; cursor: pointer; margin-top: 10px; margin-right: 5px; }

        /* Menyembunyikan filter dan tombol saat dicetak */
        @media print {
            .no-print { display: none !important; }
        }
    </style>
</head>
<body>

    <div class="no-print">
        <h2>Filter Laporan Penjualan</h2>
        <form method="GET">
            Tanggal Mulai: <input type="date" name="tanggal_mulai" value="<?= $_GET['tanggal_mulai'] ?? '' ?>" required>
            Tanggal Selesai: <input type="date" name="tanggal_selesai" value="<?= $_GET['tanggal_selesai'] ?? '' ?>" required>
            <button type="submit" class="btn">Tampilkan</button>
        </form>

        <button onclick="window.print()" class="btn">Cetak</button>
        <?php if (!empty($_GET['tanggal_mulai']) && !empty($_GET['tanggal_selesai'])): ?>
            <a href="export_excel.php?mulai=<?= $_GET['tanggal_mulai'] ?>&selesai=<?= $_GET['tanggal_selesai'] ?>">
                <button type="button" class="btn">Excel</button>
            </a>
        <?php endif; ?>
        <hr>
    </div>

    <?php
    $labels = [];
    $data_total = [];
    $rekap = [];
    $total_pendapatan = 0;
    $total_pelanggan = 0;

    if (!empty($_GET['tanggal_mulai']) && !empty($_GET['tanggal_selesai'])) {
        $mulai   = mysqli_real_escape_string($koneksi, $_GET['tanggal_mulai']);
        $selesai = mysqli_real_escape_string($koneksi, $_GET['tanggal_selesai']);

        $query = "SELECT DATE(waktu_transaksi) AS tgl, SUM(total) AS total_harian
                  FROM transaksi 
                  WHERE DATE(waktu_transaksi) BETWEEN '$mulai' AND '$selesai'
                  GROUP BY DATE(waktu_transaksi) 
                  ORDER BY tgl ASC";
        $result = mysqli_query($koneksi, $query);

        while ($row = mysqli_fetch_assoc($result)) {
            $labels[]     = $row['tgl'];
            $data_total[] = $row['total_harian'];
            $rekap[]      = $row;
        }

        $query_total = "SELECT COUNT(DISTINCT pelanggan_id) AS jml_pelanggan, SUM(total) AS total_semua
                        FROM transaksi
                        WHERE DATE(waktu_transaksi) BETWEEN '$mulai' AND '$selesai'";
        $row_total = mysqli_fetch_assoc(mysqli_query($koneksi, $query_total));
        $total_pelanggan  = $row_total['jml_pelanggan'] ?? 0;
        $total_pendapatan = $row_total['total_semua'] ?? 0;
    }
    ?>

    <?php if (!empty($labels)): ?>
        <h2>Rekap Laporan Penjualan (<?= $_GET['tanggal_mulai'] ?> s/d <?= $_GET['tanggal_selesai'] ?>)</h2>
        
        <div style="width: 75%;">
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
                options: { scales: { y: { beginAtZero: true } } }
            });
        </script>

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
            <h4>Ringkasan Informasi</h4>
            <p><b>Jumlah Pelanggan Unik:</b> <?= $total_pelanggan ?> Orang</p>
            <p><b>Total Pendapatan Keseluruhan:</b> Rp <?= number_format($total_pendapatan, 0, ',', '.') ?></p>
        </div>
    <?php elseif (isset($_GET['tanggal_mulai'])): ?>
        <p style="color: red;">Tidak ada data transaksi pada rentang tanggal tersebut.</p>
    <?php endif; ?>
</body>
</html>
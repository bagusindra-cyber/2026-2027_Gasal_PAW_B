<?php include 'koneksi.php'; ?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Soal 2 - Grafik Batang Penjualan</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
    </style>
</head>
<body>
    <h2>Filter Grafik Penjualan</h2>
    <form method="GET">
        Tanggal Mulai: <input type="date" name="tanggal_mulai" value="<?= $_GET['tanggal_mulai'] ?? '' ?>" required>
        Tanggal Selesai: <input type="date" name="tanggal_selesai" value="<?= $_GET['tanggal_selesai'] ?? '' ?>" required>
        <button type="submit">Tampilkan</button>
    </form>

    <?php
    $labels = [];
    $data_total = [];

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
        }
    }
    ?>

    <?php if (!empty($labels)): ?>
        <h3>Grafik Total Pendapatan Harian</h3>
        <div style="width: 75%; margin-top: 20px;">
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
    <?php elseif (isset($_GET['tanggal_mulai'])): ?>
        <p style="color: red;">Tidak ada data untuk ditampilkan pada grafik.</p>
    <?php endif; ?>
</body>
</html>
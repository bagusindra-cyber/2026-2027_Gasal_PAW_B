<?php include 'koneksi.php'; ?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Soal 1 - Filter Laporan Penjualan</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        table { border-collapse: collapse; width: 100%; margin-top: 15px; }
        th, td { border: 1px solid #000; padding: 8px; text-align: left; }
    </style>
</head>
<body>
    <h2>Filter Laporan Penjualan</h2>
    <form method="GET">
        Tanggal Mulai: <input type="date" name="tanggal_mulai" value="<?= $_GET['tanggal_mulai'] ?? '' ?>" required>
        Tanggal Selesai: <input type="date" name="tanggal_selesai" value="<?= $_GET['tanggal_selesai'] ?? '' ?>" required>
        <button type="submit">Tampilkan</button>
    </form>

    <?php
    if (!empty($_GET['tanggal_mulai']) && !empty($_GET['tanggal_selesai'])) {
        $mulai   = mysqli_real_escape_string($koneksi, $_GET['tanggal_mulai']);
        $selesai = mysqli_real_escape_string($koneksi, $_GET['tanggal_selesai']);

        $query = "SELECT * FROM transaksi 
                  WHERE DATE(waktu_transaksi) BETWEEN '$mulai' AND '$selesai' 
                  ORDER BY waktu_transaksi ASC";
        $result = mysqli_query($koneksi, $query);
        ?>
        <h3>Data Transaksi (<?= $mulai ?> s/d <?= $selesai ?>)</h3>
        <table>
            <thead>
                <tr>
                    <th>ID Transaksi</th>
                    <th>Waktu Transaksi</th>
                    <th>Keterangan</th>
                    <th>Total</th>
                    <th>ID Pelanggan</th>
                </tr>
            </thead>
            <tbody>
                <?php if (mysqli_num_rows($result) > 0): ?>
                    <?php while ($row = mysqli_fetch_assoc($result)): ?>
                    <tr>
                        <td><?= $row['id'] ?></td>
                        <td><?= $row['waktu_transaksi'] ?></td>
                        <td><?= $row['keterangan'] ?></td>
                        <td>Rp <?= number_format($row['total'], 0, ',', '.') ?></td>
                        <td><?= $row['pelanggan_id'] ?></td>
                    </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="5" style="text-align:center;">Tidak ada data transaksi.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    <?php } ?>
</body>
</html>
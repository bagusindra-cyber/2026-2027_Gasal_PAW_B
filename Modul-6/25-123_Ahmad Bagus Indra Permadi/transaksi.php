<?php
require 'koneksi.php';

$query = "
    SELECT t.id, t.waktu_transaksi, t.keterangan, t.total,
           p.nama AS nama_pelanggan,
           (SELECT COUNT(*) FROM transaksi_detail td WHERE td.transaksi_id = t.id) AS jml_item
    FROM transaksi t
    LEFT JOIN pelanggan p ON p.id = t.pelanggan_id
    ORDER BY t.id DESC
";
$result = mysqli_query($koneksi, $query);

$pelangganList = mysqli_query($koneksi, "SELECT id, nama FROM pelanggan ORDER BY nama ASC");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Transaksi (Master)</title>
</head>
<body>

<h1>Sistem Penjualan</h1>
<p>
    <a href="index.php">Dashboard</a> |
    <a href="barang.php">Data Barang</a> |
    <a href="transaksi.php">Transaksi</a>
</p>
<hr>

<p><a href="index.php">Dashboard</a> / Transaksi</p>

<?php if (!empty($_GET['sukses'])): ?>
    <p><strong>Sukses:</strong> <?= htmlspecialchars($_GET['sukses']) ?></p>
<?php endif; ?>
<?php if (!empty($_GET['gagal'])): ?>
    <p><strong>Gagal:</strong> <?= htmlspecialchars($_GET['gagal']) ?></p>
<?php endif; ?>

<h2>Tambah Transaksi Baru (Master)</h2>
<form method="post" action="simpan_transaksi.php">
    <table cellpadding="4">
        <tr>
            <td>Tanggal</td>
            <td><input type="date" name="waktu_transaksi" value="<?= date('Y-m-d') ?>" required></td>
        </tr>
        <tr>
            <td>Pelanggan</td>
            <td>
                <select name="pelanggan_id" required>
                    <option value="">-- pilih pelanggan --</option>
                    <?php while ($p = mysqli_fetch_assoc($pelangganList)): ?>
                        <option value="<?= htmlspecialchars($p['id']) ?>"><?= htmlspecialchars($p['nama']) ?> (<?= htmlspecialchars($p['id']) ?>)</option>
                    <?php endwhile; ?>
                </select>
            </td>
        </tr>
        <tr>
            <td>Keterangan</td>
            <td><input type="text" name="keterangan" placeholder="mis. Pembelian alat tulis"></td>
        </tr>
        <tr>
            <td></td>
            <td><button type="submit">Simpan Transaksi</button></td>
        </tr>
    </table>
    <p>Total akan terisi 0 dulu, lalu terhitung otomatis setelah Anda menambahkan item pada halaman detail.</p>
</form>

<h2>Daftar Transaksi</h2>
<table border="1" cellpadding="6" cellspacing="0">
    <thead>
        <tr>
            <th>ID</th>
            <th>Tanggal</th>
            <th>Pelanggan</th>
            <th>Keterangan</th>
            <th>Jml Item</th>
            <th>Total</th>
            <th>Aksi</th>
        </tr>
    </thead>
    <tbody>
    <?php while ($row = mysqli_fetch_assoc($result)): ?>
        <tr>
            <td>#<?= (int)$row['id'] ?></td>
            <td><?= htmlspecialchars($row['waktu_transaksi']) ?></td>
            <td><?= htmlspecialchars($row['nama_pelanggan'] ?? '-') ?></td>
            <td><?= htmlspecialchars($row['keterangan']) ?></td>
            <td><?= (int)$row['jml_item'] ?></td>
            <td>Rp<?= number_format($row['total'], 0, ',', '.') ?></td>
            <td><a href="transaksi_detail.php?id=<?= (int)$row['id'] ?>">Lihat / Isi Detail</a></td>
        </tr>
    <?php endwhile; ?>
    </tbody>
</table>

</body>
</html>

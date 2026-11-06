<?php
require 'koneksi.php';

$query = "
    SELECT b.id, b.kode_barang, b.nama_barang, b.harga, b.stok,
           s.nama AS nama_supplier,
           (SELECT COUNT(*) FROM transaksi_detail td WHERE td.barang_id = b.id) AS jml_dipakai
    FROM barang b
    LEFT JOIN supplier s ON s.id = b.supplier_id
    ORDER BY b.id ASC
";
$result = mysqli_query($koneksi, $query);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Data Barang</title>
</head>
<body>

<h1>Sistem Penjualan</h1>
<p>
    <a href="index.php">Dashboard</a> |
    <a href="barang.php">Data Barang</a> |
    <a href="transaksi.php">Transaksi</a>
</p>
<hr>

<p><a href="index.php">Dashboard</a> / Data Barang</p>

<?php if (!empty($_GET['sukses'])): ?>
    <p><strong>Sukses:</strong> <?= htmlspecialchars($_GET['sukses']) ?></p>
<?php endif; ?>
<?php if (!empty($_GET['gagal'])): ?>
    <p><strong>Gagal:</strong> <?= htmlspecialchars($_GET['gagal']) ?></p>
<?php endif; ?>

<h2>Daftar Barang</h2>
<table border="1" cellpadding="6" cellspacing="0">
    <thead>
        <tr>
            <th>Kode</th>
            <th>Nama Barang</th>
            <th>Harga</th>
            <th>Stok</th>
            <th>Supplier</th>
            <th>Dipakai di Transaksi</th>
            <th>Aksi</th>
        </tr>
    </thead>
    <tbody>
    <?php while ($row = mysqli_fetch_assoc($result)): ?>
        <tr>
            <td><?= htmlspecialchars($row['kode_barang']) ?></td>
            <td><?= htmlspecialchars($row['nama_barang']) ?></td>
            <td>Rp<?= number_format($row['harga'], 0, ',', '.') ?></td>
            <td><?= (int)$row['stok'] ?></td>
            <td><?= htmlspecialchars($row['nama_supplier'] ?? '-') ?></td>
            <td>
                <?php if ($row['jml_dipakai'] > 0): ?>
                    <?= (int)$row['jml_dipakai'] ?>x dipakai
                <?php else: ?>
                    belum dipakai
                <?php endif; ?>
            </td>
            <td>
                <form method="post" action="hapus_barang.php"
                      onsubmit="return confirm('Yakin ingin menghapus barang &quot;<?= htmlspecialchars(addslashes($row['nama_barang'])) ?>&quot;?');">
                    <input type="hidden" name="id" value="<?= (int)$row['id'] ?>">
                    <button type="submit">Hapus</button>
                </form>
            </td>
        </tr>
    <?php endwhile; ?>
    </tbody>
</table>

<p>
    Catatan: tombol "Hapus" akan ditolak oleh sistem apabila barang tersebut sudah pernah
    dipakai pada tabel transaksi_detail (ditandai "x dipakai" di atas).
</p>

</body>
</html>

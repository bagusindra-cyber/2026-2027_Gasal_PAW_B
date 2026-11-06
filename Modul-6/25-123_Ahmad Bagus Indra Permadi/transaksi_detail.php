<?php
require 'koneksi.php';

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

$stmtHeader = mysqli_prepare($koneksi, "
    SELECT t.id, t.waktu_transaksi, t.keterangan, t.total,
           p.nama AS nama_pelanggan, p.id AS pelanggan_id
    FROM transaksi t
    LEFT JOIN pelanggan p ON p.id = t.pelanggan_id
    WHERE t.id = ?
");
mysqli_stmt_bind_param($stmtHeader, 'i', $id);
mysqli_stmt_execute($stmtHeader);
$transaksi = mysqli_fetch_assoc(mysqli_stmt_get_result($stmtHeader));

if (!$transaksi) {
    header('Location: transaksi.php');
    exit;
}
$stmtDetail = mysqli_prepare($koneksi, "
    SELECT td.barang_id, td.harga, td.qty, b.kode_barang, b.nama_barang
    FROM transaksi_detail td
    JOIN barang b ON b.id = td.barang_id
    WHERE td.transaksi_id = ?
    ORDER BY b.nama_barang ASC
");
mysqli_stmt_bind_param($stmtDetail, 'i', $id);
mysqli_stmt_execute($stmtDetail);
$detailItems = mysqli_stmt_get_result($stmtDetail);

$stmtBarang = mysqli_prepare($koneksi, "
    SELECT id, kode_barang, nama_barang, harga, stok
    FROM barang
    WHERE id NOT IN (
        SELECT barang_id FROM transaksi_detail WHERE transaksi_id = ?
    )
    ORDER BY nama_barang ASC
");
mysqli_stmt_bind_param($stmtBarang, 'i', $id);
mysqli_stmt_execute($stmtBarang);
$barangTersedia = mysqli_stmt_get_result($stmtBarang);
$jmlBarangTersedia = mysqli_num_rows($barangTersedia);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Detail Transaksi #<?= (int)$transaksi['id'] ?></title>
</head>
<body>

<h1>Sistem Penjualan</h1>
<p>
    <a href="index.php">Dashboard</a> |
    <a href="barang.php">Data Barang</a> |
    <a href="transaksi.php">Transaksi</a>
</p>
<hr>

<p><a href="index.php">Dashboard</a> / <a href="transaksi.php">Transaksi</a> / Detail #<?= (int)$transaksi['id'] ?></p>

<?php if (!empty($_GET['sukses'])): ?>
    <p><strong>Sukses:</strong> <?= htmlspecialchars($_GET['sukses']) ?></p>
<?php endif; ?>
<?php if (!empty($_GET['gagal'])): ?>
    <p><strong>Gagal:</strong> <?= htmlspecialchars($_GET['gagal']) ?></p>
<?php endif; ?>

<h2>Transaksi #<?= (int)$transaksi['id'] ?> (Master)</h2>
<table border="1" cellpadding="6" cellspacing="0">
    <tr><td>Tanggal</td><td><?= htmlspecialchars($transaksi['waktu_transaksi']) ?></td></tr>
    <tr><td>Pelanggan</td><td><?= htmlspecialchars($transaksi['nama_pelanggan'] ?? '-') ?> (<?= htmlspecialchars($transaksi['pelanggan_id'] ?? '-') ?>)</td></tr>
    <tr><td>Keterangan</td><td><?= htmlspecialchars($transaksi['keterangan']) ?></td></tr>
    <tr><td>Total saat ini</td><td><strong>Rp<?= number_format($transaksi['total'], 0, ',', '.') ?></strong></td></tr>
</table>

<h2>Item Barang (Detail)</h2>
<table border="1" cellpadding="6" cellspacing="0">
    <thead>
        <tr>
            <th>Kode</th>
            <th>Nama Barang</th>
            <th>Harga</th>
            <th>Qty</th>
            <th>Subtotal</th>
            <th>Aksi</th>
        </tr>
    </thead>
    <tbody>
    <?php
    $grandTotal = 0;
    $jmlDetail = mysqli_num_rows($detailItems);
    while ($d = mysqli_fetch_assoc($detailItems)):
        $subtotal = $d['harga'] * $d['qty'];
        $grandTotal += $subtotal;
    ?>
        <tr>
            <td><?= htmlspecialchars($d['kode_barang']) ?></td>
            <td><?= htmlspecialchars($d['nama_barang']) ?></td>
            <td>Rp<?= number_format($d['harga'], 0, ',', '.') ?></td>
            <td><?= (int)$d['qty'] ?></td>
            <td>Rp<?= number_format($subtotal, 0, ',', '.') ?></td>
            <td>
                <form method="post" action="hapus_detail.php"
                      onsubmit="return confirm('Hapus item ini dari transaksi?');">
                    <input type="hidden" name="transaksi_id" value="<?= (int)$transaksi['id'] ?>">
                    <input type="hidden" name="barang_id" value="<?= (int)$d['barang_id'] ?>">
                    <button type="submit">Hapus</button>
                </form>
            </td>
        </tr>
    <?php endwhile; ?>
    <?php if ($jmlDetail === 0): ?>
        <tr><td colspan="6">Belum ada item pada transaksi ini.</td></tr>
    <?php endif; ?>
    </tbody>
    <tfoot>
        <tr>
            <td colspan="4"><strong>TOTAL</strong></td>
            <td><strong>Rp<?= number_format($transaksi['total'], 0, ',', '.') ?></strong></td>
            <td></td>
        </tr>
    </tfoot>
</table>
<p>
    Kolom TOTAL di atas diambil langsung dari tabel transaksi, yang diperbarui
    otomatis (recalculate) setiap kali item ditambah/dihapus dari transaksi_detail.
</p>

<h2>Tambah Item Barang</h2>
<?php if ($jmlBarangTersedia === 0): ?>
    <p><strong>Semua barang yang tersedia sudah ditambahkan ke transaksi ini.</strong></p>
<?php else: ?>
    <form method="post" action="simpan_detail.php">
        <input type="hidden" name="transaksi_id" value="<?= (int)$transaksi['id'] ?>">
        <table cellpadding="4">
            <tr>
                <td>Barang</td>
                <td>
                    <select name="barang_id" required>
                        <option value="">-- pilih barang --</option>
                        <?php while ($b = mysqli_fetch_assoc($barangTersedia)): ?>
                            <option value="<?= (int)$b['id'] ?>">
                                <?= htmlspecialchars($b['kode_barang'] . ' - ' . $b['nama_barang']) ?>
                                (Rp<?= number_format($b['harga'], 0, ',', '.') ?>, stok <?= (int)$b['stok'] ?>)
                            </option>
                        <?php endwhile; ?>
                    </select>
                </td>
            </tr>
            <tr>
                <td>Qty</td>
                <td><input type="number" name="qty" min="1" value="1" required></td>
            </tr>
            <tr>
                <td></td>
                <td><button type="submit">Tambah ke Transaksi</button></td>
            </tr>
        </table>
    </form>
    <p>
        Dropdown di atas hanya menampilkan barang yang BELUM ada pada transaksi ini
        (<?= $jmlBarangTersedia ?> dari <?= $jmlBarangTersedia + $jmlDetail ?> barang tersedia) —
        barang yang sudah dipilih otomatis disembunyikan agar tidak bisa diinput dua kali.
    </p>
<?php endif; ?>

</body>
</html>

<?php
if (!defined('APP_ENTRY')) {
    http_response_code(404);
    exit();
}

$transactionId = filter_var($_GET['id'] ?? null, FILTER_VALIDATE_INT);
$selectedTransaction = null;
$details = null;
$availableProducts = null;

$transactionList = mysqli_query(
    $connect,
    'SELECT t.id, t.waktu_transaksi, t.keterangan, t.total, p.nama AS pelanggan,
            (SELECT COUNT(*) FROM transaksi_detail td WHERE td.transaksi_id = t.id) AS jumlah_item
     FROM transaksi t
     LEFT JOIN pelanggan p ON p.id = t.pelanggan_id
     ORDER BY t.id DESC'
);
$customers = mysqli_query($connect, 'SELECT id, nama FROM pelanggan ORDER BY nama');

if ($transactionId) {
    $statement = runStatement(
        $connect,
        'SELECT t.id, t.waktu_transaksi, t.keterangan, t.total, t.pelanggan_id, p.nama AS pelanggan
         FROM transaksi t LEFT JOIN pelanggan p ON p.id = t.pelanggan_id WHERE t.id = ?',
        'i',
        [$transactionId]
    );
    $selectedTransaction = mysqli_fetch_assoc(mysqli_stmt_get_result($statement));
    if ($selectedTransaction) {
        $detailsStatement = runStatement(
            $connect,
            'SELECT td.barang_id, td.harga, td.qty, b.kode_barang, b.nama_barang
             FROM transaksi_detail td JOIN barang b ON b.id = td.barang_id
             WHERE td.transaksi_id = ? ORDER BY b.nama_barang',
            'i',
            [$transactionId]
        );
        $details = mysqli_stmt_get_result($detailsStatement);
        $availableStatement = runStatement(
            $connect,
            'SELECT b.id, b.kode_barang, b.nama_barang, b.harga, b.stok
             FROM barang b
             WHERE NOT EXISTS (
                 SELECT 1 FROM transaksi_detail td
                 WHERE td.transaksi_id = ? AND td.barang_id = b.id
             )
             ORDER BY b.nama_barang',
            'i',
            [$transactionId]
        );
        $availableProducts = mysqli_stmt_get_result($availableStatement);
    }
}
?>
<h2>Transaksi</h2>

<?php if ($transactionId && !$selectedTransaction) { ?>
    <p class="flash error">Transaksi tidak ditemukan.</p>
<?php } elseif ($selectedTransaction) { ?>
    <p><a href="index.php?page=transaksi">Kembali ke daftar transaksi</a></p>
    <section class="section">
        <h3>Transaksi #<?php echo h($selectedTransaction['id']); ?></h3>
        <p><strong>Tanggal:</strong> <?php echo h($selectedTransaction['waktu_transaksi']); ?></p>
        <p><strong>Pelanggan:</strong> <?php echo h($selectedTransaction['pelanggan'] ?? '-'); ?></p>
        <p><strong>Keterangan:</strong> <?php echo h($selectedTransaction['keterangan'] ?? '-'); ?></p>
        <p><strong>Total:</strong> Rp <?php echo number_format((float) $selectedTransaction['total'], 0, ',', '.'); ?></p>

        <h4>Detail Barang</h4>
        <table>
            <thead>
                <tr><th>Kode</th><th>Nama Barang</th><th>Harga</th><th>Jumlah</th><th>Subtotal</th><th>Aksi</th></tr>
            </thead>
            <tbody>
                <?php if (mysqli_num_rows($details) === 0) { ?>
                    <tr><td colspan="6">Belum ada barang pada transaksi ini.</td></tr>
                <?php } ?>
                <?php while ($item = mysqli_fetch_assoc($details)) { ?>
                    <tr>
                        <td><?php echo h($item['kode_barang']); ?></td>
                        <td><?php echo h($item['nama_barang']); ?></td>
                        <td>Rp <?php echo number_format((float) $item['harga'], 0, ',', '.'); ?></td>
                        <td><?php echo h($item['qty']); ?></td>
                        <td>Rp <?php echo number_format((float) $item['harga'] * (int) $item['qty'], 0, ',', '.'); ?></td>
                        <td>
                            <form method="post" action="index.php?page=transaksi" onsubmit="return confirm('Hapus barang ini dari transaksi?');">
                                <input type="hidden" name="action" value="delete_detail">
                                <input type="hidden" name="transaksi_id" value="<?php echo h($transactionId); ?>">
                                <input type="hidden" name="barang_id" value="<?php echo h($item['barang_id']); ?>">
                                <button class="danger" type="submit">Hapus</button>
                            </form>
                        </td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>

        <h4>Tambah Barang</h4>
        <?php if (mysqli_num_rows($availableProducts) === 0) { ?>
            <p>Semua barang sudah dimasukkan ke transaksi ini.</p>
        <?php } else { ?>
            <form method="post" action="index.php?page=transaksi">
                <input type="hidden" name="action" value="add_detail">
                <input type="hidden" name="transaksi_id" value="<?php echo h($transactionId); ?>">
                <div class="form-grid">
                    <label>
                        Barang
                        <select name="barang_id" required>
                            <option value="">-- Pilih barang --</option>
                            <?php while ($product = mysqli_fetch_assoc($availableProducts)) { ?>
                                <option value="<?php echo h($product['id']); ?>">
                                    <?php echo h($product['kode_barang'] . ' - ' . $product['nama_barang']); ?>
                                    (Rp <?php echo number_format((float) $product['harga'], 0, ',', '.'); ?>, stok <?php echo h($product['stok']); ?>)
                                </option>
                            <?php } ?>
                        </select>
                    </label>
                    <label>
                        Jumlah
                        <input type="number" name="qty" value="1" min="1" required>
                    </label>
                </div>
                <button type="submit">Tambah ke Transaksi</button>
            </form>
        <?php } ?>
    </section>
<?php } else { ?>
    <section class="section">
        <h3>Tambah Transaksi Baru</h3>
        <?php if (mysqli_num_rows($customers) === 0) { ?>
            <p>Tambahkan data pelanggan terlebih dahulu sebelum membuat transaksi.</p>
        <?php } else { ?>
            <form method="post" action="index.php?page=transaksi">
                <input type="hidden" name="action" value="create_transaction">
                <div class="form-grid">
                    <label>
                        Tanggal Transaksi
                        <input type="date" name="waktu_transaksi" value="<?php echo h(date('Y-m-d')); ?>" required>
                    </label>
                    <label>
                        Pelanggan
                        <select name="pelanggan_id" required>
                            <option value="">-- Pilih pelanggan --</option>
                            <?php while ($customer = mysqli_fetch_assoc($customers)) { ?>
                                <option value="<?php echo h($customer['id']); ?>"><?php echo h($customer['nama']); ?></option>
                            <?php } ?>
                        </select>
                    </label>
                    <label>
                        Keterangan
                        <input type="text" name="keterangan">
                    </label>
                </div>
                <button type="submit">Buat Transaksi</button>
            </form>
        <?php } ?>
    </section>

    <h3>Daftar Transaksi</h3>
    <table>
        <thead>
            <tr><th>ID</th><th>Tanggal</th><th>Pelanggan</th><th>Keterangan</th><th>Jumlah Item</th><th>Total</th><th>Aksi</th></tr>
        </thead>
        <tbody>
            <?php if (mysqli_num_rows($transactionList) === 0) { ?>
                <tr><td colspan="7">Belum ada transaksi.</td></tr>
            <?php } ?>
            <?php while ($transaction = mysqli_fetch_assoc($transactionList)) { ?>
                <tr>
                    <td>#<?php echo h($transaction['id']); ?></td>
                    <td><?php echo h($transaction['waktu_transaksi']); ?></td>
                    <td><?php echo h($transaction['pelanggan'] ?? '-'); ?></td>
                    <td><?php echo h($transaction['keterangan'] ?? '-'); ?></td>
                    <td><?php echo h($transaction['jumlah_item']); ?></td>
                    <td>Rp <?php echo number_format((float) $transaction['total'], 0, ',', '.'); ?></td>
                    <td><a class="button" href="index.php?page=transaksi&amp;id=<?php echo h($transaction['id']); ?>">Lihat / Isi Detail</a></td>
                </tr>
            <?php } ?>
        </tbody>
    </table>
<?php } ?>

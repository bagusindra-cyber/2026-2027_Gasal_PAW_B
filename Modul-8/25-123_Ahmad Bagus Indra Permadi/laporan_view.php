<?php
if (!defined('APP_ENTRY')) {
    http_response_code(404);
    exit();
}

$startValue = $_GET['tanggal_mulai'] ?? '';
$endValue = $_GET['tanggal_selesai'] ?? '';
$startDate = is_string($startValue) ? $startValue : '';
$endDate = is_string($endValue) ? $endValue : '';
$reportError = '';
$dailyRows = [];
$transactionRows = [];
$summary = null;
$hasFilter = $startDate !== '' || $endDate !== '';

if ($hasFilter) {
    if (!validDate($startDate) || !validDate($endDate)) {
        $reportError = 'Tanggal mulai dan tanggal selesai harus diisi dengan format yang valid.';
    } elseif ($startDate > $endDate) {
        $reportError = 'Tanggal mulai tidak boleh melewati tanggal selesai.';
    } else {
        $dailyStatement = runStatement(
            $connect,
            'SELECT DATE(waktu_transaksi) AS tanggal, COUNT(*) AS jumlah_transaksi, SUM(total) AS total_penjualan
             FROM transaksi
             WHERE waktu_transaksi BETWEEN ? AND ?
             GROUP BY DATE(waktu_transaksi)
             ORDER BY tanggal',
            'ss',
            [$startDate, $endDate]
        );
        $dailyResult = mysqli_stmt_get_result($dailyStatement);
        while ($row = mysqli_fetch_assoc($dailyResult)) {
            $dailyRows[] = $row;
        }

        $transactionStatement = runStatement(
            $connect,
            'SELECT t.id, t.waktu_transaksi, t.keterangan, t.total, p.nama AS pelanggan
             FROM transaksi t
             LEFT JOIN pelanggan p ON p.id = t.pelanggan_id
             WHERE t.waktu_transaksi BETWEEN ? AND ?
             ORDER BY t.waktu_transaksi, t.id',
            'ss',
            [$startDate, $endDate]
        );
        $transactionResult = mysqli_stmt_get_result($transactionStatement);
        while ($row = mysqli_fetch_assoc($transactionResult)) {
            $transactionRows[] = $row;
        }

        $summaryStatement = runStatement(
            $connect,
            'SELECT COUNT(*) AS jumlah_transaksi,
                    COUNT(DISTINCT pelanggan_id) AS jumlah_pelanggan,
                    COALESCE(SUM(total), 0) AS total_pendapatan
             FROM transaksi
             WHERE waktu_transaksi BETWEEN ? AND ?',
            'ss',
            [$startDate, $endDate]
        );
        $summary = mysqli_fetch_assoc(mysqli_stmt_get_result($summaryStatement));
    }
}
?>
<h2>Laporan Penjualan</h2>
<form class="no-print section" method="get" action="index.php">
    <input type="hidden" name="page" value="laporan">
    <div class="form-grid">
        <label>
            Tanggal Mulai
            <input type="date" name="tanggal_mulai" value="<?php echo h($startDate); ?>" required>
        </label>
        <label>
            Tanggal Selesai
            <input type="date" name="tanggal_selesai" value="<?php echo h($endDate); ?>" required>
        </label>
    </div>
    <button type="submit">Tampilkan Laporan</button>
    <?php if ($summary !== null) { ?>
        <button type="button" onclick="window.print()">Cetak</button>
    <?php } ?>
</form>

<?php if ($reportError !== '') { ?>
    <p class="flash error"><?php echo h($reportError); ?></p>
<?php } elseif ($summary !== null) { ?>
    <h3>Periode <?php echo h($startDate); ?> s/d <?php echo h($endDate); ?></h3>
    <section class="section">
        <p><strong>Jumlah transaksi:</strong> <?php echo h($summary['jumlah_transaksi']); ?></p>
        <p><strong>Jumlah pelanggan unik:</strong> <?php echo h($summary['jumlah_pelanggan']); ?></p>
        <p><strong>Total pendapatan:</strong> Rp <?php echo number_format((float) $summary['total_pendapatan'], 0, ',', '.'); ?></p>
    </section>

    <h3>Ringkasan Penjualan Harian</h3>
    <table>
        <thead><tr><th>Tanggal</th><th>Jumlah Transaksi</th><th>Total Penjualan</th></tr></thead>
        <tbody>
            <?php if (count($dailyRows) === 0) { ?>
                <tr><td colspan="3">Tidak ada transaksi pada periode ini.</td></tr>
            <?php } ?>
            <?php foreach ($dailyRows as $row) { ?>
                <tr>
                    <td><?php echo h($row['tanggal']); ?></td>
                    <td><?php echo h($row['jumlah_transaksi']); ?></td>
                    <td>Rp <?php echo number_format((float) $row['total_penjualan'], 0, ',', '.'); ?></td>
                </tr>
            <?php } ?>
        </tbody>
    </table>

    <h3>Rincian Transaksi</h3>
    <table>
        <thead><tr><th>ID</th><th>Tanggal</th><th>Pelanggan</th><th>Keterangan</th><th>Total</th></tr></thead>
        <tbody>
            <?php if (count($transactionRows) === 0) { ?>
                <tr><td colspan="5">Tidak ada transaksi pada periode ini.</td></tr>
            <?php } ?>
            <?php foreach ($transactionRows as $row) { ?>
                <tr>
                    <td>#<?php echo h($row['id']); ?></td>
                    <td><?php echo h($row['waktu_transaksi']); ?></td>
                    <td><?php echo h($row['pelanggan'] ?? '-'); ?></td>
                    <td><?php echo h($row['keterangan'] ?? '-'); ?></td>
                    <td>Rp <?php echo number_format((float) $row['total'], 0, ',', '.'); ?></td>
                </tr>
            <?php } ?>
        </tbody>
    </table>
<?php } else { ?>
    <p>Pilih rentang tanggal untuk melihat laporan penjualan.</p>
<?php } ?>

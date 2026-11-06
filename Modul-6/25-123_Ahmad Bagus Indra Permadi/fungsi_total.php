<?php

function hitungUlangTotalTransaksi(mysqli $koneksi, int $transaksi_id): void
{
    $stmtTotal = mysqli_prepare($koneksi,
        "SELECT COALESCE(SUM(harga * qty), 0) AS total_baru FROM transaksi_detail WHERE transaksi_id = ?");
    mysqli_stmt_bind_param($stmtTotal, 'i', $transaksi_id);
    mysqli_stmt_execute($stmtTotal);
    $totalBaru = mysqli_fetch_assoc(mysqli_stmt_get_result($stmtTotal))['total_baru'];

    $stmtUpdate = mysqli_prepare($koneksi, "UPDATE transaksi SET total = ? WHERE id = ?");
    mysqli_stmt_bind_param($stmtUpdate, 'ii', $totalBaru, $transaksi_id);
    mysqli_stmt_execute($stmtUpdate);
}

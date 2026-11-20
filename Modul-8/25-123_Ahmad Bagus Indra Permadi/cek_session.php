<?php
// File ini adalah MIDDLEWARE proteksi halaman.
// Cukup include file ini di baris paling atas setiap halaman yang
// hanya boleh diakses setelah login (index.php, Data Master, Transaksi, Laporan).
session_start();

if (!isset($_SESSION['login']) || $_SESSION['login'] !== true) {
    header("Location: login.php");
    exit();
}
?>

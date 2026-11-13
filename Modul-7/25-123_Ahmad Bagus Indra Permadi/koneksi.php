<?php
$servername = "localhost";
$username   = "root";
$password   = "";
$dbname     = "db_toko";

$koneksi = mysqli_connect($servername, $username, $password, $dbname);

if (mysqli_connect_error()) {
    die("Koneksi database gagal: " . mysqli_connect_error());
}
?>
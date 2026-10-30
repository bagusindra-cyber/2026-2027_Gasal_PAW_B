<?php
include 'koneksi.php';

$query = "CREATE TABLE Supplier (
    id_perusahaan INT(10) AUTO_INCREMENT PRIMARY KEY,
    perusahaan VARCHAR(255) NOT NULL,
    nomorTelp VARCHAR(50) NOT NULL,
    alamat VARCHAR(100) NOT NULL
)";

if (mysqli_query($conn, $query)) {
    echo "Tabel Supplier berhasil dibuat! Silakan kembali ke index.php";
} else {
    echo "Error membuat tabel: " . mysqli_error($conn);
}
?>
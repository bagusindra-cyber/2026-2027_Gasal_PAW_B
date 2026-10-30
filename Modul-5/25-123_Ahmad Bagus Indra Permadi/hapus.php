<?php
include 'koneksi.php';

$id = $_GET['id'];
$query = "DELETE FROM Supplier WHERE id_perusahaan = $id";

if (mysqli_query($conn, $query)) {
    header("Location: index.php");
} else {
    echo "Error: " . mysqli_error($conn);
}
?>
<?php
include 'koneksi.php';

if (isset($_POST['submit'])) {
    $perusahaan = $_POST['perusahaan'];
    $nomorTelp = $_POST['nomorTelp'];
    $alamat = $_POST['alamat'];

    $query = "INSERT INTO Supplier (perusahaan, nomorTelp, alamat) VALUES ('$perusahaan', '$nomorTelp', '$alamat')";
    if (mysqli_query($conn, $query)) {
        header("Location: index.php");
    } else {
        echo "Error: " . mysqli_error($conn);
    }
}
?>
<!DOCTYPE html>
<html>
<head><title>Tambah Supplier</title></head>
<body>
    <h2>Tambah Data Supplier</h2>
    <form action="" method="POST">
        <label>Nama Perusahaan:</label><br>
        <input type="text" name="perusahaan" required><br><br>
        
        <label>Nomor Telepon:</label><br>
        <input type="text" name="nomorTelp" required><br><br>
        
        <label>Alamat:</label><br>
        <textarea name="alamat" required></textarea><br><br>
        
        <button type="submit" name="submit">Simpan</button>
        <a href="index.php"><button type="button">Batal</button></a>
    </form>
</body>
</html>
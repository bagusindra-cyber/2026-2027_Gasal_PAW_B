<?php
include 'koneksi.php';

$id = $_GET['id'];


$query_select = "SELECT * FROM Supplier WHERE id_perusahaan = $id";
$result = mysqli_query($conn, $query_select);
$data = mysqli_fetch_assoc($result);


if (isset($_POST['submit'])) {
    $perusahaan = $_POST['perusahaan'];
    $nomorTelp = $_POST['nomorTelp'];
    $alamat = $_POST['alamat'];

    $query_update = "UPDATE Supplier SET perusahaan='$perusahaan', nomorTelp='$nomorTelp', alamat='$alamat' WHERE id_perusahaan=$id";
    if (mysqli_query($conn, $query_update)) {
        header("Location: index.php");
    } else {
        echo "Error: " . mysqli_error($conn);
    }
}
?>
<!DOCTYPE html>
<html>
<head><title>Edit Supplier</title></head>
<body>
    <h2>Edit Data Supplier</h2>
    <form action="" method="POST">
        <label>Nama Perusahaan:</label><br>
        <input type="text" name="perusahaan" value="<?php echo $data['perusahaan']; ?>" required><br><br>
        
        <label>Nomor Telepon:</label><br>
        <input type="text" name="nomorTelp" value="<?php echo $data['nomorTelp']; ?>" required><br><br>
        
        <label>Alamat:</label><br>
        <textarea name="alamat" required><?php echo $data['alamat']; ?></textarea><br><br>
        
        <button type="submit" name="submit">Update</button>
        <a href="index.php"><button type="button">Batal</button></a>
    </form>
</body>
</html>
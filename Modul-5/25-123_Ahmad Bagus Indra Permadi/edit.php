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
<style>
    body {
        font-family: Arial, Helvetica, sans-serif;
        color: #333;
        margin: 30px;
    }

    h2 {
        color: #337ab7;
        margin-bottom: 20px;
        font-weight: normal;
        border-bottom: 1px solid #eee;
        padding-bottom: 10px;
    }

    .form-container {
        width: 50%;
        margin-top: 20px;
    }

    .form-group {
        display: flex;
        align-items: center;
        margin-bottom: 15px;
    }

    .form-group label {
        width: 25%; 
        font-weight: bold;
        text-align: right;
        margin-right: 20px;
    }

    .form-group input,
    .form-group textarea {
        width: 75%; 
        padding: 8px;
        border: 1px solid #ccc;
        border-radius: 4px;
        box-sizing: border-box;
    }

    .form-actions {
        display: flex;
        margin-top: 20px;
        margin-left: 25%; 
        padding-left: 20px;
        gap: 10px; 
    }

    .btn {
        text-decoration: none;
        color: white;
        border-radius: 4px;
        border: 1px solid transparent;
        padding: 8px 15px;
        font-size: 14px;
        cursor: pointer;
    }

    .btn-simpan, .btn-update {
        background-color: #73b839; 
        border-color: #67a633;
    }

    .btn-batal {
        background-color: #c0392b; 
        border-color: #a5281b;
    }
</style>
<body>
    <h2>Edit Data Master Supplier</h2>
    
    <div class="form-container">
        <form action="" method="POST">
            
            <div class="form-group">
                <label>Nama</label>
                <input type="text" name="perusahaan" value="<?php echo $data['perusahaan']; ?>" required>
            </div>
            
            <div class="form-group">
                <label>Telp</label>
                <input type="text" name="nomorTelp" value="<?php echo $data['nomorTelp']; ?>" required>
            </div>
            
            <div class="form-group">
                <label>Alamat</label>
                <input type="text" name="alamat" value="<?php echo $data['alamat']; ?>" required>
            </div>
            
            <div class="form-actions">
                <button type="submit" name="submit" class="btn btn-update">Update</button>
                <a href="index.php" class="btn btn-batal">Batal</a>
            </div>
            
        </form>
    </div>
</body>
</html>
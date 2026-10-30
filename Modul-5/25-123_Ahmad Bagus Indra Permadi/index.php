<?php
include 'koneksi.php';
?>
<!DOCTYPE html>
<html>
<head>
    <title>Data Master Supplier</title>
<style>
    body {
        font-family: Arial, Helvetica, sans-serif;
        color: #333;
        margin: 30px;
    }

    .header-container {
        display: flex;
        justify-content: space-between;
        align-items: flex-end;
        border-bottom: 1px solid #eee;
        padding-bottom: 15px;
        margin-bottom: 20px;
    }

    h2 {
        color: #337ab7; 
        margin: 0;
        font-weight: normal;
        font-size: 24px;
    }

    .btn {
        text-decoration: none;
        color: white;
        border-radius: 4px;
        display: inline-block;
        border: 1px solid transparent;
        text-align: center;
    }

    .btn-tambah {
        background-color: #73b839; 
        border-color: #67a633;
        padding: 8px 15px;
        font-size: 14px;
    }

    .btn-edit {
        background-color: #e67e22; 
        border-color: #d35400;
        padding: 6px 12px;
        font-size: 13px;
    }

    .btn-hapus {
        background-color: #c0392b; 
        border-color: #a5281b;
        padding: 6px 12px;
        font-size: 13px;
    }

    table {
        width: 100%;
        border-collapse: collapse;
    }

    th, td {
        padding: 12px;
        text-align: left;
        border: 1px solid #ddd; 
    }

    th {
        background-color: #d9edf7; 
        color: #31708f;
        font-weight: bold;
    }
</style>
</head> 
<body>
    <div class="header-container">
        <h2>Data Master Supplier</h2>
        <a href="tambah.php" class="btn btn-tambah">Tambah Data</a>
    </div>
    
    <table>
        <tr>
            <th>No</th>
            <th>Nama</th>
            <th>Telp</th>
            <th>Alamat</th>
            <th>Tindakan</th>
        </tr>
        <?php
        $query = "SELECT * FROM Supplier";
        $result = mysqli_query($conn, $query);
        $no = 1;

        if (mysqli_num_rows($result) > 0) {
            while ($row = mysqli_fetch_assoc($result)) {
                echo "<tr>";
                echo "<td>" . $no++ . "</td>";
                echo "<td>" . $row['perusahaan'] . "</td>";
                echo "<td>" . $row['nomorTelp'] . "</td>";
                echo "<td>" . $row['alamat'] . "</td>";
                echo "<td>
                        <a href='edit.php?id=" . $row['id_perusahaan'] . "' class='btn btn-edit'>Edit</a> 
                        <a href='hapus.php?id=" . $row['id_perusahaan'] . "' class='btn btn-hapus' onclick=\"return confirm('Anda yakin akan menghapus supplier ini?');\">Hapus</a>
                      </td>";
                echo "</tr>";
            }
        } else {
            echo "<tr><td colspan='5' style='text-align:center;'>Belum ada data supplier.</td></tr>";
        }
        ?>
    </table>
</body>
</html>
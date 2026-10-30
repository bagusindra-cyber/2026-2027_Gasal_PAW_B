<?php
include 'koneksi.php';
?>
<!DOCTYPE html>
<html>
<head>
    <title>Data Master Supplier</title>
    <style>
        table { border-collapse: collapse; width: 60%; margin-top: 10px; }
        th, td { padding: 8px; text-align: left; border: 1px solid black; }
        a { text-decoration: none; padding: 2px 5px; }
    </style>
</head> 
<body>
    <h2>Data Master Supplier</h2>
    <a href="tambah.php" style="border: 1px solid #000; background: #eee;">+ Tambah Data</a>
    <br><br>
    
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
                        <a href='edit.php?id=" . $row['id_perusahaan'] . "'>Edit</a> | 
                        <a href='hapus.php?id=" . $row['id_perusahaan'] . "' onclick=\"return confirm('Anda yakin akan menghapus supplier ini?');\">Hapus</a>
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
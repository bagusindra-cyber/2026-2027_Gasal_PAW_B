<?php
$hostname = "localhost";
$username = "root";
$password ="";

$conn = mysqli_connect($hostname, $username, $password);

if (!$conn){
	die("Connection failed: ".mysql_connect_error());
}
echo "Connection successfully";

$sql = "CREATE DATABASE Modul05";
if (mysqli_query($conn,$sql)){
	echo "Database created successfully";
}else {
 echo "Error creating database: " . mysqli_error($conn);
}



$tab = "CREATE TABLE Supplier (
	id_perusahaan INT(10) AUTO_INCREMENT PRIMARY KEY,
	perusahaan VARCHAR(255) NOT NULL,
	nomorTelp VARCHAR(50) NOT NULL,
	alamat VARCHAR(100)NOT NULL)";
mysqli_close($conn);
?>
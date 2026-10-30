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

mysqli_close($conn);
?>
<?PHP
$vegies = array("Carrot", "Broccoli", "Spinach");
$arrlength = count($vegies);

for ($x = 0; $x < $arrlength; $x++) {
    echo $vegies[$x];
    echo "<br>";
}
?>

<p><b>Penjelasan:</b>memodifikasi skrip yang sudah ada tidak perlu membuat skrip baru
logika perulangannya sama persis (hitung panjang dengan count() lalu telusuri indeks
0 sampai $arrlength - 1 dengan for) yang berubah hanya nama array
($fruits menjadi $vegies) dan isi datanya karena skrip bersifat generik
untuk array terindeks berapa pun ukurannya</p>

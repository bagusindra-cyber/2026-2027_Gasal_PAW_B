<?PHP
$fruits = array("Avocado", "Blueberry", "Cherry");

for ($i = 1; $i <= 5; $i++) {
    $fruits[] = "Buah Tambahan " . $i;
}

$arrlength = count($fruits);
echo "Panjang array saat ini: " . $arrlength . "<br><br>";

for ($x = 0; $x < $arrlength; $x++) {
    echo $fruits[$x];
    echo "<br>";
}
?>

<p><b>penjelasan:</b> skrip perulangan for pada baris #5-#8 tidak perlu diubah
batas perulangan memakai variabel $arrlength = count($fruits), bukan angka tetap (3),
sehingga otomatis mengikuti jumlah data syaratnya count() harus dipanggil
setelah data baru ditambahkan (seperti pada skrip ini) jika count() dipanggil sebelum
penambahan, $arrlength tetap 3 dan hanya tiga data pertama yang tampil</p>

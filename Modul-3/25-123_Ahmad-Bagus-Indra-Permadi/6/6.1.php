<?PHP
$a = array("A");
echo 'Array awal: ("A")<br>';
array_push($a, "B");
echo "Hasil array_push: " . implode(" ", $a) . "<br><br>";

$a1 = array("A", "B");
$a2 = array("C");
echo 'Array awal: ("A", "B") digabung dengan ("C")<br>';
echo "Hasil array_merge: " . implode(" ", array_merge($a1, $a2)) . "<br><br>";

$assoc = array("x" => 1, "y" => 2);
echo 'Array awal: ("x" => 1, "y" => 2)<br>';
echo "Hasil array_values: " . implode(" ", array_values($assoc)) . "<br><br>";

$cari = array("A", "B", "C");
echo 'Mencari "B" pada array: ("A", "B", "C")<br>';
echo "Hasil array_search: " . array_search("B", $cari) . "<br><br>";

$campur = array(0, 1, false, 2, "", 3, "array");
echo 'Array awal: (0, 1, false, 2, "", 3, "array")<br>';
echo "Hasil array_filter: " . implode(" ", array_filter($campur)) . "<br><br>";

$angka = array(3, 1, 2);
echo "Array awal: (3, 1, 2)<br>";
sort($angka);
echo "Hasil sort: " . implode(" ", $angka) . "<br>";
rsort($angka);
echo "Hasil rsort: " . implode(" ", $angka) . "<br><br>";

$umur = array("Peter" => 35, "Ben" => 37, "Joe" => 43);
echo 'Array awal: ("Peter"=>35, "Ben"=>37, "Joe"=>43)<br>';

function cetakAsosiatif($label, $arr) {
    echo "Hasil $label: ";
    foreach ($arr as $k => $v) {
        echo "$k=> $v, ";
    }
    echo "<br>";
}

$t = $umur; asort($t);  cetakAsosiatif("asort", $t);
$t = $umur; ksort($t);  cetakAsosiatif("ksort", $t);
$t = $umur; arsort($t); cetakAsosiatif("arsort", $t);
$t = $umur; krsort($t); cetakAsosiatif("krsort", $t);
?>

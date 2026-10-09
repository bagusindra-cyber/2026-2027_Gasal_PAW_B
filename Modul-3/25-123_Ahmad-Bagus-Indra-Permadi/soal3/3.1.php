<?PHP
function tampilArray($nama, $arr) {
    $items = array();
    foreach ($arr as $k => $v) {
        $items[] = "\"$k\"=>\"$v\"";
    }
    return "$nama = (" . implode(", ", $items) . ")";
}

$height = array("Andy" => "176", "Barry" => "165", "Charlie" => "170");
$height["David"]  = "180";
$height["Ethan"]  = "172";
$height["Frank"]  = "168";
$height["George"] = "175";
$height["Harry"]  = "182";
echo tampilArray("height", $height) . "<br>";
echo "Nilai dengan indeks terakhir: " . end($height) . "<br><br>";
unset($height["Barry"]);

echo tampilArray("height", $height) . "<br>";
echo "Nilai dengan indeks terakhir setelah dihapus: " . end($height);
?>

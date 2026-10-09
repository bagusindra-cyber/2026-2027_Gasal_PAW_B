<?PHP
$weight = array("Andy" => "70", "Barry" => "65", "Charlie" => "75");

$items = array();
foreach ($weight as $k => $v) {
    $items[] = "\"$k\"=>\"$v\"";
}
echo "weight = (" . implode(", ", $items) . ")<br><br>";
$keys = array_keys($weight);
$jumlah = count($keys);

for ($i = 0; $i < $jumlah; $i++) {
    $nama = $keys[$i];
    echo $nama . " is " . $weight[$nama] . " kg.";
    echo "<br>";
}
?>

<?PHP
$weight = array("Andy" => "70", "Barry" => "65", "Charlie" => "75");
$items = array();

foreach ($weight as $k => $v) {
    $items[] = "\"$k\"=>\"$v\"";
}

echo "weight = (" . implode(", ", $items) . ")<br>";
$nilai = array_values($weight);
echo "Data kedua: " . $nilai[1];
?>

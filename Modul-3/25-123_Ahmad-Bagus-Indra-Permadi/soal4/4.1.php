<?PHP
$height = array("Andy" => "176", "Barry" => "165", "Charlie" => "170");
$height["David"]  = "180";
$height["Ethan"]  = "172";
$height["Frank"]  = "168";
$height["George"] = "175";
$height["Harry"]  = "182";

$items = array();
foreach ($height as $k => $v) {
    $items[] = "\"$k\"=>\"$v\"";
}
echo "height = (" . implode(", ", $items) . ")<br><br>";

foreach ($height as $nama => $tinggi) {
    echo $nama . " is " . $tinggi . " cm tall.";
    echo "<br>";
}
?>

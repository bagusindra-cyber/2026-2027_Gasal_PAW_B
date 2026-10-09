<?PHP
$fruits = array("Avocado", "Blueberry", "Cherry", "Durian", "Elderberry", "Fig", "Grape", "Honeydew");
$hapus = "Blueberry";
$index = array_search($hapus, $fruits);

if ($index !== false) {
    unset($fruits[$index]);
    $fruits = array_values($fruits);
}

echo "Data $hapus dihapus.<br>";
echo 'fruits = ("' . implode('", "', $fruits) . '")<br>';
echo "Nilai dengan indeks tertinggi: " . $fruits[count($fruits) - 1];
?>

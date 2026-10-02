<?PHP
$matkul = array("PTI","ALPRO","DPW","STRUKDAT","JARKOM","PAW","PSBF","RPL");
foreach ($matkul as $value) {
    switch($value){
        case "PTI":
            echo "Saya suka ".$value."<br>";
            break;
        case "ALPRO":
            echo "Saya suka ".$value."<br>";
            break; 
        case "DPW":
            echo "Saya suka ".$value."<br>";
            break;
        case "STRUKDAT":
            echo "Saya suka ".$value."<br>";
            break; 
        case "JARKOM":
            echo "Saya suka ".$value."<br>";
            break; 
        case "PAW":
            echo "Saya suka ".$value."<br>";
            break;
        case "PSBF":
            echo "Saya tidak mengambil matkul ".$value."<br>";
            break;   
        case "RPL":
            echo "Saya tidak mengambil matkul ".$value."<br>";
            break;

        default:
            echo "tidakmengambil matkul";
            break;
        }
}
?>

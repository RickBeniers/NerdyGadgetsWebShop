<style>
table, th, td {
    border:1px solid black;
    }
</style>
<?php
$host = "localhost";
$user = "root";
$pass = "Rick3109"; //eigen password invullen
$databasename = "cursus";
$port = 3306;
$connection = mysqli_connect($host, $user, $pass, $databasename, $port);
$sql = "SELECT * FROM medewerker";
$result = mysqli_query($connection, $sql);

$medewerkers = mysqli_fetch_all($result, MYSQLI_ASSOC);
print_r($medewerkers);

//$naam = array();
//$maandsal = array();
//while ($row = mysqli_fetch_array($result, MYSQLI_ASSOC))
//{
//    $naam[] = $row["naam"];
//    $maandsal[] = $row["maandsal"];
//}
//print_r($naam);
//print_r($maandsal);
//foreach($naam as $i => $naam){
//    print("<table style='width:100%' >
//<tr>
//<th>$naam</th>
//</tr>
//<tr>
//<td>$maandsal[$i]</td>
//</tr>
//</table>");
//}
?>
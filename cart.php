<!-- dit bestand bevat alle code voor de winkelmand functionaliteit -->
<?php
//session_start();

include __DIR__ . "/header.php";
//include "database.php";

//$databaseConnection = connectToDatabase();

$sql_cart_invoer = "";

function berekenVerkoopPrijs($adviesPrijs, $btw)
{
	return $btw * $adviesPrijs / 100 + $adviesPrijs;
}

function getVoorraadTekst($actueleVoorraad)
{
	if ($actueleVoorraad > 1000) {
		return "Ruime voorraad beschikbaar.";
	} elseif ($actueleVoorraad <= 0) {
		return "Geen voorraad beschikbaar";
	} else {
		return "Voorraad: $actueleVoorraad";
	}
}
//aantal producten in winkelmand aanpassen (input field + "-" en "+" knoppen)
if (isset($_GET['hoeveel'])) {}

if (isset($_GET['min'])) {
    addProductToCart($_GET['id'], -1);}

if (isset($_GET['max'])) {
    addProductToCart($_GET['id'], 1);}

//product uit winkelmand verwijderen (knop)
if(isset($_GET["delete"])){
    deleteProductFromCart($_GET['id']);}

$cart = getCart();

//producten ophalen vanuit array --> tonen van de producten
if (!empty($_SESSION['cart'])) {
for ($i = 0; $i < count($cart);) { //$i=0 das de rede hiervoor
	foreach ($cart as $x => $blablabla) {

		if ($i != 0) {
			$sql_cart_invoer .= "OR ";
		}
		$i++;
		$sql_cart_invoer .= 'SI.StockItemID="' . $x . '"';
	}
}

if ($cart != "") {
	$Query = "
               SELECT SI.StockItemID, SI.StockItemName, SI.MarketingComments, TaxRate, RecommendedRetailPrice,
               ROUND(SI.TaxRate * SI.RecommendedRetailPrice / 100 + SI.RecommendedRetailPrice,2) as SellPrice,
               QuantityOnHand,
               (SELECT ImagePath FROM stockitemimages WHERE StockItemID = SI.StockItemID LIMIT 1) as ImagePath,
               (SELECT ImagePath FROM stockgroups JOIN stockitemstockgroups USING(StockGroupID) WHERE StockItemID = SI.StockItemID LIMIT 1) as BackupImagePath
               FROM stockitems SI
               JOIN stockitemholdings SIH USING(stockitemid)
               JOIN stockitemstockgroups USING(StockItemID)
               JOIN stockgroups ON stockitemstockgroups.StockGroupID = stockgroups.StockGroupID
               WHERE " . $sql_cart_invoer . "
               GROUP BY StockItemID
               ";
	$Connection = connectToDatabase();
	$result = mysqli_query($Connection, $Query);

}
?>
<div id="ResultsArea" class="Cart">
	<?php
	if (isset($result)) {
		foreach ($result as $row) {
			?>
			<a class="ListItem" href='view.php?id=<?php print $row['StockItemID']; ?>'>
				<div id="ProductFrameCart">
					<?php
					if (isset($row['ImagePath'])) { ?>
						<div class="ImgFrameCart"
						     style="background-image: url('<?php print "Public/StockItemIMG/" . $row['ImagePath']; ?>'); background-size: 230px; background-repeat: no-repeat; background-position: center;"></div>
					<?php } else if (isset($row['BackupImagePath'])) { ?>
						<div class="ImgFrameCart"
						     style="background-image: url('<?php print "Public/StockGroupIMG/" . $row['BackupImagePath'] ?>'); background-size: cover;"></div>
					<?php }
					?>

					<div id="StockItemFrameRight">
						<div class="CenterPriceLeftChild">
							<h1 class="StockItemPriceText"><?php print sprintf(" %0.2f", berekenVerkoopPrijs($row["RecommendedRetailPrice"], $row["TaxRate"])); ?></h1>
							<h6>Inclusief BTW </h6>
						</div>
					</div>

					<h1 class="StockItemID">Artikelnummer: <?php print $row["StockItemID"]; ?></h1>
					<p class="StockItemName"><?php print $row["StockItemName"]; ?></p>
					<p class="StockItemComments"><?php print $row["MarketingComments"]; ?></p>

                    <p style="display: inline-flex">
                    <form action="cart.php" method="GET">
                        <!--product uit winkelmand verwijderen (knop)-->
                        <input type="submit" name="delete" value="rm" style="height:50px;width:50px">
                        <!--aantal producten in winkelmand aanpassen (input field + "-" en "+" knoppen)-->

                        <input type="submit" name="min" value="-" style="height:50px;width:50px">


                        <input type="number" name="hoeveel" value="<?php print($cart[$row["StockItemID"]]);?>" style="height:50px;width:150px">
                        <input type="number" name="id" value="<?php print($row["StockItemID"]);?>" hidden>


                        <input type="submit" name="max" value="+" style="height:50px;width:50px">
                    </form>
                    </p>
					<h4 class="ItemQuantity"><?php print getVoorraadTekst($row["QuantityOnHand"]); ?></h4>
				</div>
			</a>
        <?php }
	}
	}
	//totaalprijs berekenen + tonen van de totaalprijs
        $BTW = 0;
        $prijsBTW = 0;
        $prijs = 0;

        if(isset($result)){
            foreach($result as $row){
                $prijs = $prijs+($row["RecommendedRetailPrice"]*$cart[$row["StockItemID"]]);
                $BTW = $BTW+($row["TaxRate"]/100*$prijs);
                $prijsBTW = $prijs+$BTW;
            }
        }
        ?>
	<h4>De prijs is: <?php print(number_format($prijs, 2));?></h4>
	<h4>De BTW is: <?php print(number_format($BTW, 2));?></h4>
	<h4>U betaalt: <?php print(number_format($prijsBTW, 2));?></h4>


	<!--knop terug naar vorige pagina


	?>

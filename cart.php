<!-- dit bestand bevat alle code voor de winkelmand functionaliteit -->
<?php
//session_start();
session_start();
include_once "database.php";
include_once "Cartfuncties.php";


//Waardes:
$sql_cart_invoer = "";
$databaseConnection = connectToDatabase();

//aantal producten in winkelmand aanpassen (input field + "-" en "+" knoppen)
if (!empty($_GET['hoeveel'])) {
    changeCart($_GET['id'], $_GET['hoeveel'], $databaseConnection);
}

if (isset($_GET['min'])) {
	addProductToCart($_GET['id'], -1, $databaseConnection);
}

if (isset($_GET['max'])) {
	addProductToCart($_GET['id'], 1, $databaseConnection);
}

//product uit winkelmand verwijderen (knop)
if (isset($_GET["delete"])) {
	deleteProductFromCart($_GET['id']);
}

include __DIR__ . "/header.php";
//totaalprijs berekenen + tonen van de totaalprijs
$cart = getCart();

?>

<!-- Winkelwagen Header -->
<div class="container">
    <div class="row" style="background-color: rgba(128,128,128,0);height: 56px">
        <div class="col" style="font-size: 24px;display:flex;align-items:center">
            <i class="fas fa-shopping-cart"></i>
                Winkelwagen
        </div>
    </div>
</div>

<!-- PRODUCTEN TONEN -->
<div class="container">
    <div class="row" style="background-color: rgba(0,128,0,0)">
        <div class="col" style="background-color: rgba(128,0,128,0);margin-top: 12px">
            <div class="container" style="padding-left: 12px;padding-right: 12px">
                <div class="row" style="">

            <?php
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

            <!-- Producten tonen in winkelwagen -> LAAT HIER STAAN -> anders verspringt de prijssidebar -->

                <?php
                if (isset($result)) {
                foreach ($result as $row) {
                ?>

                        <a class="" href='view.php?id=<?php print $row['StockItemID']; ?>'>

                            <!-- Standaard STYLE -> LATER VERWIJDEREN -->
                            <div class="row" style="border: lightgrey 1px solid;padding: 12px;margin-bottom: 12px;border-radius: 8px">

                                <!-- Artikelplaatje -->
                                <?php if (isset($row['ImagePath'])) { ?>

                                    <div class="col-2" style="height:111.6px;border-radius: 8px;background-image: url('<?php print "Public/StockItemIMG/" . $row['ImagePath']; ?>'); background-size: 230px; background-repeat: no-repeat; background-position: center;"></div>

                                    <!-- Backupplaatje -->
                                    <?php } else if (isset($row['BackupImagePath'])) { ?>

                                    <div class="col-2" style="height:111.6px;border-radius: 8px;background-image: url('<?php print "Public/StockGroupIMG/" . $row['BackupImagePath'] ?>'); background-size: cover;"></div>

                                <?php
                                }
                                ?>

                                <!-- Artikelinformatie -->
                                <div class="col-5">

                                <!-- Artikelnummer -->
                                <h1 class="StockItemID">Artikelnummer: <?php print $row["StockItemID"]; ?></h1>

                                <!-- Artikelnaam -->
                                <p class="StockItemName"><?php print $row["StockItemName"]; ?></p>

<!--                                 Artikeluitleg -->
<!--                                <p class="StockItemComments">--><?php //print $row["MarketingComments"]; ?><!--</p>-->

                                <!-- Aantal per product op voorraad -->
                                <h4 class="ItemQuantity"><?php print getVoorraadTekst($row["QuantityOnHand"]); ?></h4>
                                </div>

                                <!-- Aantal producten in winkelmand aanpassen-->
                                <div class="col-5" style="margin-left:auto;padding: unset;text-align:right;">
                                    <!-- Productprijs -->
                                    <div id="" class="col" style="margin: unset">
                                        <h1 class="StockItemPriceText"><?php print sprintf(" %0.2f", berekenVerkoopPrijs($row["RecommendedRetailPrice"], $row["TaxRate"])); ?></h1>
                                    </div>

                                    <!-- Aantal producten in winkelmand aanpassen -->
                                    <form action="cart.php" method="GET">
                                        <!-- producten uit winkelmand verwijderen (knop)-->
                                        <button type="submit" name="delete" style="height: 48px;width: 48px;border: unset;border-radius: 8px;background-color: #721c24;color: white" <i class="fas fa-trash"></i></button>

                                        <!-- Aantal verminderen knop -->
                                        <button type="submit" name="min" style="height: 48px;width: 48px;border: 1px lightgrey solid;border-top-left-radius: 8px;border-bottom-left-radius: 8px;background-color: white;color: black" <i class="fas fa-minus"></i></button>

                                        <!-- Aantal in winkelmand per product -->
                                        <input type="number" name="hoeveel" placeholder="<?php print($cart[$row["StockItemID"]]); ?>" style="height:48px;width:64px;border: 1px lightgrey solid">

                                        <!-- ID van product (HIDDEN) -> Nodig om te weten van welk product aantal aan te passen -->
                                        <input type="number" name="id" value="<?php print($row["StockItemID"]); ?>" hidden>

                                        <!-- Aantal verhogen knop -->
                                        <button type="submit" name="max" style="height: 48px;width: 48px;border: 1px lightgrey solid;border-top-right-radius: 8px;border-bottom-right-radius: 8px;background-color: white;color: black" <i class="fas fa-plus"></i></button>
                                    </form>
                                </div>
                            </div>
                        </a>

                    <?php
                }
                }
                }

                $BTW = 0;
                $prijsBTW = 0;
                $prijs = 0;


                if (isset($result)) {
                    foreach ($result as $row) {
                        $prijs = $prijs + ($row["RecommendedRetailPrice"] * $cart[$row["StockItemID"]]);
                        $prijsBTW = $prijsBTW + (berekenVerkoopPrijs($row["RecommendedRetailPrice"], $row["TaxRate"])) * $cart[$row["StockItemID"]];
                    }
                    $BTW = $prijsBTW - $prijs;
                }
                ?>

            </div>
        </div>
    </div>


        <!-- Totaalprijs sidebar -->
        <div class="col-3" style="background-color: rgba(134,183,254,0);margin-left:auto">

            <h4 style="margin-top: 8px;">Overzicht</h4>

            <h5>De prijs is: <?php print("€ " . number_format($prijs, 2)); ?></h5>
            <h5>De BTW is: <?php print("€ " . number_format($BTW, 2)); ?></h5>
            <h5>U betaalt: <?php print("€ " . number_format($prijsBTW, 2)); ?></h5>
<!--            <h5>Testing: --><?php //print(addProductToCart(76, 1000000000000, $databaseConnection));?><!--</h5>-->

            <!-- RUSH -> BUTTON KAN NOG ANDERS -->
            <form action="bestelling.php">
                <button type="submit" name="doorgaan" style="border: unset;height: 48px;width: 100%;background-color: seagreen;color: white;border-radius: 8px">Doorgaan naar bestellen</button>
            </form>
        </div>
    </div>
</div>

<?php
include __DIR__ . "/footer.php";
?>
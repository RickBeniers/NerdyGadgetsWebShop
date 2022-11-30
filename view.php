<!-- dit bestand bevat alle code voor de pagina die één product laat zien -->
<?php
session_start();
include_once "database.php";
include_once "Cartfuncties.php";

$cart = getCart();
$databaseConnection = connectToDatabase();
if (isset($_POST["submit"])) {
	$stockItemID = $_POST["stockItemID"];
	addProductToCart($stockItemID, 1, $databaseConnection);
//  PRINT BOVENAAN DE PAGINA --> PAGINA VERSPRINGT --> HOE OPLOSSEN!
//	print_r($cart);
//	print("Het product is toegevoegd aan het winkelmandje <a> </a>");
}

include_once "header.php";

$StockItem = getStockItem($_GET['id'], $databaseConnection);
$StockItemImage = getStockItemImage($_GET['id'], $databaseConnection);
?>
<div class="container">
    <div class="row">
    <div id="CenteredContent">
        <?php
        if ($StockItem != null) {
            ?>
            <?php
            if (isset($StockItem['Video'])) {
                ?>
                <div id="VideoFrame">
                    <?php print $StockItem['Video']; ?>
                </div>
            <?php }
            ?>
    </div>

            <div id="ArticleHeader">
                <?php
                if (isset($StockItemImage)) {
                    // één plaatje laten zien
                    if (count($StockItemImage) == 1) {
                        ?>
                        <div id="ImageFrame"
                             style="background-image: url('Public/StockItemIMG/<?php print $StockItemImage[0]['ImagePath']; ?>'); background-size: 300px; background-repeat: no-repeat; background-position: center;"></div>
                        <?php
                    } else if (count($StockItemImage) >= 2) { ?>
                        <!-- meerdere plaatjes laten zien -->
                        <div id="ImageFrame">
                            <div id="ImageCarousel" class="carousel slide" data-interval="false">
                                <!-- Indicators -->
                                <ul class="carousel-indicators">
                                    <?php for ($i = 0; $i < count($StockItemImage); $i++) {
                                        ?>
                                        <li data-target="#ImageCarousel"
                                            data-slide-to="<?php print $i ?>" <?php print (($i == 0) ? 'class="active"' : ''); ?>></li>
                                        <?php
                                    } ?>
                                </ul>

                                <!-- slideshow -->
                                <div class="carousel-inner">
                                    <?php for ($i = 0; $i < count($StockItemImage); $i++) {
                                        ?>
                                        <div class="carousel-item <?php print ($i == 0) ? 'active' : ''; ?>">
                                            <img src="Public/StockItemIMG/<?php print $StockItemImage[$i]['ImagePath'] ?>">
                                        </div>
                                    <?php } ?>
                                </div>

                                <!-- knoppen 'vorige' en 'volgende' -->
                                <a class="carousel-control-prev" href="#ImageCarousel" data-slide="prev">
                                    <span class="carousel-control-prev-icon"></span>
                                </a>
                                <a class="carousel-control-next" href="#ImageCarousel" data-slide="next">
                                    <span class="carousel-control-next-icon"></span>
                                </a>
                            </div>
                        </div>
                        <?php
                    }
                } else {
                    ?>
                    <div id="ImageFrame"
                         style="background-image: url('Public/StockGroupIMG/<?php print $StockItem['BackupImagePath']; ?>'); background-size: cover;"></div>
                    <?php
                }
                ?>


                <h1 class="StockItemID">Artikelnummer: <?php print $StockItem["StockItemID"]; ?></h1>
                <h2 class="StockItemNameViewSize StockItemName">
                    <?php print $StockItem['StockItemName']; ?>
                </h2>
                <div class="QuantityText"><?php
                    print getVoorraadTekst($StockItem["QuantityOnHand"]);?></div>
                <div id="StockItemHeaderLeft">
                    <div class="CenterPriceLeft">
                        <div class="CenterPriceLeftChild">
                            <p class="StockItemPriceText"><b><?php print sprintf("€ %.2f", $StockItem['SellPrice']); ?></b></p>
                            <h6> Inclusief BTW </h6>
                            <!-- Toevoegen aan winkelmand knop -->
                            <form method="post">
                                <div>
                                    <input type="number" name="stockItemID" value="<?php print($_GET["id"]) ?>" hidden >
                                    <input type="submit" name="submit" value="In winkelwagen" style="right: 1px;top: 135px;position: absolute;alignment: bottom;padding: 12px 32px;background: #686ef7;border-radius: 8px;color: white">
                                </div>
                            </form>
                        </div>
<!--                        --><?php
                        if (isset($_POST["submit"])) {
                            $stockItemID = $_POST["stockItemID"];
                            addProductToCart($stockItemID, 1);
                            print("Het product is toegevoegd aan het winkelmandje <a> </a>");
                        }
                        ?>
                    </div>
                </div>
            </div>

            <div id="StockItemDescription">
                <h3>Artikel beschrijving</h3>
                <p><?php print $StockItem['SearchDetails']; ?></p>
            </div>
            <div id="StockItemSpecifications">
                <h3>Artikel specificaties</h3>
                <?php
                $CustomFields = json_decode($StockItem['CustomFields'], true);
                if (is_array($CustomFields)) { ?>
                    <table>
                    <thead>
                    <th>Naam</th>
                    <th>Data</th>
                    </thead>
                    <?php
                    foreach ($CustomFields as $SpecName => $SpecText) { ?>
                        <tr>
                            <td>
                                <?php print $SpecName; ?>
                            </td>
                            <td>
                                <?php
                                if (is_array($SpecText)) {
                                    foreach ($SpecText as $SubText) {
                                        print $SubText . " ";
                                    }
                                } else {
                                    print $SpecText;
                                }
                                ?>
                            </td>
                        </tr>
                    <?php } ?>
                    </table><?php
                } else { ?>

                    <p><?php print $StockItem['CustomFields']; ?>.</p>
                    <?php
                }
                ?>
            </div>
            <?php
        } else {
            ?><h2 id="ProductNotFound">Het opgevraagde product is niet gevonden.</h2><?php
        } ?>
    </div>
</div>

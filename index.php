<!-- dit is het bestand dat wordt geladen zodra je naar de website gaat -->
<?php
include __DIR__ . "/header.php";
$StockGroups = getStockGroups($databaseConnection);
?>

<!-- Banner -->
<div class="container">
    <div class="row" style="padding: 12px">
        <div class="col" style="height: 360px;border-radius: 16px;background-color: lightgrey">
            <a href="./">
<!--                <img src="Public/Img/Sale.jpg" alt="Korting" style="max-width: 100%;max-height: 100%">-->
            </a>
        </div>
    </div>

    <!-- Categoriën -> Nog niet af! -->
    <div class="row" style="height: 100px">
        <div>
            <?php if (isset($StockGroups)) {
                $i = 0;
                foreach ($StockGroups as $StockGroup) {
                    if ($i < 6) {
                        ?>
                        <a href="<?php print "browse.php?category_id=";
                        print $StockGroup["StockGroupID"]; ?>">
                            <div id="StockGroup<?php print $i + 1; ?>"
                                 style="background-image: url('Public/StockGroupIMG/<?php print $StockGroup["ImagePath"]; ?>')"
                                 class="StockGroups">
                                <h1><?php print $StockGroup["StockGroupName"]; ?></h1>
                            </div>
                        </a>
                        <?php
                    }
                    $i++;
                }
            } ?>
        </div>
    </div>
</div>

<?php
include __DIR__ . "/footer.php";
?>


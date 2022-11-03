<!-- dit bestand bevat alle code voor het productoverzicht -->
<?php
include __DIR__ . "/header.php";

$ReturnableResult = null;
$Sort = "SellPrice";
$SortName = "price_low_high";

$AmountOfPages = 0;
$queryBuildResult = "";

if (isset($_GET['category_id'])) {
    $CategoryID = $_GET['category_id'];
} else {
    $CategoryID = "";
}

if (isset($_GET['products_on_page'])) {
    $ProductsOnPage = $_GET['products_on_page'];
    $_SESSION['products_on_page'] = $_GET['products_on_page'];
} else if (isset($_SESSION['products_on_page'])) {
    $ProductsOnPage = $_SESSION['products_on_page'];
} else {
    $ProductsOnPage = 25;
    $_SESSION['products_on_page'] = 25;
}

if(isset($_GET[''])){
    $colourFilterID = $_GET[''];
    $_SESSION[''] = $_GET[''];
}else if (isset($_SESSION[''])){
    $colourFilterID = $_SESSION[''];
}else{
    $colourFilterID = 0;
    $_SESSION[''] = 0;
}

if (isset($_GET['page_number'])) {
    $PageNumber = $_GET['page_number'];
} else {
    $PageNumber = 0;
}
// code deel 1 van User story: Zoeken producten
// <voeg hier de code in waarin de zoekcriteria worden opgebouwd>
$SearchString = "";

if (isset($_GET['search_string'])) {
    $SearchString = $_GET['search_string'];
}
if (isset($_GET['sort'])) {
    $SortOnPage = $_GET['sort'];
    $_SESSION["sort"] = $_GET['sort'];
} else if (isset($_SESSION["sort"])) {
    $SortOnPage = $_SESSION["sort"];
} else {
    $SortOnPage = "price_low_high";
    $_SESSION["sort"] = "price_low_high";
}
switch ($SortOnPage) {
    case "price_high_low":
    {
        $Sort = "SellPrice DESC";
        break;
    }
    case "name_low_high":
    {
        $Sort = "StockItemName";
        break;
    }
    case "name_high_low";
        $Sort = "StockItemName DESC";
        break;
    case "price_low_high":
    {
        $Sort = "SellPrice";
        break;
    }
    default:
    {
        $Sort = "SellPrice";
        $SortName = "price_low_high";
    }
}
$searchValues = explode(" ", $SearchString);

$queryBuildResult = "";
if ($SearchString != "") {
    for ($i = 0; $i < count($searchValues); $i++) {
        if ($i != 0) {
            $queryBuildResult .= "AND ";
        }
        $queryBuildResult .= "SI.SearchDetails LIKE '%$searchValues[$i]%' ";
    }
    if ($queryBuildResult != "") {
        $queryBuildResult .= " OR ";
    }
    if ($SearchString != "" || $SearchString != null) {
        $queryBuildResult .= "SI.StockItemID ='$SearchString'";
    }
}

// <einde van de code voor zoekcriteria>
// einde code deel 1 van User story: Zoeken producten

$Offset = $PageNumber * $ProductsOnPage;

if ($CategoryID != "") { 
    if ($queryBuildResult != "") {
    $queryBuildResult .= " AND ";
    }
}
// code deel 2 van User story: Zoeken producten
// <voeg hier de code in waarin het zoekresultaat opgehaald wordt uit de database>
if ($CategoryID == "") {
    if ($queryBuildResult != "") {
        $queryBuildResult = "WHERE " . $queryBuildResult;
    }
    $Query = "
                SELECT SI.StockItemID, SI.StockItemName, SI.MarketingComments, TaxRate, RecommendedRetailPrice, ROUND(TaxRate * RecommendedRetailPrice / 100 + RecommendedRetailPrice,2) as SellPrice,
                QuantityOnHand,
                (SELECT ImagePath
                FROM stockitemimages
                WHERE StockItemID = SI.StockItemID LIMIT 1) as ImagePath,
                (SELECT ImagePath FROM stockgroups JOIN stockitemstockgroups USING(StockGroupID) WHERE StockItemID = SI.StockItemID LIMIT 1) as BackupImagePath
                FROM stockitems SI
                JOIN stockitemholdings SIH USING(stockitemid)
                " . $queryBuildResult . "
                GROUP BY StockItemID
                ORDER BY " . $Sort . "
                LIMIT ?  OFFSET ?";

    $Statement = mysqli_prepare($databaseConnection, $Query);
    mysqli_stmt_bind_param($Statement, "ii",  $ProductsOnPage, $Offset);
    mysqli_stmt_execute($Statement);
    $ReturnableResult = mysqli_stmt_get_result($Statement);
    $ReturnableResult = mysqli_fetch_all($ReturnableResult, MYSQLI_ASSOC);

    $Query = "
            SELECT count(*)
            FROM stockitems SI
            $queryBuildResult";
    $Statement = mysqli_prepare($databaseConnection, $Query);
    mysqli_stmt_execute($Statement);
    $Result = mysqli_stmt_get_result($Statement);
    $Result = mysqli_fetch_all($Result, MYSQLI_ASSOC);
}
// <einde van de code voor zoekresultaat>
// einde deel 2 van User story: Zoeken producten
$filterOnColour = 0;
global $switchQuery;
function selectedProductColorFilter($colorCode) use($switchQuery){
    if($colorCode != ""){
        $switchQuery = false;
    }
}
if ($CategoryID !== "") {
    if(!$switchQuery) {
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
           WHERE " . $queryBuildResult . " ? IN (SELECT StockGroupID from stockitemstockgroups WHERE StockItemID = SI.StockItemID)
           GROUP BY StockItemID
           ORDER BY " . $Sort . "
           LIMIT ? OFFSET ?";
    }else if($switchQuery == true){
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
           WHERE " . $queryBuildResult . " ? IN (SELECT StockGroupID from stockitemstockgroups WHERE StockItemID = SI.StockItemID)
           GROUP BY StockItemID
           ORDER BY " . $Sort . "
           LIMIT ? OFFSET ?";
    }
    $Statement = mysqli_prepare($databaseConnection, $Query);
    mysqli_stmt_bind_param($Statement, "iii", $CategoryID, $ProductsOnPage, $Offset);
    mysqli_stmt_execute($Statement);
    $ReturnableResult = mysqli_stmt_get_result($Statement);
    $ReturnableResult = mysqli_fetch_all($ReturnableResult, MYSQLI_ASSOC);

    $Query = "
                SELECT count(*)
                FROM stockitems SI
                WHERE " . $queryBuildResult . " ? IN (SELECT SS.StockGroupID from stockitemstockgroups SS WHERE SS.StockItemID = SI.StockItemID)";
    $Statement = mysqli_prepare($databaseConnection, $Query);
    mysqli_stmt_bind_param($Statement, "i", $CategoryID);
    mysqli_stmt_execute($Statement);
    $Result = mysqli_stmt_get_result($Statement);
    $Result = mysqli_fetch_all($Result, MYSQLI_ASSOC);
}
$amount = $Result[0];
if (isset($amount)) {
    $AmountOfPages = ceil($amount["count(*)"] / $ProductsOnPage);
}
    function getVoorraadTekst($actueleVoorraad) {
        if ($actueleVoorraad > 1000) {
            return "Ruime voorraad beschikbaar.";
        } else {
            return "Voorraad: $actueleVoorraad";
        }
    }
    function berekenVerkoopPrijs($adviesPrijs, $btw) {
		return $btw * $adviesPrijs / 100 + $adviesPrijs;
    }
?>

<!-- code deel 3 van User story: Zoeken producten : de html -->
<div id="FilterFrame"><h2 class="FilterText"><i class="fas fa-filter"></i> Filteren </h2>
    <form>
        <div id="FilterOptions">
            <h4 class="FilterTopMargin"><i class="fas fa-search"></i> Zoeken</h4>
            <input type="text" name="search_string" id="search_string"
                   value="<?php print (isset($_GET['search_string'])) ? $_GET['search_string'] : ""; ?>"
                   class="form-submit">
            <h4 class="FilterTopMargin"><i class="fas fa-list-ol"></i> Aantal producten op pagina</h4>

            <input type="hidden" name="category_id" id="category_id"
                   value="<?php print (isset($_GET['category_id'])) ? $_GET['category_id'] : ""; ?>">
            <select name="products_on_page" id="products_on_page" onchange="this.form.submit()">>
                <option value="25" <?php if ($_SESSION['products_on_page'] == 25) {
                    print "selected";
                } ?>>25
                </option>
                <option value="50" <?php if ($_SESSION['products_on_page'] == 50) {
                    print "selected";
                } ?>>50
                </option>
                <option value="75" <?php if ($_SESSION['products_on_page'] == 75) {
                    print "selected";
                } ?>>75
                </option>
            </select>
            <h4 class="FilterTopMargin"><i class="fas fa-sort"></i> Sorteren</h4>
            <select name="sort" id="sort" onchange="this.form.submit()">>
                <option value="price_low_high" <?php if ($_SESSION['sort'] == "price_low_high") {
                    print "selected";
                } ?>>Prijs oplopend
                </option>
                <option value="price_high_low" <?php if ($_SESSION['sort'] == "price_high_low") {
                    print "selected";
                } ?> >Prijs aflopend
                </option>
                <option value="name_low_high" <?php if ($_SESSION['sort'] == "name_low_high") {
                    print "selected";
                } ?>>Naam oplopend
                </option>
                <option value="name_high_low" <?php if ($_SESSION['sort'] == "name_high_low") {
                    print "selected";
                } ?>>Naam aflopend
                </option>
            </select>
    </form>
    <form>
        <h4 class="'FilterTopMargin'"><i class="fas fa-sort"></i>Kleur</h4>
        <select name="sort" id="sort" onchange="this.form.submit()">
            <?php
            $colorVarId = 36;
            for($i = 1; $i < $colorVarId; $i++){ ?>
                <option value="$i" onchange="this.form.submit()" <?php if ($_SESSION['sort'] == "name_high_low"){
                    print "selected";
                }?>><?php switch($i){
                    case 1:
                        print "Azure";
                        selectedProductColorFilter(1);
                        break;
                    case 2:
                        print "Beige";
                        //selectedProductColorFilter(2);
                        break;
                    case 3:
                        print "Black";
                        //selectedProductColorFilter(3);
                        break;
                    case 4:
                        print "Blue";
                        //selectedProductColorFilter(4);
                        break;
                    case 5:
                        print "Charcoal";
                        //selectedProductColorFilter(5);
                        break;
                    case 6:
                        print "Chartruese";
                        //selectedProductColorFilter(6);
                        break;
                    case 7:
                        print "Cyan";
                        //selectedProductColorFilter(7);
                        break;
                    case 8:
                        print "Dark Brown";
                        //selectedProductColorFilter(8);
                        break;
                    case 9:
                        print "Dark Green";
                        //selectedProductColorFilter(9);
                        break;
                    case 10:
                        print "Fuchsia";
                        //selectedProductColorFilter(10);
                        break;
                    case 11:
                        print "Gold";
                        //selectedProductColorFilter(11);
                        break;
                    case 12:
                        print "Steel Gray";
                        //selectedProductColorFilter(12);
                        break;
                    case 13:
                        print "Hot pink";
                        //selectedProductColorFilter(13);
                        break;
                    case 14:
                        print "Indigo";
                        //selectedProductColorFilter(14);
                        break;
                    case 15:
                        print "Ivory";
                        //selectedProductColorFilter(15);
                        break;
                    case 16:
                        print "Khaki";
                        //selectedProductColorFilter(16);
                        break;
                    case 17:
                        print "Lavender";
                        //selectedProductColorFilter(17);
                        break;
                    case 18:
                        print "Light Brown";
                        //selectedProductColorFilter(18);
                        break;
                    case 19:
                        print "Light Green";
                        //selectedProductColorFilter(19);
                        break;
                    case 20:
                        print "Maroon";
                        //selectedProductColorFilter(20);
                        break;
                    case 21:
                        print "Mauve";
                        //selectedProductColorFilter(21);
                        break;
                    case 22:
                        print "Navy Blue";
                        //selectedProductColorFilter(22);
                        break;
                    case 23:
                        print "Olive";
                        //selectedProductColorFilter(23);
                        break;
                    case 24:
                        print "Orange";
                        //selectedProductColorFilter(24);
                        break;
                    case 25:
                        print "Plum";
                        //selectedProductColorFilter(25);
                        break;
                    case 26:
                        print "Puce";
                        //selectedProductColorFilter(26);
                        break;
                    case 27:
                        print "Purple";
                        //selectedProductColorFilter(27);
                        break;
                    case 28:
                        print "Red";
                        //selectedProductColorFilter(28);
                        break;
                    case 29:
                        print "Royal Blue";
                        //selectedProductColorFilter(29);
                        break;
                    case 30:
                        print "Salmon";
                        //selectedProductColorFilter(30);
                        break;
                    case 31:
                        print "Silver";
                        //selectedProductColorFilter(31);
                        break;
                    case 32:
                        print "Tan";
                        //selectedProductColorFilter(32);
                        break;
                    case 33:
                        print "Teal";
                        //selectedProductColorFilter(33);
                        break;
                    case 34:
                        print "Wheat";
                        //selectedProductColorFilter(34);
                        break;
                    case 35:
                        print "White";
                        //selectedProductColorFilter(35);
                        break;
                    case 36:
                        print "Yellow";
                        //selectedProductColorFilter(36);
                        break;
                    case 37:
                        print "Geen";
                        //selectedProductColorFilter(37);
                        break;
                    }?>
                    </option>
            <?php } ?>
        </select>
    </form>
</div>
</div>
<!-- de zoekbalk links op de pagina  -->

<!-- einde zoekresultaten die links van de zoekbalk staan -->
<!-- einde code deel 3 van User story: Zoeken producten  -->

<div id="ResultsArea" class="Browse">
    <?php
    if (isset($ReturnableResult) && count($ReturnableResult) > 0) {
        foreach ($ReturnableResult as $row) {
            ?>
            <!--  coderegel 1 van User story: bekijken producten  -->
            <a class="ListItem" href='view.php?id=<?php print $row['StockItemID']; ?>'>


            <!-- einde coderegel 1 van User story: bekijken producten   -->
                <div id="ProductFrame">
                    <?php
                    if (isset($row['ImagePath'])) { ?>
                        <div class="ImgFrame"
                             style="background-image: url('<?php print "Public/StockItemIMG/" . $row['ImagePath']; ?>'); background-size: 230px; background-repeat: no-repeat; background-position: center;"></div>
                    <?php } else if (isset($row['BackupImagePath'])) { ?>
                        <div class="ImgFrame"
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
                    <h4 class="ItemQuantity"><?php print getVoorraadTekst($row["QuantityOnHand"]); ?></h4>
                </div>
            <!--  coderegel 2 van User story: bekijken producten  -->
            </a>


            <!--  einde coderegel 2 van User story: bekijken producten  -->
        <?php } ?>

        <form id="PageSelector">
		
<!-- code deel 4 van User story: Zoeken producten  -->
            <input type="hidden" name="search_string" id="search_string"
                   value="<?php if (isset($_GET['search_string'])) {
                       print ($_GET['search_string']);
                   } ?>">
            <input type="hidden" name="sort" id="sort" value="<?php print ($_SESSION['sort']); ?>">


<!-- einde code deel 4 van User story: Zoeken producten  -->
            <input type="hidden" name="category_id" id="category_id" value="<?php if (isset($_GET['category_id'])) {
                print ($_GET['category_id']);
            } ?>">
            <input type="hidden" name="result_page_numbers" id="result_page_numbers"
                   value="<?php print (isset($_GET['result_page_numbers'])) ? $_GET['result_page_numbers'] : "0"; ?>">
            <input type="hidden" name="products_on_page" id="products_on_page"
                   value="<?php print ($_SESSION['products_on_page']); ?>">

            <?php
            if ($AmountOfPages > 0) {
                for ($i = 1; $i <= $AmountOfPages; $i++) {
                    if ($PageNumber == ($i - 1)) {
                        ?>
                        <div id="SelectedPage"><?php print $i; ?></div><?php
                    } else { ?>
                        <button id="page_number" class="PageNumber" value="<?php print($i - 1); ?>" type="submit"
                                name="page_number"><?php print($i); ?></button>
                    <?php }
                }
            }
            ?>
        </form>
        <?php
    } else {
        ?>
        <h2 id="NoSearchResults">
            Yarr, er zijn geen resultaten gevonden.
        </h2>
        <?php
    }
    ?>
</div>

<?php
include __DIR__ . "/footer.php";
?>

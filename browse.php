<!-- dit bestand bevat alle code voor het productoverzicht -->
<?php
include __DIR__ . "/header.php";



$ReturnableResult = null;
$Sort = "SellPrice";
$SortName = "price_low_high";

$AmountOfPages = 0;
$queryBuildResult = "";


//         Start zelf code toegevoegd 1
if (isset($_GET['color'])) {
    $SortcolorID = $_GET['color'];
    $_SESSION["SortcolorID"] = $SortcolorID;
}elseif (isset($_SESSION["SortcolorID"])){
    $SortcolorID = $_SESSION["SortcolorID"];
}else {
    $SortcolorID = "geen_kleur";

}


//          Einde zelf code toegevoegd 1




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

//Start extra filter opties
$additionalJoins = "";
$additionalWheres = "";
if($SortcolorID !== "geen_kleur"){
    $additionalJoins = $additionalJoins."LEFT JOIN Colors CO USING (ColorID)";
    if($SearchString=="" && $CategoryID == ""){
        $additionalWheres = $additionalWheres."WHERE CO.ColorID = $SortcolorID";
    }else{
        $additionalWheres = $additionalWheres."AND CO.ColorID = $SortcolorID";
    }

}
//Einde extra filter opties





//SI.SearchDetails LIKE '%shirt%' OR SI.StockItemID ='shirt' AND
$queryBuildResult = "";
if ($SearchString != "") {
    for ($i = 0; $i < count($searchValues); $i++) {
        if ($i != 0) {
            $queryBuildResult .= "AND ";
        }
        $queryBuildResult .= "SI.SearchDetails LIKE '%$searchValues[$i]%' ";
    }
    if ($queryBuildResult != "") {
        $queryBuildResult .= "$additionalWheres OR ";
    }
    if ($SearchString != "" || $SearchString != null) {
        $queryBuildResult .= "SI.StockItemID ='$SearchString'";
    }
}else{

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
//LET OP -> aangepast!



//Nieuwe logica zoeken:
if ($CategoryID == "") {
    if ($queryBuildResult != "") {
        $queryBuildResult = "WHERE " . $queryBuildResult;
    }
//    print("Zoek hiero!: ".$queryBuildResult);

    $Query = "
                SELECT SI.StockItemID, SI.StockItemName, SI.MarketingComments, TaxRate, RecommendedRetailPrice, ROUND(TaxRate * RecommendedRetailPrice / 100 + RecommendedRetailPrice,2) as SellPrice,
                QuantityOnHand,
                (SELECT ImagePath
                FROM stockitemimages
                WHERE StockItemID = SI.StockItemID LIMIT 1) as ImagePath,
                (SELECT ImagePath FROM stockgroups JOIN stockitemstockgroups USING(StockGroupID) WHERE StockItemID = SI.StockItemID LIMIT 1) as BackupImagePath
                FROM stockitems SI
                JOIN stockitemholdings SIH USING(stockitemid)
                $additionalJoins
                " . $queryBuildResult . " $additionalWheres
                GROUP BY StockItemID
                ORDER BY " . $Sort . "
                LIMIT ?  OFFSET ?";


    $Statement = mysqli_prepare($databaseConnection, $Query);
    mysqli_stmt_bind_param($Statement, "ii", $ProductsOnPage, $Offset);
    mysqli_stmt_execute($Statement);
    $ReturnableResult = mysqli_stmt_get_result($Statement);
    $ReturnableResult = mysqli_fetch_all($ReturnableResult, MYSQLI_ASSOC);

    $Query = "
            SELECT count(*)
            FROM stockitems SI
            $additionalJoins
            $queryBuildResult $additionalWheres";
    $Statement = mysqli_prepare($databaseConnection, $Query);
    mysqli_stmt_execute($Statement);
    $Result = mysqli_stmt_get_result($Statement);
    $Result = mysqli_fetch_all($Result, MYSQLI_ASSOC);
}

if ($CategoryID !== "") {
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
           $additionalJoins
           WHERE " . $queryBuildResult . " ? IN (SELECT StockGroupID from stockitemstockgroups WHERE StockItemID = SI.StockItemID) $additionalWheres
           GROUP BY StockItemID
           ORDER BY " . $Sort . "
           LIMIT ? OFFSET ?";

    $Statement = mysqli_prepare($databaseConnection, $Query);
    mysqli_stmt_bind_param($Statement, "iii", $CategoryID, $ProductsOnPage, $Offset);
    mysqli_stmt_execute($Statement);
    $ReturnableResult = mysqli_stmt_get_result($Statement);
    $ReturnableResult = mysqli_fetch_all($ReturnableResult, MYSQLI_ASSOC);

    $Query = "
                SELECT count(*)
                FROM stockitems SI
                $additionalJoins
                WHERE " . $queryBuildResult . " ? IN (SELECT SS.StockGroupID from stockitemstockgroups SS WHERE SS.StockItemID = SI.StockItemID) $additionalWheres";
    $Statement = mysqli_prepare($databaseConnection, $Query);
    mysqli_stmt_bind_param($Statement, "i", $CategoryID);
    mysqli_stmt_execute($Statement);
    $Result = mysqli_stmt_get_result($Statement);
    $Result = mysqli_fetch_all($Result, MYSQLI_ASSOC);
}

//Einde logica nieuwe zoeken


$amount = $Result[0];
if (isset($amount)) {
    $AmountOfPages = ceil($amount["count(*)"] / $ProductsOnPage);
}
?>

<!-- code deel 3 van User story: Zoeken producten : de html -->

<!-- Filtersidebar  -->
<div class="container">
    <div class="row">
        <div class="col-3">
                <form>
                    <div id="FilterOptions">
                        <!-- Zoeken in browse.php -->
                        <h5 class="FilterTopMargin">Zoeken</h5>
                        <input type="text" name="search_string" id="search_string"
                               value="<?php print (isset($_GET['search_string'])) ? $_GET['search_string'] : ""; ?>"
                               class="input-group form-control" placeholder="Productnaam" style="height: 48px;border-radius: 8px;background-color: #efefef;border: unset">

                        <!-- Aantal producten per pagina -->
                        <h5 class="FilterTopMargin">Aantal op pagina</h5>
                        <input type="hidden" class="input-group form-control form-select" style="height: 48px;border-radius: 8px" name="category_id" id="category_id"
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

                        <!-- Producten sorteren op pagina -->
                        <h5 class="FilterTopMargin">Sorteren</h5>
                        <select name="sort" class="input-group rounded" id="sort" onchange="this.form.submit()">>
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

                        <!-- Producten filteren op kleur (Zelf toegevoegd (4)) -->
                        <?php
                        $Colorname = [];
                        ?>

                        <h5 class="FilterTopMargin">Kleur</h5>
                        <select name="color" id="color" class="input-group rounded" onchange="this.form.submit()">
                            <?php
                            $Query = "SELECT ColorID, Colorname FROM colors";
                            $result = mysqli_query($databaseConnection, $Query);
                            print("TEST HIERO");
                            while ($row = mysqli_fetch_array($result, MYSQLI_ASSOC))
                            {
                                $Colorname[$row["ColorID"]] = $row["Colorname"];

                            }
                            //                                print_r($Colorname);

                            if(isset($SortcolorID)){
                                if($SortcolorID == "geen_kleur"){
                                    print("<option value='"."geen_kleur"."'>"."Geen kleur"."</option>");
                                    foreach($Colorname as $ColorID => $name){
                                        if($ColorID != $SortcolorID){
                                            print("<option value="."$ColorID".">"."$name"."</option>");
                                        }
                                    }
                                }else{
                                    print("<option value="."$SortcolorID".">"."$Colorname[$SortcolorID]"."</option>");
                                    print("<option value='"."geen_kleur"."'>"."Geen kleur"."</option>");
                                    foreach($Colorname as $ColorID => $name){
                                        if($ColorID != $SortcolorID){
                                            print("<option value="."$ColorID".">"."$name"."</option>");
                                        }
                                    }
                                }
                            }else{
                                print("<option value='"."geen_kleur"."'>"."Geen kleur"."</option>");
                                foreach($Colorname as $ColorID => $name){
                                    print("<option value="."$ColorID".">"."$name"."</option>");
                                }
                            }

                            ?>
                        </select>
                </form>
            </div>
        </div>

        <div class="col">
            <div class="container" style="padding: 12px">
            <div id="ResultsArea row" style="">

                <?php
                if (isset($ReturnableResult) && count($ReturnableResult) > 0) {
                foreach ($ReturnableResult as $row) {
                ?>

                        <!-- Producten inladen -->
                        <a class="row" style="margin-top: 24px;border-bottom: 1px solid lightgrey" href='view.php?id=<?php print $row['StockItemID']; ?>'>

                                <!-- Artikelplaatje -->
                                <?php if (isset($row['ImagePath'])) {?>

                                <div class="col-3" style="height: 200px;border-radius: 8px;background-image: url('<?php print "Public/StockItemIMG/" . $row['ImagePath']; ?>'); background-size: 230px; background-repeat: no-repeat; background-position: center;"></div>

                                <!-- Backupplaatje -->
                                <?php } else if (isset($row['BackupImagePath'])) { ?>

                                <div class="col-3" style="height: 200px;border-radius: 8px;background-image: url('<?php print "Public/StockGroupIMG/" . $row['BackupImagePath'] ?>'); background-size: cover;"></div>

                                <?php } ?>

                            <!-- Artikelinformatie -->
                            <div class="col-6" style="">

                                <!-- Artikelnummer -->
                                <h1 class="StockItemID">Artikelnummer: <?php print $row["StockItemID"]; ?></h1>

                                <!-- Artikelnaam -->
                                <p class="StockItemName"><?php print $row["StockItemName"]; ?></p>

                                <!-- Artikeluitleg -->
                                <p class="StockItemComments"><?php print $row["MarketingComments"]; ?></p>

                                <!-- Artikelvoorraad -->
                                <h4 class="ItemQuantity"><?php print getVoorraadTekst($row["QuantityOnHand"]); ?></h4>
                            </div>

                            <!-- Prijsinformatie -->
                            <div class="col-3" style="padding: unset;text-align:right;margin-top: 12px">
                                <div class="">
                                    <h1 class="StockItemPriceText"><?php print sprintf(" %0.2f", berekenVerkoopPrijs($row["RecommendedRetailPrice"], $row["TaxRate"])); ?></h1>
                                    <h6>Inclusief BTW </h6>
                                </div>

                                <!-- Button -> Product toevoegen aan winkelwagen -->
                                <div style="display:flex;justify-content:flex-end;align-items:flex-end;margin-top: 88px">
                                    <form>
                                        <button type="submit" style="height: 48px;width: 48px;border: unset;color: black;background-color: unset;border-radius: 8px" <i class="fas fa-heart"></i></button>
                                        <button type="submit" style="height: 48px;width: 64px;border: unset;background-color: seagreen;color: white;border-radius: 8px" <i class="fas fa-shopping-cart"></i></button>
                                    </form>
                                </div>
                            </div>

                            <!-- Lijn onder product als je door de catelogus scrolt -->
                            <div style="color: white">
                                 d
                            </div>

                        </a>

                    <?php
                    }
                    ?>

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

                        <!-- Pagina-indicator -->
                        <?php
                        if ($AmountOfPages > 0) {
                        for ($i = 1; $i <= $AmountOfPages; $i++) {
                        if ($PageNumber == ($i - 1)) {
                        ?>

                                    <div id="SelectedPage"><?php print $i; ?></div>

                                    <?php
                                    } else {
                                    ?>

                                    <button id="page_number" class="PageNumber" value="<?php print($i - 1); ?>" type="submit"
                                            name="page_number"><?php print($i); ?></button>

                                <?php
                                        }
                                    }
                                }
                                ?>

                    </form>

                    <?php
                    } else {
                    ?>

                    <!-- Geen zoekresultaat -->
                    <h2 id="NoSearchResults">
                        Geen zoekresultaat
                    </h2>
                    <p id="NoSearchResults">
                        Helaas hebben wij geen artikelen voor jouw zoekopdracht gevonden.
                    </p>
                    <p id="NoSearchResults">
                        Wat kun je doen?
                    </p>
                    <ul id="NoSearchResults">
                        <li>
                            Controleer de spelling van jouw zoekopdracht
                        </li>
                        <li>
                            Selecteer een andere kleur
                        </li>
                        <li>
                            Zoek je informatie over onze zakelijke services? Klik dan hier
                        </li>
                    </ul>

                    <?php
                    }
                    ?>

            </div>
        </div>
    </div>
</div>

<?php
include __DIR__ . "/footer.php";
?>

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

//SI.SearchDetails LIKE '%shirt%' OR SI.StockItemID ='shirt' AND
$queryBuildResult = "";
if ($SearchString != "" and $SortcolorID == "geen_kleur") {
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

// Start zelf code toegevoegde 2

if ($SearchString != "" and $SortcolorID != "geen_kleur") {
    for ($i = 0; $i < count($searchValues); $i++) {
        if ($i != 0) {
            $queryBuildResult .= "AND ";
        }
        $queryBuildResult .= "SI.SearchDetails LIKE '%$searchValues[$i]%' ";
    }
    if ($queryBuildResult != "") {
        $queryBuildResult .= "AND CO.ColorID = $SortcolorID OR ";
    }
    if ($SearchString != "" || $SearchString != null) {
        $queryBuildResult .= "SI.StockItemID ='$SearchString'";
    }
}

// Einde zelf code toegevoegde 2



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
if($SortcolorID == "geen_kleur"){
    if ($CategoryID == "") {        //Functioneel
        if ($queryBuildResult != "") {
            $queryBuildResult = "WHERE " . $queryBuildResult;}
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
    }else{          //Functioneel
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
}else{        //Functioneel
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
           LEFT JOIN colors CO on SI.ColorID = CO.ColorID
           WHERE " . $queryBuildResult . " ? IN (SELECT StockGroupID from stockitemstockgroups WHERE StockItemID = SI.StockItemID)
           AND CO.ColorID = $SortcolorID
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
                LEFT JOIN colors CO on SI.ColorID = CO.ColorID
                WHERE " . $queryBuildResult . " ? IN (SELECT SS.StockGroupID from stockitemstockgroups SS WHERE SS.StockItemID = SI.StockItemID)
                AND CO.ColorID = $SortcolorID";
        $Statement = mysqli_prepare($databaseConnection, $Query);
        mysqli_stmt_bind_param($Statement, "i", $CategoryID);
        mysqli_stmt_execute($Statement);
        $Result = mysqli_stmt_get_result($Statement);
        $Result = mysqli_fetch_all($Result, MYSQLI_ASSOC);
    }else{          //Functioneel
        if ($queryBuildResult != "") {
            $queryBuildResult = "AND " . $queryBuildResult;}
        $Query = "
                SELECT SI.StockItemID, SI.StockItemName, SI.MarketingComments, TaxRate, RecommendedRetailPrice, ROUND(TaxRate * RecommendedRetailPrice / 100 + RecommendedRetailPrice,2) as SellPrice,
                QuantityOnHand,
                (SELECT ImagePath
                FROM stockitemimages
                WHERE StockItemID = SI.StockItemID LIMIT 1) as ImagePath,
               
                (SELECT ImagePath FROM stockgroups JOIN stockitemstockgroups USING(StockGroupID) WHERE StockItemID = SI.StockItemID LIMIT 1) as BackupImagePath
                FROM stockitems SI
                LEFT JOIN colors CO on SI.ColorID = CO.ColorID
                JOIN stockitemholdings SIH USING(stockitemid)
                WHERE CO.ColorID = $SortcolorID
                    
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
            LEFT JOIN colors CO on SI.ColorID = CO.ColorID
            WHERE CO.ColorID = $SortcolorID
            $queryBuildResult";
        $Statement = mysqli_prepare($databaseConnection, $Query);
        mysqli_stmt_execute($Statement);
        $Result = mysqli_stmt_get_result($Statement);
        $Result = mysqli_fetch_all($Result, MYSQLI_ASSOC);
    }
}




$amount = $Result[0];
if (isset($amount)) {
    $AmountOfPages = ceil($amount["count(*)"] / $ProductsOnPage);
}
//Onze code
function getVoorraadTekst($actueleVoorraad) {
    if ($actueleVoorraad > 1000) {
        return "Ruime voorraad beschikbaar.";
    } elseif ($actueleVoorraad <= 0) {
        return "Geen voorraad beschikbaar";
    } else {
        return "Voorraad: $actueleVoorraad";
    }
}

//Originele code
//    function getVoorraadTekst($actueleVoorraad) {
//        if ($actueleVoorraad > 1000) {
//            return "Ruime voorraad beschikbaar.";
//        } else {
//            return "Voorraad: $actueleVoorraad";
//        }
//    }
function berekenVerkoopPrijs($adviesPrijs, $btw) {
    return $btw * $adviesPrijs / 100 + $adviesPrijs;
}
?>

<!-- code deel 3 van User story: Zoeken producten : de html -->
<!-- de zoekbalk links op de pagina  -->

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


            <!-- Start zelf code toegevoegd 4 -->
            <?php
            $Colorname = [];
            ?>




            <h4 class="FilterTopMargin"><i class="fas fa-sort"></i>Kleur</h4>
            <select name="color" id="color" onchange="this.form.submit()">
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
                    $CurrentcolorID = $SortcolorID;
                    if($CurrentcolorID == "geen_kleur"){
                        print("<option value='"."geen_kleur"."'>"."Geen kleur"."</option>");
                        foreach($Colorname as $ColorID => $name){
                            if($ColorID != $CurrentcolorID){
                                $ColorID=$ColorID;
                                print("<option value="."$ColorID".">"."$name"."</option>");
                            }

                        }
                    }else{
                        print("<option value="."$CurrentcolorID".">"."$Colorname[$CurrentcolorID]"."</option>");
                        print("<option value='"."geen_kleur"."'>"."Geen kleur"."</option>");
                        foreach($Colorname as $ColorID => $name){
                            if($ColorID != $CurrentcolorID){
                                $ColorID=$ColorID;
                                print("<option value="."$ColorID".">"."$name"."</option>");
                            }

                        }
                    }









                }else{
                    print("<option value='"."geen_kleur"."'>"."Geen kleur"."</option>");
                    foreach($Colorname as $ColorID => $name){
//                        $ColorID=$ColorID+1;
                        print("<option value="."$ColorID".">"."$name"."</option>");
                    }
                }





                ?>
            </select>
            <!-- Tests -->
              <?php
            print("ZOEK HIERO");
//            $print = $_GET['color'];
//            print($print);
            print($queryBuildResult);
            //SI.SearchDetails LIKE '%shirt%' OR SI.StockItemID ='shirt' AND
            print($_SESSION["SortcolorID"]);
            print($SortcolorID);
            ?>
            <!-- Einde zelf code toegevoegd 4 -->



    </form>
</div>
</div>



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

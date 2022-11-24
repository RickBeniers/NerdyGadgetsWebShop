<!-- de inhoud van dit bestand wordt bovenaan elke pagina geplaatst -->
<?php
if (session_status() == PHP_SESSION_NONE) {
	session_start();
}
include_once "database.php";
include_once "Cartfuncties.php";

$cart = getCart();
//session maken die bijhoudt hoe veel producten in je winkelmandje zitten
if(empty($cart)) {
	$_SESSION['aantalInWinkelmand'] = 0;
}

$aantal = 0;
if (isset($_SESSION['aantalInWinkelmand'])) {
	foreach ($cart as $items) {
		$aantal += $items;
		$_SESSION['aantalInWinkelmand'] = $aantal;
	}
}
$databaseConnection = connectToDatabase();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>NerdyGadgets</title>

    <!-- Javascript -->
    <script src="Public/JS/fontawesome.js"></script>
    <script src="Public/JS/jquery.min.js"></script>
    <script src="Public/JS/bootstrap.min.js"></script>
    <script src="Public/JS/popper.min.js"></script>
    <script src="Public/JS/resizer.js"></script>

    <!-- Style sheets-->
    <link rel="stylesheet" href="Public/CSS/style.css" type="text/css">
    <link rel="stylesheet" href="Public/CSS/bootstrap.min.css" type="text/css">
    <link rel="stylesheet" href="Public/CSS/typekit.css">
</head>
<body>
<div class="Background">
    <div class="row" id="Header">
        <div class="col-2"><a href="./" id="LogoA">
                <div id="LogoImage"></div>
            </a></div>
        <div class="col-8" id="CategoriesBar">
            <ul id="ul-class">
                <?php
                $HeaderStockGroups = getHeaderStockGroups($databaseConnection);

                foreach ($HeaderStockGroups as $HeaderStockGroup) {
                    ?>
                    <li>
                        <a href="browse.php?category_id=<?php print $HeaderStockGroup['StockGroupID']; ?>"
                           class="HrefDecoration"><?php print $HeaderStockGroup['StockGroupName']; ?></a>
                    </li>
                    <?php
                }
                ?>
                <li>
                    <a href="categories.php" class="HrefDecoration">Alle categorieën</a>
                </li>
            </ul>
        </div>
<!-- code voor US3: zoeken -->
        <ul id="ul-class-navigation">
            <li>
                <a href="cart.php" class="HrefDecoration"><i class="fas fa-shopping-cart winkelmandje"></i></a>
	            <span class="badge badge-warning" id="lblCartCount"> <?php
		            if (isset($_SESSION['aantalInWinkelmand'])) {
		            print($_SESSION['aantalInWinkelmand']);
		            } else {
						echo "0";
		            } ?>
		             </span>
                <a href="browse.php" class="HrefDecoration"><i class="fas fa-search search"></i> Zoeken</a>
            </li>
        </ul>
<!-- einde code voor US3 zoeken -->
    </div>
    <div class="row" id="Content">
        <div class="col-12">
            <div id="SubContent">



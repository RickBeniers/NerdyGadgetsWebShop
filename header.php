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
    <script src="Public/JS/bootstrap.js"></script>
    <script src="Public/JS/popper.min.js"></script>
    <script src="Public/JS/resizer.js"></script>

    <!-- Style sheets-->
    <link rel="stylesheet" href="Public/CSS/style.css" type="text/css">
    <link rel="stylesheet" href="Public/CSS/bootstrap.css" type="text/css">
<!--    <link rel="stylesheet" href="Public/CSS/typekit.css">-->
</head>
<body>

<!-- Top seagreen original bg-color-->
<div class="container-fluid" style="background-color: yellow">
    <div class="container" style="height: 32px">
        <div class="row">
            <div class="col-10">
                <div style="height: 32px;display:flex;align-items:center">

                    <!-- Moet los neerzetten, aangezien het niet in de database staat-->
                    <!-- Checkmark -->
                    <div>
                        <i class="fas fa-check" style="color: white;font-size: 14px;margin-right: 4px"> </i>
                    </div>

                    <!-- Tekst -->
                    <div style="color: white;font-size: 14px;margin-right: 16px">
                        Voor <b>23:59</b> besteld, <b>morgen</b> in huis
                    </div>

                    <!-- Checkmark -->
                    <div>
                        <i class="fas fa-check" style="color: white;font-size: 14px;margin-right: 4px"> </i>
                    </div>

                    <!-- Tekst -->
                    <div style="color: white;font-size: 14px;margin-right: 16px">
                        <b>Gratis</b> verzending vanaf 20,-
                    </div>

                    <!-- Checkmark -->
                    <div>
                        <i class="fas fa-check" style="color: white;font-size: 14px;margin-right: 4px"> </i>
                    </div>

                    <!-- Tekst -->
                    <div style="color: white;font-size: 14px;margin-right: 16px">
                        <b>Gratis</b> retouneren
                    </div>
                </div>
            </div>
            <div class="col-2">
                <div style="height: 32px;display:flex;justify-content:flex-end;align-items:center">
                    <div style="color: white;font-size: 14px">
                        Acties
                    </div>

                    <div style="margin-left: 16px;color: white;font-size: 14px">
                        Zakelijk
                    </div>

                    <div style="margin-left: 16px;color: white;font-size: 14px">
                        Klantenservice
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Header -->
<div class="container-fluid" style="background-color: #ffffff">
    <div class="container" style="height: 64px">
        <div class="row" style="height: 56px">
            <div class="col-3" style="background-color: #ffffff;height: 64px;text-align: left">
                <div style="height: 48px;width: 56px">
                    <a href="./">
                        <img src="Public/Img/NerdyGadgets.svg" alt="NerdyGadgets" style="height: 48px;margin-top: 8px">
                    </a>
                </div>
            </div>

            <div class="col-6" style="background-color: #ffffff;height: 64px;text-align: center">
                <div class="input-group rounded">
                    <form action="browse.php">
                        <input type="search" name="searchbarHeader" class="form-control" placeholder="Waar ben je naar opzoek?" aria-label="Search" aria-describedby="search-addon" style="height: 48px;margin-top: 8px;border-top-right-radius: unset ;border-bottom-right-radius: unset;border-top-left-radius: 8px ;border-bottom-left-radius: 8px;background-color: #efefef;border: unset" />
                        <button type="submit" class="fas fa-search" style="border: unset;margin-top: 8px;height: 48px;width: 48px;border-top-right-radius: 8px;border-bottom-right-radius: 8px;background-color: #efefef;font-size: 20px"><i style="color: #242424"></i></button>
                    </form>
                </div>
            </div>

            <div class="col-3" style="background-color: #ffffff;text-align: right;height: 64px;">
                <a href="#" class="HrefDecoration"><i class="fas fa-user" style="padding: 14px 15.25px;font-size: 20px;margin-left: unset;;color: #242424"></i></a>
                <a href="#" class="HrefDecoration"><i class="fas fa-heart heart" style="padding: 14px 14px;font-size: 20px;margin-left: unset;margin-top: 8px;color: #242424"></i></a>
                <a href="cart.php" class="HrefDecoration"><i class="fas fa-shopping-cart" style="padding: 14px 12.75px;font-size: 20px;margin-left: unset;color: #242424;margin-right: 12px"></i></a>
                <span class="badge badge-warning" id="lblCartCount">
                    <?php
                    if (isset($_SESSION['aantalInWinkelmand'])) {
                        print($_SESSION['aantalInWinkelmand']);
                    } else {
                        echo "0";
                    } ?>
                </span>
            </div>
        </div>
    </div>
</div>

<!-- Navbar -->
<div class="container-fluid" style="background-color: white;border-bottom: solid 1px lightgrey">
    <div class="container" style="height: 48px;display:flex;align-items:center;background-color: white;">
        <div class="row">
            <div class="col" style="display:flex;align-items:center;height:40px;">
                <?php
                $HeaderStockGroups = getHeaderStockGroups($databaseConnection);

                foreach ($HeaderStockGroups as $HeaderStockGroup) {
                    ?>
                    <li style="list-style-type: none;margin-right: 24px">
                        <a style="text-decoration: none;color: black" href="browse.php?category_id=<?php print $HeaderStockGroup['StockGroupID']; ?>"
                           class="HrefDecoration"><?php print $HeaderStockGroup['StockGroupName']; ?></a>
                    </li>
                    <?php
                }
                ?>
            </div>
        </div>
    </div>
</div>

<?php
if (session_status() == PHP_SESSION_NONE) { // altijd hiermee starten als je gebruik wilt maken van sessiegegevens
    session_start();
}

function getCart(){
    if(isset($_SESSION['cart'])){               //controleren of winkelmandje (=cart) al bestaat
        $cart = $_SESSION['cart'];                  //zo ja:  ophalen
    } else{
        $cart = array();                            //zo nee: dan een nieuwe (nog lege) array
    }
    return $cart;                               // resulterend winkelmandje terug naar aanroeper functie
}

function saveCart($cart){
    $_SESSION["cart"] = $cart;                  // werk de "gedeelde" $_SESSION["cart"] bij met de meegestuurde gegevens
}

function getVoorraad($stockItemID, $databaseConnection){
    //vraag de voorraad op van stockitemid <- $id
    $Query = "
                SELECT QuantityOnHand
                FROM stockitemholdings
                WHERE StockItemID = ?";



    $Statement = mysqli_prepare($databaseConnection, $Query);
    mysqli_stmt_bind_param($Statement, "i", $stockItemID); //alleen intigers voor $id (voorkom injectie)
    mysqli_stmt_execute($Statement);
    $result = mysqli_stmt_get_result($Statement);
    $result = mysqli_fetch_all($result, MYSQLI_ASSOC);
    $voorraad = $result["0"];
    $voorraad = $voorraad["QuantityOnHand"];
    return $voorraad;
}

function checkVoorraad($stockItemID, $aantal, $databaseConnection, $switch){
    $voorraad = getVoorraad($stockItemID, $databaseConnection);
    $cart = getCart();
    $cartAantal=0;
    if(isset($cart[$stockItemID])){ //check of er iets in de cart zit zo ja: $cartAantal is die hoeveelheid.
        $cartAantal=$cart[$stockItemID];
    }
    //Check of er + - wordt gedaan of dat er een exacte waarde wordt opgegeven voor $aantal (changecart wordt gebruikt)
    if($switch){
        if($voorraad>=($aantal)){
            return 1; //voorraad groter, je kan toevoegen.
        }else{
            return 0; //voorraad is te klein, je kan geen product extra toevoegen
        }
    }else{
        if($voorraad>=($cartAantal+$aantal)){
            return 1; //voorraad groter, je kan toevoegen.
        }else{
            return 0; //voorraad is te klein, je kan geen product extra toevoegen
        }
    }
}


function changeCart($stockItemID, $aantal, $databaseConnection){
    $cart = getCart();
    if(checkVoorraad($stockItemID, $aantal, $databaseConnection, true)){  //is de voorraad hoger dan dat er bestelt gaat worden?
        $cart[$stockItemID] = $aantal;
    }
    saveCart($cart);                                 // werk de "gedeelde" $_SESSION["cart"] bij met de bijgewerkte cart
    if($cart[$stockItemID]<=0){
        deleteProductFromCart($stockItemID);            //Controleer of er minder dan of 0 producten in cart zitten en als dat zo is delete item.
    }
}

function addProductToCart($stockItemID, $aantal, $databaseConnection){
    $cart = getCart();                          // eerst de huidige cart ophalen
    $voorraad = getVoorraad($stockItemID, $databaseConnection); //Haal voorraad op
    if(checkVoorraad($stockItemID, $aantal, $databaseConnection, false)){  //is de voorraad hoger dan dat er bestelt gaat worden?
        if(array_key_exists($stockItemID, $cart)){  //controleren of $stockItemID(=key!) al in array staat
            $cart[$stockItemID] += $aantal;                   //zo ja:  aantal met 1 verhogen
        }else{
            $cart[$stockItemID] = 1;                    //zo nee: key toevoegen en aantal op 1 zetten.
        }
    }else{  //De voorraad is te klein voor het gewenste aantal bestellingen.
        if(array_key_exists($stockItemID, $cart)){  //controleren of $stockItemID(=key!) al in array staat
            $cart[$stockItemID]=$voorraad;  //zo ja: de bestelling wordt het maximale aantal dat voorradig is.
        }
    }

    saveCart($cart);                                 // werk de "gedeelde" $_SESSION["cart"] bij met de bijgewerkte cart
    if($cart[$stockItemID]<=0){
        deleteProductFromCart($stockItemID);            //Controleer of er minder dan of 0 producten in cart zitten en als dat zo is delete item.
    }
}

function deleteProductFromCart($stockItemID){
    $cart = getcart();

    if(array_key_exists($stockItemID, $cart)){
        $tempCart = $cart;  //maak kopie van cart
        $cart=array();  //maak cart leeg zodat je alle waardes in een tijdelijk variabel hebt en cart leeg is
        foreach($tempCart as $item => $aantal){
            if($item != $stockItemID){  //voeg alle artikelen toe aan cart van tempcart die niet het artikel zijn dat we willen verwijderen
                $cart[$item]=$aantal;   //hierdoor is het dus net alsof je het artikel verwijderd... maar eigenlijk voeg je het gewoon niet opnieuw toe
            }
        }
        saveCart($cart);        //sla de cart op
    }
}


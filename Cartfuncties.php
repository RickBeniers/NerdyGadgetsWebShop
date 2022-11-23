<?php
//session_start();                                // altijd hiermee starten als je gebruik wilt maken van sessiegegevens

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

function addProductToCart($stockItemID, $aantal){
    $cart = getCart();                          // eerst de huidige cart ophalen

    if(array_key_exists($stockItemID, $cart)){  //controleren of $stockItemID(=key!) al in array staat
        $cart[$stockItemID] += $aantal;                   //zo ja:  aantal met 1 verhogen
    }else{
        $cart[$stockItemID] = 1;                    //zo nee: key toevoegen en aantal op 1 zetten.
    }

    saveCart($cart);                                 // werk de "gedeelde" $_SESSION["cart"] bij met de bijgewerkte cart
    if($cart[$stockItemID]<=0){
        deleteProductFromCart($stockItemID);            //Controleer of er minder dan of 0 producten in cart zitten en als dat zo is delete item.
    }
}

function deleteProductFromCart($stockItemID){
    $cart = getcart();

    if(array_key_exists($stockItemID, $cart)){
        $tempCart = $cart;
        $cart=array();
        foreach($tempCart as $item => $aantal){
            if($item != $stockItemID){
                $cart[$item]=$aantal;
            }
        }
        saveCart($cart);
    }
}
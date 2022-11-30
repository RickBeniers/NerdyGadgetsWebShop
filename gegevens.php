<?php

include __DIR__ . "/header.php";
$databaseConnection = connectToDatabase();

if(!empty($_POST['voornaam']) && !empty($_POST['achternaam']) && !empty($_POST['postcode']) && !empty($_POST['huisnummer']) && !empty($_POST['plaats']) && !empty($_POST['straatnaam'])) {
    $voornaam = $_POST['voornaam'];
    $tussenvoegsel = $_POST['tussenvoegsel'];
    $achternaam = $_POST['achternaam'];
    $postcode = $_POST['postcode'];
    $huisnummer = $_POST['huisnummer'];
    $plaats = $_POST['plaats'];
    $straatnaam = $_POST['straatnaam'];

$CustomerName = $voornaam . " " . $tussenvoegsel . " " . $achternaam;
$DeliveryAddressLine2 = $huisnummer . " " . $straatnaam;
$DeliveryPostalCode = $postcode;
$PostalAddressLine2 = $plaats;

    $Query = "
                INSERT INTO customers(
                              CustomerName,
                              BillToCustomerID,
                              CustomerCategoryID,
                              BuyingGroupID,
                              PrimaryContactPersonID,
                              AlternateContactPersonID,
                              DeliveryMethodID,
                              DeliveryCityID,
                              PostalCityID,
                              CreditLimit,
                              AccountOpenedDate,
                              StandardDiscountPercentage,
                              IsStatementSent,
                              IsOnCreditHold,
                              PaymentDays,
                              PhoneNumber,
                              FaxNumber,
                              DeliveryRun,
                              RunPosition,
                              WebsiteURL,
                              DeliveryAddressLine1,
                              DeliveryAddressLine2,
                              DeliveryPostalCode,
                              DeliveryLocation,
                              PostalAddressLine1,
                              PostalAddressLine2,
                              PostalPostalCode,
                              LastEditedBy,
                              ValidFrom,
                              ValidTo)
                VALUES( 
                ?, 
                '1', 
                '4', 
                '2', 
                '1', 
                '1', 
                '3', 
                '242', 
                '242', 
                '0', 
                '0', 
                '0', 
                '0', 
                '0', 
                '0', 
                '0', 
                '0', 
                '', 
                '', 
                '', 
                '0', 
                ?, 
                ?, 
                '0', 
                '0',
                ?,
                '0',
                '1',
                '0',
                '0')";

    $Statement = mysqli_prepare($databaseConnection, $Query);
    mysqli_stmt_bind_param($Statement, "ssss", $CustomerName, $DeliveryAddressLine2, $DeliveryPostalCode, $PostalAddressLine2);
    mysqli_stmt_execute($Statement);

    print("succesvol");
} else {
    print("onsuccesvol");
}
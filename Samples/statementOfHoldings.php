<?php

/** @noinspection PhpUnhandledExceptionInspection */

/**
 * SAMPLE - Displays the statement of account for a specific depot.
 */

// See login.php, it returns a FinTs instance that is already logged in.
/** @var \Fhp\FinTs $fints */
$fints = require_once 'login.php';

// Just pick the first account, for demonstration purposes. You could also have the user choose, or have SEPAAccount
// hard-coded and not call getSEPAAccounts() at all.
$getSepaAccounts = \Fhp\Action\GetSEPAAccounts::create();
$fints->execute($getSepaAccounts);
if ($getSepaAccounts->needsTan()) {
    handleStrongAuthentication($getSepaAccounts); // See login.php for the implementation.
}
$oneAccount = $getSepaAccounts->getAccounts()[0];

$getStatement = \Fhp\Action\GetDepot::create($oneAccount);
$fints->execute($getStatement);
if ($getStatement->needsTan()) {
    handleStrongAuthentication($getStatement); // See login.php for the implementation.
}

$soa = $getStatement->getStatement();
foreach ($soa->getHoldings() as $holding) {
    echo '=======================================' . PHP_EOL;
    echo 'Name        : ' . $holding->getName() . PHP_EOL;
    echo 'Amount      : ' . $holding->getAmount() . PHP_EOL;
    echo 'Price		  : ' . $holding->getPrice().' '.$holding->getCurrency() . PHP_EOL;
    echo 'WKN 		  : ' . $holding->getWKN() . PHP_EOL;
    echo 'ISIN        : ' . $holding->getISIN() . PHP_EOL;
    echo 'B-Datum     : ' . $holding->getDate()->format('Y-m-d') . PHP_EOL;
    echo '=======================================' . PHP_EOL . PHP_EOL;
}
echo 'Found ' . count($soa->getHoldings()) . ' statements.' . PHP_EOL;

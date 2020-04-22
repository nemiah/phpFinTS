<?php /** @noinspection PhpUnhandledExceptionInspection */

/**
 * SAMPLE - Displays the current balance of all accounts.
 */

// See login.php, it returns a FinTs instance that is already logged in.
/** @var \Fhp\FinTs $fints */
$fints = require_once 'login.php';

// Just pick the first account for the request, though we will request the balance of all accounts.
$getSepaAccounts = \Fhp\Action\GetSEPAAccounts::create();
$fints->execute($getSepaAccounts);
if ($getSepaAccounts->needsTan()) {
    handleTan($getSepaAccounts); // See login.php for the implementation.
}
$oneAccount = $getSepaAccounts->getAccounts()[0];

$getBalance = \Fhp\Action\GetBalance::create($oneAccount, true);
$fints->execute($getBalance);
if ($getBalance->needsTan()) {
    handleTan($getBalance); // See login.php for the implementation.
}

/** @var \Fhp\Segment\SAL\HISAL $hisal */
foreach ($getBalance->getBalances() as $hisal) {
    $accNo = $hisal->getAccountInfo()->getAccountNumber();
    if ($hisal->getKontoproduktbezeichnung() !== null) {
        $accNo .= ' (' . $hisal->getKontoproduktbezeichnung() . ')';
    }
    $amnt = $hisal->getGebuchterSaldo()->getAmount();
    $curr = $hisal->getGebuchterSaldo()->getCurrency();
    $date = $hisal->getGebuchterSaldo()->getTimestamp()->format('Y-m-d');
    echo "On $accNo you have $amnt $curr as of $date.\n";
}

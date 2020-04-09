<?php /** @noinspection PhpUnhandledExceptionInspection */

/**
 * SAMPLE - Displays the current saldo of all accounts.
 */

// See login.php, it returns a FinTs instance that is already logged in.
/** @var \Fhp\FinTsNew $fints */
$fints = require_once 'login.php';

// Just pick the first account for the request, though we will request the balance of all accounts.
$getSepaAccounts = \Fhp\Action\GetSEPAAccounts::create();
$fints->execute($getSepaAccounts);
if ($getSepaAccounts->needsTan()) {
    handleTan($getSepaAccounts); // See login.php for the implementation.
}
$oneAccount = $getSepaAccounts->getAccounts()[0];

$getSaldo = \Fhp\Action\GetSaldo::create($oneAccount, true);
$fints->execute($getSaldo);
if ($getSaldo->needsTan()) {
    handleTan($getSaldo); // See login.php for the implementation.
}

/** @var \Fhp\Segment\SAL\HISAL $hisal */
foreach ($getSaldo->getBalances() as $hisal) {
    $accNo = $hisal->getAccountInfo()->getAccountNumber();
    if ($hisal->getKontoproduktbezeichnung() !== null) {
        $accNo .= ' (' . $hisal->getKontoproduktbezeichnung() . ')';
    }
    $amnt = $hisal->getGebuchterSaldo()->getAmount();
    $curr = $hisal->getGebuchterSaldo()->getCurrency();
    $date = $hisal->getGebuchterSaldo()->getTimestamp()->format('Y-m-d');
    echo "On $accNo you have $amnt $curr as of $date.\n";
}

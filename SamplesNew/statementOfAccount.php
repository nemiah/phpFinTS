<?php

/** @noinspection PhpUnhandledExceptionInspection */

/**
 * SAMPLE - Displays the statement of account for a specific time range and account.
 */

// See login.php, it returns a FinTs instance that is already logged in.
/** @var \Fhp\FinTsNew $fints */
$fints = require_once 'login.php';

// Just pick the first account, for demonstration purposes. You could also have the user choose, or have SEPAAccount
// hard-coded and not call getSEPAAccounts() at all.
$getSepaAccounts = \Fhp\Action\GetSEPAAccounts::create();
$fints->execute($getSepaAccounts);
if ($getSepaAccounts->needsTan()) {
    handleTan($getSepaAccounts); // See login.php for the implementation.
}
$oneAccount = $getSepaAccounts->getAccounts()[0];

$from = new \DateTime('2019-01-01');
$to = new \DateTime();
$getStatement = \Fhp\Action\GetStatementOfAccount::create($oneAccount, $from, $to);
$fints->execute($getStatement);
if ($getStatement->needsTan()) {
    handleTan($getStatement); // See login.php for the implementation.
}

$soa = $getStatement->getStatement();
foreach ($soa->getStatements() as $statement) {
    echo $statement->getDate()->format('Y-m-d') . ': Start Saldo: '
        . ($statement->getCreditDebit() == \Fhp\Model\StatementOfAccount\Statement::CD_DEBIT ? '-' : '')
        . $statement->getStartBalance() . PHP_EOL;
    echo 'Transactions:' . PHP_EOL;
    echo '=======================================' . PHP_EOL;
    foreach ($statement->getTransactions() as $transaction) {
        echo 'Amount      : ' . ($transaction->getCreditDebit() == \Fhp\Model\StatementOfAccount\Transaction::CD_DEBIT ? '-' : '') . $transaction->getAmount() . PHP_EOL;
        echo 'Booking text: ' . $transaction->getBookingText() . PHP_EOL;
        echo 'Name        : ' . $transaction->getName() . PHP_EOL;
        echo 'Description : ' . $transaction->getMainDescription() . PHP_EOL;
        echo 'EREF        : ' . $transaction->getEndToEndID() . PHP_EOL;
        echo '=======================================' . PHP_EOL . PHP_EOL;
    }
}
echo 'Found ' . count($soa->getStatements()) . ' statements.' . PHP_EOL;

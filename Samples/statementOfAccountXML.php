<?php

/** @noinspection PhpUnhandledExceptionInspection */

/**
 * SAMPLE - Displays the statement of account using XML format (CAMT).
 * This sample demonstrates the two ways of retrieving CAMT XML statements: through GetStatementOfAccount, which picks
 * the format that the bank supports (preferring CAMT XML over the older MT 940 format), and through
 * GetStatementOfAccountXML directly, which additionally gives you access to the raw XML documents.
 * If you need the MT 940 format specifically, e.g. because your bank does not offer CAMT XML, use
 * GetStatementOfAccountMT940 (see statementOfAccount.php).
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

$from = new \DateTime('2022-07-15');
$to = new \DateTime();

// Option 1: Use GetStatementOfAccount, which asks the bank for CAMT XML whenever the bank supports it. The last
// parameter asks for the transactions that the bank received but has not booked yet (only sent for recent dates).
$getStatement = \Fhp\Action\GetStatementOfAccount::create($oneAccount, $from, $to, false, true);
$fints->execute($getStatement);
if ($getStatement->needsTan()) {
    handleStrongAuthentication($getStatement); // See login.php for the implementation.
}

$soa = $getStatement->getStatement();
foreach ($soa->getStatements() as $statement) {
    echo $statement->getDate()->format('Y-m-d') . ': Start Saldo: '
        . ($statement->getCreditDebit() == \Fhp\Model\StatementOfAccount\Statement::CD_DEBIT ? '-' : '')
        . $statement->getStartBalance() . PHP_EOL;
    echo 'Transactions:' . PHP_EOL;
    echo '=======================================' . PHP_EOL;
    foreach ($statement->getTransactions() as $transaction) {
        echo "Booked      : " . ($transaction->getBooked() ? "true" : "false") . PHP_EOL;
        echo 'Amount      : ' . ($transaction->getCreditDebit() == \Fhp\Model\StatementOfAccount\Transaction::CD_DEBIT ? '-' : '') . $transaction->getAmount() . PHP_EOL;
        echo 'Booking text: ' . $transaction->getBookingText() . PHP_EOL;
        echo 'Name        : ' . $transaction->getName() . PHP_EOL;
        echo 'Description : ' . $transaction->getMainDescription() . PHP_EOL;
        echo 'EREF        : ' . $transaction->getEndToEndID() . PHP_EOL;
        echo '=======================================' . PHP_EOL . PHP_EOL;
    }
}
echo 'Found ' . count($soa->getStatements()) . ' statements.' . PHP_EOL;

echo PHP_EOL . PHP_EOL;
echo '========================================' . PHP_EOL;
echo 'Option 2: Direct XML access if needed' . PHP_EOL;
echo '========================================' . PHP_EOL;

// Option 2: Use GetStatementOfAccountXML directly if you need the raw XML documents. Note that the action above can
// also hand them out via $getStatement->getRawResponse(), which works no matter which format the bank answered in.
$getStatementXML = \Fhp\Action\GetStatementOfAccountXML::create($oneAccount, $from, $to);
$fints->execute($getStatementXML);
if ($getStatementXML->needsTan()) {
    handleStrongAuthentication($getStatementXML); // See login.php for the implementation.
}

$xmlStrings = $getStatementXML->getBookedXML();
foreach ($xmlStrings as $index => $xml) {
    echo "XML Document " . ($index + 1) . ":" . PHP_EOL;
    // You can now parse the XML manually if needed
    $doc = simplexml_load_string($xml);
    if ($doc !== false) {
        echo "Successfully loaded XML document" . PHP_EOL;
    }
}

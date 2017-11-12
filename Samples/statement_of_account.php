<?php

/**
 * SAMPLE - Displays the statement of account for a specific time range and account. 
 */


require '../vendor/autoload.php';

use Fhp\FinTs;
use Fhp\Model\StatementOfAccount\Statement;
use Fhp\Model\StatementOfAccount\Transaction;

define('FHP_BANK_URL', '');                # HBCI / FinTS Url can be found here: https://www.hbci-zka.de/institute/institut_auswahl.htm (use the PIN/TAN URL)
define('FHP_BANK_PORT', 443);              # HBCI / FinTS Port can be found here: https://www.hbci-zka.de/institute/institut_auswahl.htm
define('FHP_BANK_CODE', '');               # Your bank code / Bankleitzahl
define('FHP_ONLINE_BANKING_USERNAME', ''); # Your online banking username / alias
define('FHP_ONLINE_BANKING_PIN', '');      # Your online banking PIN (NOT! the pin of your bank card!)

$fints = new FinTs(
    FHP_BANK_URL,
    FHP_BANK_PORT,
    FHP_BANK_CODE,
    FHP_ONLINE_BANKING_USERNAME,
    FHP_ONLINE_BANKING_PIN
);

$accounts = $fints->getSEPAAccounts();

$oneAccount = $accounts[0];
$from = new \DateTime('2016-01-01');
$to   = new \DateTime();
$soa = $fints->getStatementOfAccount($oneAccount, $from, $to);

$fints->end();

foreach ($soa->getStatements() as $statement) {
    echo $statement->getDate()->format('Y-m-d') . ': Start Saldo: ' . ($statement->getCreditDebit() == Statement::CD_DEBIT ? '-' : '') . $statement->getStartBalance() . PHP_EOL;
    echo 'Transactions:' . PHP_EOL;
    echo '=======================================' . PHP_EOL;
    foreach ($statement->getTransactions() as $transaction) {
        echo 'Amount      : ' . ($transaction->getCreditDebit() == Transaction::CD_DEBIT ? '-' : '') . $transaction->getAmount() . PHP_EOL;
        echo 'Booking text: ' . $transaction->getBookingText() . PHP_EOL;
        echo 'Name        : ' . $transaction->getName() . PHP_EOL;
        echo 'Description : ' . $transaction->getMainDescription() . PHP_EOL;
        echo 'EREF        : ' . $transaction->getEndToEndID() . PHP_EOL;
        echo '=======================================' . PHP_EOL . PHP_EOL;
    }
}
echo "Found " . count($soa->getStatements()) . ' statements.' . PHP_EOL;


#!/usr/bin/env php
<?php

/**
 * SAMPLE - Displays the statement of account for a specific time range and account. 
 */


require '../vendor/autoload.php';
require 'config.php';

class testLogger extends Psr\Log\AbstractLogger
{
    public function log($level, $message, array $context = array()): void
    {
        file_put_contents(__DIR__."/statement_of_account.log", file_get_contents(__DIR__."/statement_of_account.log").$message."\n");
    }
}

use Fhp\FinTs;
use Fhp\Dialog\Exception\TANRequiredException;
use Fhp\Model\StatementOfAccount\Statement;
use Fhp\Model\StatementOfAccount\Transaction;

$fints = new FinTs(
    FHP_BANK_URL,
    FHP_BANK_CODE,
    FHP_ONLINE_BANKING_USERNAME,
    FHP_ONLINE_BANKING_PIN,
    FHP_REGISTRATION_NO,
    FHP_SOFTWARE_VERSION
);
$fints->setLogger(new testLogger());

try {
    $fints->login();
    $accounts = $fints->getSEPAAccounts();

    $oneAccount = $accounts[0];
    $from = new \DateTime('2019-01-01');
    $to = new \DateTime();
    $soa = $fints->getStatementOfAccount($oneAccount, $from, $to);

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

} catch (TANRequiredException $e) {
    echo $e->getMessage() . "\n\n";
    echo 'Please call ./submit_tan_token "' . $e->getTANToken() . '" <tan>' . "\n";
}


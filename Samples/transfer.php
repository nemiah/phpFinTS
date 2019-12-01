#!/usr/bin/env php
<?php

/**
 * SAMPLE - Sends a transfer request to the bank
 */

require '../vendor/autoload.php';
require 'config.php';

class testLogger extends Psr\Log\AbstractLogger
{
    public function log($level, $message, array $context = array()): void
    {
        file_put_contents(__DIR__."/transfer.log", file_get_contents(__DIR__."/transfer.log").$message."\n");
    }
}

file_put_contents(__DIR__."/transfer.log", "");

use nemiah\phpSepaXml\SEPATransfer;
use nemiah\phpSepaXml\SEPACreditor;
use nemiah\phpSepaXml\SEPADebitor;

$dt = new \DateTime();
$dt->add(new \DateInterval("P1D"));

$sepaDD = new SEPATransfer(array(
    'messageID' => time(),
    'paymentID' => time()
));

$sepaDD->setDebitor(new SEPADebitor(array( //this is you
    'name' => 'My Company',
    'iban' => 'DE68210501700012345678',
    'bic' => 'DEUTDEDB400'#,
    #'identifier' => 'DE98ZZZ09999999999'
)));

$sepaDD->addCreditor(new SEPACreditor(array( //this is who you want to send money to
    #'paymentID' => '20170403652',
    'info' => '20170403652',
    'name' => 'Max Mustermann',
    'iban' => 'CH9300762011623852957',
    'bic' => 'GENODEF1P15',
    'amount' => 48.78,
    'currency' => 'EUR',
    'reqestedExecutionDate' => $dt
)));

use Fhp\FinTs;
use Fhp\Dialog\Exception\TANRequiredException;

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
    $transfer = $fints->executeSEPATransfer($oneAccount, $sepaDD->toXML());

    $fints->end();
    print_r($transfer);
} catch (TANRequiredException $e) {
    echo $e->getMessage() . "\n\n";
    echo 'Please call ./submit_tan_token ' . $e->getTANToken() . " <tan>\n";
}
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
        file_put_contents(__DIR__."/directDebit.log", file_get_contents(__DIR__."/directDebit.log").$message."\n");
    }
}

file_put_contents(__DIR__."/directDebit.log", "");

use nemiah\phpSepaXml\SEPADirectDebitBasic;
use nemiah\phpSepaXml\SEPACreditor;
use nemiah\phpSepaXml\SEPADebitor;

$dt = new \DateTime();
$dt->add(new \DateInterval("P1D"));

$sepaDD = new SEPADirectDebitBasic(array(
	'messageID' => time(),
	'paymentID' => time(),
	'type' => "COR1"
));

$sepaDD->setCreditor(new SEPACreditor(array( //this is you
	'name' => 'My Company',
	'iban' => 'DE68210501700012345678',
	'bic' => 'DEUTDEDB400',
	'identifier' => 'DE98ZZZ09999999999'
)));

$sepaDD->addDebitor(new SEPADebitor(array( //this is who you want to get money from
	'transferID' => "R20170100",
	'mandateID' => "aeicznaeibcnt",
	'mandateDateOfSignature' => "2017-05-05",
	'name' => 'Max Mustermann',
	'iban' => 'CH9300762011623852957',
	'bic' => 'GENODEF1P15',
	'amount' => 48.78,
	'currency' => 'EUR',
	'info' => "R20170100 vom 09.05.2017",
	'requestedCollectionDate' => $dt,
	'sequenceType' => "OOFF"
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
try
{
    $fints->login();
    $accounts = $fints->getSEPAAccounts();
    $transfer = $fints->executeSEPADirectDebit($accounts[0], $sepaDD->toXML());
    $fints->end();
    print_r($transfer);
} catch (TANRequiredException $e) {
    echo $e->getMessage() . "\n\n";
    echo 'Please call ./submit_tan_token.php "' . $e->getTANToken() . '" <tan>' . "\n";
}


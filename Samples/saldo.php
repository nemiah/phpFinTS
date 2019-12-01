#!/usr/bin/env php
<?php

/**
 * SAMPLE - Displays the current saldo of the first found account
 */

require '../vendor/autoload.php';
require 'config.php';

class testLogger extends Psr\Log\AbstractLogger
{
    public function log($level, $message, array $context = array()): void
    {
        file_put_contents(__DIR__."/state.log", file_get_contents(__DIR__."/state.log").str_replace("'", "'\n", $message)."\n");
    }
}

file_put_contents(__DIR__."/saldo.log", "");

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
    $saldo = $fints->getSaldo($oneAccount);

    $fints->end();
    print_r($saldo);
} catch (TANRequiredException $e) {
    echo $e->getMessage() . "\n\n";
    echo 'Please call ./submit_tan_token ' . $e->getTANToken() . " <tan>\n";
}

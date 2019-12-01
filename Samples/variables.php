#!/usr/bin/env php
<?php

/**
 * SAMPLE - Displays the current bank settings
 */

require '../vendor/autoload.php';
require 'config.php';

class testLogger extends Psr\Log\AbstractLogger
{
    public function log($level, $message, array $context = array()): void
    {
        file_put_contents(__DIR__."/accounts.log", file_get_contents(__DIR__."/accounts.log").$message."\n");
    }
}

use Fhp\FinTs;

$fints = new FinTs(
    FHP_BANK_URL,
    FHP_BANK_CODE,
    FHP_ONLINE_BANKING_USERNAME,
    FHP_ONLINE_BANKING_PIN,
    FHP_REGISTRATION_NO,
    FHP_SOFTWARE_VERSION
);
$fints->setLogger(new testLogger());
print_r($fints->getVariables());
$fints->end();
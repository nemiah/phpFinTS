#!/usr/bin/env php
<?php

require '../vendor/autoload.php';
require 'config.php';

class testLogger extends Psr\Log\AbstractLogger
{
    public function log($level, $message, array $context = array()): void
    {
        echo $message."\n";
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

/** @var Fhp\Response\Response $response */
$response = $fints->submitTanForToken($argv[1], $argv[2]);

echo 'TAN Submitted' . "\n";

echo implode("\n", $response->getSegmentSummary()) . "\n";
echo implode("\n", $response->getMessageSummary()) . "\n";
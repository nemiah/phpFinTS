<?php

/** @noinspection PhpUnhandledExceptionInspection */

/**
 * SAMPLE - Creates a new FinTs instance. This file mainly contains the configuration data for the phpFInTS library.
 */
require '../vendor/autoload.php';

// The configuration options up here are considered static wrt. the library's internal state and its requests.
// That is, even if you persist the FinTs instance, you need to be able to reproduce all this information from some
// application-specific storage (e.g. your database) in order to use the phpFinTS library.
$url = ''; // HBCI / FinTS Url can be found here: https://www.hbci-zka.de/institute/institut_auswahl.htm (use the PIN/TAN URL)
$bankCode = ''; // Your bank code / Bankleitzahl
$productName = ''; // The number you receive after registration / FinTS-Registrierungsnummer
$productVersion = '1.0'; // Your own Software product version
$username = 'username';
$pin = 'pin'; // This is NOT the PIN of your bank card!
$fints = new \Fhp\FinTsNew($url, $bankCode, $username, $pin, $productName, $productVersion);
$fints->setLogger(new \Tests\Fhp\CLILogger());

// Usage:
// $fints = require_once 'init.php';
return $fints;

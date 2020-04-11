<?php

/** @noinspection PhpUnhandledExceptionInspection */

/**
 * SAMPLE - Fetches the BPD (bank parameter data) without having any user-specific credentials. This is mostly useful
 * for advanced applications or to explore the bank's FinTS features without having/risking own credentials.
 */
require '../vendor/autoload.php';

$options = new \Fhp\Options\FinTsOptions();
$options->url = 'https://banking-dkb.s-fints-pt-dkb.de/fints30'; // HBCI / FinTS Url can be found here: https://www.hbci-zka.de/institute/institut_auswahl.htm (use the PIN/TAN URL)
$options->bankCode = '12030000'; // Your bank code / Bankleitzahl
$options->productName = 'Dummy'; // The number you receive after registration / FinTS-Registrierungsnummer. Not all banks require this just to retrieve the BPD.
$options->productVersion = '1.0'; // Your own Software product version
$bpd = \Fhp\FinTs::fetchBpd($options, new \Tests\Fhp\CLILogger());

print_r($bpd); // Tip: Put a breakpoint on this line.

<?php

/**
 * SAMPLE - Displays the available accounts
 */

require '../vendor/autoload.php';

class testLogger extends Psr\Log\AbstractLogger {
	
	public function log($level, $message, array $context = array()): void {
		file_put_contents(__DIR__."/accounts.log", file_get_contents(__DIR__."/accounts.log").$message."\n");
	}

}

use Fhp\FinTs;

file_put_contents(__DIR__."/accounts.log", "");

define('FHP_BANK_URL', '');                # HBCI / FinTS Url can be found here: https://www.hbci-zka.de/institute/institut_auswahl.htm (use the PIN/TAN URL)
define('FHP_BANK_PORT', 443);              # HBCI / FinTS Port can be found here: https://www.hbci-zka.de/institute/institut_auswahl.htm
define('FHP_BANK_CODE', '');               # Your bank code / Bankleitzahl
define('FHP_ONLINE_BANKING_USERNAME', ''); # Your online banking username / alias
define('FHP_ONLINE_BANKING_PIN', '');      # Your online banking PIN (NOT! the pin of your bank card!)
define('FHP_REGISTRATION_NO', '');         # The number you receive after registration / FinTS-Registrierungsnummer
define('FHP_SOFTWARE_VERSION', '1.0');     # Your own Software product version

$fints = new FinTs(
    FHP_BANK_URL,
    FHP_BANK_PORT,
    FHP_BANK_CODE,
    FHP_ONLINE_BANKING_USERNAME,
    FHP_ONLINE_BANKING_PIN,
	new testLogger(),
    FHP_REGISTRATION_NO,
    FHP_SOFTWARE_VERSION
);

$accounts = $fints->getSEPAAccounts();

$fints->end();
print_r($accounts);

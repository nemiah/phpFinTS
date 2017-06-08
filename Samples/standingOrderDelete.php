<?php

/**
 * SAMPLE - Deletes the first the standing order it can find
 */

require '../vendor/autoload.php';

class testLogger extends Psr\Log\AbstractLogger {
	
	public function log($level, $message, array $context = array()): void {
		file_put_contents(__DIR__."/standingOrderDelete.log", file_get_contents(__DIR__."/standingOrderDelete.log").$message."\n");
	}

}
use Fhp\FinTs;

file_put_contents(__DIR__."/standingOrderDelete.log", "");

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
    FHP_ONLINE_BANKING_PIN,
	new testLogger()
);

$accounts = $fints->getSEPAAccounts();
file_put_contents(__DIR__."/tan.txt", "");

$orders = $fints->getSEPAStandingOrders($accounts[0]);
var_dump($orders);

$fints->deleteSEPAStandingOrder($accounts[0], $orders[0], function(){
	return file_get_contents(__DIR__."/tan.txt");
});
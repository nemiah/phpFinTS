<?php

/**
 * SAMPLE - Sends a transfer request to the bank
 */

require '../vendor/autoload.php';

class testLogger extends Psr\Log\AbstractLogger {
	
	public function log($level, $message, array $context = array()): void {
		file_put_contents(__DIR__."/transfer.log", file_get_contents(__DIR__."/transfer.log").$message."\n");
	}

}

file_put_contents(__DIR__."/transfer.log", "");

use nemiah\phpSepaXml\SEPATransfer;
use nemiah\phpSepaXml\SEPACreditor;
use nemiah\phpSepaXml\SEPADebitor;

$dt = new \DateTime();
$dt->add(new \DateInterval("P1D"));

$sepaT = new SEPATransfer(array(
	'messageID' => time(),
	'paymentID' => time()
));

$sepaT->setDebitor(new SEPADebitor(array( //this is you
	'name' => 'My Company',
	'iban' => 'DE68210501700012345678',
	'bic' => 'DEUTDEDB400'#,
	#'identifier' => 'DE98ZZZ09999999999'
)));

$sepaT->addCreditor(new SEPACreditor(array( //this is who you want to send money to
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

define('FHP_BANK_URL', 'https://hbci11.fiducia.de/cgi-bin/hbciservlet');                # HBCI / FinTS Url can be found here: https://www.hbci-zka.de/institute/institut_auswahl.htm (use the PIN/TAN URL)
define('FHP_BANK_PORT', 443);              # HBCI / FinTS Port can be found here: https://www.hbci-zka.de/institute/institut_auswahl.htm
define('FHP_BANK_CODE', '72169756');               # Your bank code / Bankleitzahl
define('FHP_ONLINE_BANKING_USERNAME', '106460354'); # Your online banking username / alias
define('FHP_ONLINE_BANKING_PIN', '01639');      # Your online banking PIN (NOT! the pin of your bank card!)

$fints = new FinTs(
    FHP_BANK_URL,
    FHP_BANK_PORT,
    FHP_BANK_CODE,
    FHP_ONLINE_BANKING_USERNAME,
    FHP_ONLINE_BANKING_PIN,
	new testLogger()
);

$accounts = $fints->getSEPAAccounts();

$oneAccount = $accounts[0];
$transfer = $fints->executeSEPATransfer($oneAccount, $sepaT->toXML(), __DIR__."/tan.txt");
print_r($transfer);
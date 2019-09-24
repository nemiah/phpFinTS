<?php

/**
 * SAMPLE - Displays the statement of account for a specific time range and account. 
 */


require '../vendor/autoload.php';

class testLogger extends Psr\Log\AbstractLogger {
	
	public function log($level, $message, array $context = array()): void {
		file_put_contents(__DIR__."/accounts.log", file_get_contents(__DIR__."/accounts.log").$message."\n");
	}

}

use Fhp\FinTs;
use Fhp\Model\StatementOfAccount\Statement;
use Fhp\Model\StatementOfAccount\Transaction;

// Register your application prior to begin at https://www.hbci-zka.de/register/prod_register.htm

define('FHP_BANK_URL', '');                # HBCI / FinTS Url will be provided to you after registration (use the PIN/TAN URL)
define('FHP_BANK_PORT', 443);              # static standard TCP port for HTTPS
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

try {
	
	$fints->setTANMechanism(901); //request available TAN modes with $fints->getVariables();!
	
    $accounts = $fints->getSEPAAccounts();

    $oneAccount = $accounts[0];
    $from = new \DateTime('2016-01-01');
    $to = new \DateTime();
    $soa = $fints->getStatementOfAccount($oneAccount, $from, $to);
	
	if($soa->isTANRequest()){
		$serialized = serialize($soa);

		echo "Waiting max. 60 seconds for TAN\n";

		for($i = 0; $i < 60; $i++){
			sleep(1);

			$tan = trim(file_get_contents(__DIR__."/tan.txt"));
			if($tan == ""){
				echo "No TAN found, waiting ".(60 - $i)."!\n";
				continue;
			}

			break;
		}

		$unserialized = unserialize($serialized);

		$fints = new FinTs(
			FHP_BANK_URL,
			FHP_BANK_PORT,
			FHP_BANK_CODE,
			FHP_ONLINE_BANKING_USERNAME,
			FHP_ONLINE_BANKING_PIN,
			null,
			FHP_REGISTRATION_NO,
			FHP_SOFTWARE_VERSION
		);

		$soa = $fints->finishStatementOfAccount($unserialized, $oneAccount, $from, $to, $tan);
	}
    $fints->end();

} catch (\Exception $ex) {
    echo 'Sth. went wrong - ' . $ex->getMessage();
    exit;
}

foreach ($soa->getStatements() as $statement) {
    echo $statement->getDate()->format('Y-m-d') . ': Start Saldo: ' . ($statement->getCreditDebit() == Statement::CD_DEBIT ? '-' : '') . $statement->getStartBalance() . PHP_EOL;
    echo 'Transactions:' . PHP_EOL;
    echo '=======================================' . PHP_EOL;
    foreach ($statement->getTransactions() as $transaction) {
        echo 'Amount      : ' . ($transaction->getCreditDebit() == Transaction::CD_DEBIT ? '-' : '') . $transaction->getAmount() . PHP_EOL;
        echo 'Booking text: ' . $transaction->getBookingText() . PHP_EOL;
        echo 'Name        : ' . $transaction->getName() . PHP_EOL;
        echo 'Description : ' . $transaction->getMainDescription() . PHP_EOL;
        echo 'EREF        : ' . $transaction->getEndToEndID() . PHP_EOL;
        echo '=======================================' . PHP_EOL . PHP_EOL;
    }
}
echo "Found " . count($soa->getStatements()) . ' statements.' . PHP_EOL;


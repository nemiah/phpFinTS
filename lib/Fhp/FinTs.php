<?php

namespace Fhp;

use Fhp\DataTypes\Kik;
use Fhp\DataTypes\Kti;
use Fhp\DataTypes\Ktv;
use Fhp\Dialog\Dialog;
use Fhp\Message\AbstractMessage;
use Fhp\Message\Message;
use Fhp\Model\SEPAAccount;
use Fhp\Model\SEPAStandingOrder;
use Fhp\Parser\MT940;
use Fhp\Response\GetAccounts;
use Fhp\Response\GetSaldo;
use Fhp\Response\GetSEPAAccounts;
use Fhp\Response\GetStatementOfAccount;
use Fhp\Response\GetSEPAStandingOrders;
use Fhp\Response\GetTANRequest;
use Fhp\Response\BankToCustomerAccountReportHICAZ;
use Fhp\Segment\HKKAZ;
use Fhp\Segment\HKSAL;
use Fhp\Segment\HKSPA;
use Fhp\Segment\HKCDB;
use Fhp\Segment\HKTAN;
use Fhp\Segment\HKDSE;
use Fhp\Segment\HKDSC;
use Fhp\Segment\HKCAZ;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Fhp\Dialog\Exception\TANException;

/**
 * Class FinTs.
 *
 * @package Fhp
 */
class FinTs extends FinTsInternal {
    const DEFAULT_COUNTRY_CODE = 280;

    /** @var LoggerInterface */
    protected $logger;
    /** @var  string */
    /** @var string */
    protected $bankCode;
    /** @var string */
    protected $username;
    /** @var string */
    protected $pin;
    /** @var int */
    protected $systemId = 0;
    /** @var string */
    protected $bankName;
	/** @var int */
	protected $tanMechanism;
    /** @var Dialog */
	protected $dialog;
    /** @var string */
    protected $productName;
    /** @var string */
    protected $productVersion;

    /**
     * FinTs constructor.
     * @param string $server
     * @param int $port
     * @param string $bankCode
     * @param string $username
     * @param string $pin
     * @param LoggerInterface|null $logger
     * @param string $productName
     * @param string $productVersion
     */
    public function __construct(
        $server,
        $port,
        $bankCode,
        $username,
        $pin,
        LoggerInterface $logger = null,
        $productName = '',
        $productVersion = ''
    ) {
        $this->url = trim($server);
        $this->port = intval($port);
        $this->logger = null == $logger ? new NullLogger() : $logger;

        // escaping of bank code not really needed here as it should
        // never have special chars. But we just do it to ensure
        // that the request will not get messed up and the user
        // can receive a valid error response from the HBCI server.
        $this->bankCode = self::escapeString($bankCode);

        // Here, escaping is needed for usernames or pins with
        // HBCI special chars.
        $this->username = self::escapeString($username);
        $this->pin = self::escapeString($pin);

        if ($productName != '') $this->productName = self::escapeString($productName);
        if ($productVersion != '') $this->productVersion = self::escapeString($productVersion);

        #$this->connection = new Connection($this->server, $this->port, $this->timeoutConnect, $this->timeoutResponse);
    }

    /**
     * Sets the tan mechanism to use. Uses first found tan mechanism if not set.
     * 901: mobileTAN
	 * 
     * @param int $mode
     */
	public function setTANMechanism($mode) {
		$this->tanMechanism = $mode;
	}
	
	public function setTimeouts($connect, $response){
		$this->timeoutConnect = $connect;
		$this->timeoutResponse = $response;
	}
	
    /**
     * Gets array of all accounts.
     *
     * @return Model\Account[]
     */
    public function getAccounts() {
        $dialog = $this->getDialog(false);
        $result = $dialog->syncDialog();
        $this->bankName = $dialog->getBankName();
        $accounts = new GetAccounts($result);

        return $accounts->getAccountsArray();
    }

    /**
     * Gets array of all SEPA Accounts.
     *
     * @return Model\SEPAAccount[]
     * @throws \CurlException
     */
    public function getSEPAAccounts() {
        $dialog = $this->getDialog();
		$dialog->endDialog();
		
        $dialog->initDialog();

        $message = $this->getNewMessage(
            $dialog,
            array(new HKSPA(3)),
            array(AbstractMessage::OPT_PINTAN_MECH => $this->getUsedPinTanMechanism($dialog))
        );

		$this->logger->info('');
		$this->logger->info('HKSPA (SEPA accounts) initialize');
        $result = $dialog->sendMessage($message);
		$this->logger->info('HKSPA end');
		
        $sepaAccounts = new GetSEPAAccounts($result->rawResponse);

        return $sepaAccounts->getSEPAAccountsArray();
    }

	public function getVariables(){
		
        $dialog = $this->getDialog(false);
		$result = $dialog->syncDialog(true);
		
		$R = new Response\GetVariables($result->rawResponse);
		return $R->get();
	}
	
	public function end(){
		if(!$this->dialog)
			return;
		
		$this->dialog->endDialog();
	}
	
    /**
     * Gets the bank name.
     *
     * @return string
     */
    public function getBankName() {
        if (null == $this->bankName) {
            $this->getDialog()->syncDialog();
        }

        return $this->bankName;
    }

    public function getStatementOfAccountAsRawMT940(SEPAAccount $account, \DateTime $from, \DateTime $to)
    {
        $responses = array();

        $this->logger->info('');
        $this->logger->info('HKKAZ (statement of accounts) initialize');
        $this->logger->info('Start date: ' . $from->format('Y-m-d'));
        $this->logger->info('End date  : ' . $to->format('Y-m-d'));

        $dialog = $this->getDialog();
        #$dialog->syncDialog();
        #$dialog->initDialog();

        $message = $this->createStateOfAccountMessage($dialog, $account, $from, $to, null);
        $response = $dialog->sendMessage($message);
        $touchdowns = $response->getTouchdowns($message);
        $soaResponse = new GetStatementOfAccount($response->rawResponse);
        $responses[] = $soaResponse->getRawMt940();

        $touchdownCounter = 1;
        while (isset($touchdowns[HKKAZ::NAME])) {
            $this->logger->info('Fetching more statement of account results (' . $touchdownCounter++ . ') ...');
            $message = $this->createStateOfAccountMessage(
                $dialog,
                $account,
                $from,
                $to,
                self::escapeString($touchdowns[HKKAZ::NAME])
            );

            $r = $dialog->sendMessage($message);
            $touchdowns = $r->getTouchDowns($message);
            $soaResponse = new GetStatementOfAccount($r->rawResponse);
            $responses[] = $soaResponse->getRawMt940();
        }

        $this->logger->info('HKKAZ end');

        #$dialog->endDialog();

        return implode('', $responses);
    }

    /**
     * Gets statement of account.
     *
     * @param SEPAAccount $account
     * @param \DateTime $from
     * @param \DateTime $to
     * @return Model\StatementOfAccount\StatementOfAccount|null
     * @throws \Exception
     */
    public function getStatementOfAccountAsParsedMT940(SEPAAccount $account, \DateTime $from, \DateTime $to) {
        $rawMt940 = $this->getStatementOfAccountAsRawMT940($account, $from, $to);

        $urlParts = parse_url($this->url);

        // Evtl. Groß und Kleinschreibungen des Hosts normalisieren
        $dialectId = strtr($this->url, [
            $urlParts['scheme'] => strtolower($urlParts['scheme']),
            $urlParts['host'] => strtolower($urlParts['host']),
        ]);

        switch ($dialectId) {
            case Parser\Dialect\SpardaMT940::DIALECT_ID:
                $parser = new Parser\Dialect\SpardaMT940($rawMt940);
            break;
            case Parser\Dialect\PostbankMT940::DIALECT_ID:
                $parser = new Parser\Dialect\PostbankMT940($rawMt940);
            break;
            default:
                $parser = new MT940($rawMt940);
            break;
        }

        return $parser->parse(MT940::TARGET_ARRAY);
    }

    public function getStatementOfAccount(SEPAAccount $account, \DateTime $from, \DateTime $to) {
        $parsed = $this->getStatementOfAccountAsParsedMT940($account, $from, $to);

        return GetStatementOfAccount::createModelFromArray($parsed);
    }

    /**
     * Helper method to create a "Statement of Account Message".
     *
     * @param Dialog $dialog
     * @param SEPAAccount $account
     * @param \DateTime $from
     * @param \DateTime $to
     * @param string|null $touchdown
     * @return Message
     * @throws \Exception
     */
    protected function createStateOfAccountMessage(
        Dialog $dialog,
        SepaAccount $account,
        \DateTime $from,
        \DateTime $to,
        $touchdown = null
    ) {
        // version 4, 5, 6, 7

        // version 5
        /*
            1 Segmentkopf                   DEG         M 1
            2 Kontoverbindung Auftraggeber  DEG ktv #   M 1
            3 Alle Konten                   DE  jn  #   M 1
            4 Von Datum                     DE dat  #   K 1
            5 Bis Datum                     DE dat  #   K 1
            6 Maximale Anzahl Einträge      DE num ..4  K 1 >0
            7 Aufsetzpunkt                  DE an ..35  K 1
         */

        // version 6
        /*
            1 Segmentkopf                   1 DEG           M 1
            2 Kontoverbindung Auftraggeber  2 DEG ktv #     M 1
            3 Alle Konten                   1 DE jn #       M 1
            4 Von Datum                     1 DE dat #      O 1
            5 Bis Datum                     1 DE dat #      O 1
            6 Maximale Anzahl Einträge      1 DE num ..4    C 1 >0
            7 Aufsetzpunkt                  1 DE an ..35    C 1
         */

        // version 7
        /*
            1 Segmentkopf                   1 DEG       M 1
            2 Kontoverbindung international 1 DEG kti # M 1
            3 Alle Konten                   1 DE jn #   M 1
            4 Von Datum                     1 DE dat #  O 1
            5 Bis Datum                     1 DE dat #  O 1
            6 Maximale Anzahl Einträge      1 DE num ..4 C 1 >0
            7 Aufsetzpunkt                  1 DE an ..35 C 1
         */

        switch ($dialog->getHkkazMaxVersion()) {
            case 4:
            case 5:
                $konto = new Deg();
                $konto->addDataElement($account->getAccountNumber());
                $konto->addDataElement($account->getSubAccount());
                $konto->addDataElement(static::DEFAULT_COUNTRY_CODE);
                $konto->addDataElement($account->getBlz());
                break;
            case 6:
                $konto = new Ktv(
                    $account->getAccountNumber(),
                    $account->getSubAccount(),
                    new Kik(280, $account->getBlz())
                );
                break;
            case 7:
                $konto = new Kti(
                    $account->getIban(),
                    $account->getBic(),
                    $account->getAccountNumber(),
                    $account->getSubAccount(),
                    new Kik(280, $account->getBlz())
                );
                break;
            default:
                throw new \Exception('Unsupported HKKAZ version: ' . $dialog->getHkkazMaxVersion());
        }

        $message = $this->getNewMessage(
            $dialog,
            array(
                new HKKAZ(
                    $dialog->getHkkazMaxVersion(),
                    3,
                    $konto,
                    HKKAZ::ALL_ACCOUNTS_N,
                    $from,
                    $to,
                    $touchdown
                )
            ),
            array(AbstractMessage::OPT_PINTAN_MECH => $this->getUsedPinTanMechanism($dialog))
        );

        return $message;
    }

    /**
     * Gets Bank To Customer Account Report as camt XML
     *
     * @param SEPAAccount $account
     * @param \DateTime $from
     * @param \DateTime $to
     * @return string[]
     * @throws \Exception
     */
    public function getBankToCustomerAccountReportAsRawXML(SEPAAccount $account, \DateTime $from, \DateTime $to)
    {
        $responses = [];

        $this->logger->info('');
        $this->logger->info('HKCAZ (statement of accounts) initialize');
        $this->logger->info('Start date: ' . $from->format('Y-m-d'));
        $this->logger->info('End date  : ' . $to->format('Y-m-d'));

        $dialog = $this->getDialog();

        $message = $this->createHKCAZMessage($dialog, $account, $from, $to, null);
        $response = $dialog->sendMessage($message);
        $touchdowns = $response->getTouchdowns($message);

        $HICAZ = new BankToCustomerAccountReportHICAZ($response->rawResponse);
        $responses[] = $HICAZ->getBookedXML();

        $touchdownCounter = 1;
        while (isset($touchdowns[HKCAZ::NAME])) {
            $this->logger->info('Fetching more statement of account results (' . $touchdownCounter++ . ') ...');
            $message = $this->createHKCAZMessage(
                $dialog,
                $account,
                $from,
                $to,
                self::escapeString($touchdowns[HKCAZ::NAME])
            );

            $response = $dialog->sendMessage($message);
            $touchdowns = $response->getTouchDowns($message);
            $HICAZ = new BankToCustomerAccountReportHICAZ($response->rawResponse);
            $responses[] = $HICAZ->getBookedXML();
        }

        $this->logger->info('HKCAZ end');

        return $responses;
    }

    /**
     * Helper method to create a "Statement of Account Message".
     *
     * @param Dialog $dialog
     * @param SEPAAccount $account
     * @param \DateTime $from
     * @param \DateTime $to
     * @param string|null $touchdown
     * @return Message
     * @throws \Exception
     */
    protected function createHKCAZMessage(Dialog $dialog, SEPAAccount $account, \DateTime $from, \DateTime $to, $touchdown = null)
    {
        $kti = new Kti(
            $account->getIban(),
            $account->getBic(),
            $account->getAccountNumber(),
            $account->getSubAccount(),
            new Kik(280, $account->getBlz())
        );

        $message = $this->getNewMessage(
            $dialog,
            array(
                new HKCAZ(
                    1,
                    3,
                    $kti,
                    self::escapeString(HKCAZ::CAMT_FORMAT_FQ),
                    HKCAZ::ALL_ACCOUNTS_N,
                    $from,
                    $to,
                    $touchdown
                )
            ),
            array(AbstractMessage::OPT_PINTAN_MECH => $this->getUsedPinTanMechanism($dialog))
        );

        return $message;
    }

    /**
     * Gets the saldo of given SEPAAccount.
     *
     * @param SEPAAccount $account
     * @return Model\Saldo|null
     * @throws \CurlException
     * @throws \Exception
     */
    public function getSaldo(SEPAAccount $account) {
        $dialog = $this->getDialog();
        #$dialog->syncDialog();
        #$dialog->initDialog();

        switch ((int) $dialog->getHksalMaxVersion()) {
            case 4:
            case 5:
                $hksalAccount = new Deg(
                    $account->getAccountNumber(),
                    $account->getSubAccount(),
                    static::DEFAULT_COUNTRY_CODE, $account->getBlz()
                );
                $hksalAccount->addDataElement($account->getAccountNumber());
                $hksalAccount->addDataElement($account->getSubAccount());
                $hksalAccount->addDataElement(static::DEFAULT_COUNTRY_CODE);
                $hksalAccount->addDataElement($account->getBlz());
                break;
            case 6:
                $hksalAccount = new Ktv(
                    $account->getAccountNumber(),
                    $account->getSubAccount(),
                    new Kik(280, $account->getBlz())
                );
                break;
            case 7:
                $hksalAccount = new Kti(
                    $account->getIban(),
                    $account->getBic(),
                    $account->getAccountNumber(),
                    $account->getSubAccount(),
                    new Kik(280, $account->getBlz())
                );
                break;
            default:
                throw new \Exception('Unsupported HKSAL version: ' . $dialog->getHksalMaxVersion());
        }

        $message = new Message(
            $this->bankCode,
            $this->username,
            $this->pin,
            $dialog->getSystemId(),
            $dialog->getDialogId(),
            $dialog->getMessageNumber(),
            array(
                new HKSAL($dialog->getHksalMaxVersion(), 3, $hksalAccount, HKSAL::ALL_ACCOUNTS_N)
            ),
            array(
                AbstractMessage::OPT_PINTAN_MECH => $this->getUsedPinTanMechanism($dialog)
            )
        );

        $response = $dialog->sendMessage($message);
        $response = new GetSaldo($response->rawResponse);

        return $response->getSaldoModel();
    }
	
	public function finishSEPATAN(GetTANRequest $tanRequest, $tan){
		if($tan == "")
			throw new TANException("No TAN received!");
			#echo "No TAN found, exiting!\n";
			#return;
		
		$dialog = $tanRequest->getDialog();
		$this->dialog = $dialog;
		
        $message = new Message(
            $this->bankCode,
            $this->username,
            $this->pin,
            $dialog->getSystemId(),
            $dialog->getDialogId(),
            $dialog->getMessageNumber(),
            array(
				new HKTAN(HKTAN::VERSION, 3, $tanRequest->get()->getProcessID())
            ),
            array(
                AbstractMessage::OPT_PINTAN_MECH => $this->getUsedPinTanMechanism($dialog)
            ),
			$tan
        );
		
		$this->logger->info('');
		$this->logger->info('HKTAN (Zwei-Schritt-TAN-Einreichung) initialize');
		$dialog->sendMessage($message);
		$this->logger->info('HKTAN end');
	}
	
	/**
	 * Executes SEPA transfer
	 * You have to call finishSEPATAN(), if $tanCallback is not set
	 * 
	 * @param SEPAAccount $account
	 * @param string $painMessage
	 * @param \Closure $tanCallback
	 */
	public function executeSEPATransfer(SEPAAccount $account, $painMessage, \Closure $tanCallback = null) {
		$response = $this->startSEPATransfer($account, $painMessage);
		
		if($tanCallback === null)
			return $response;
		
		echo "Waiting max. 120 seconds for TAN from callback\n";
		for($i = 0; $i < 120; $i++){
			sleep(1);
			
			$tan = trim($tanCallback());
			if($tan == ""){
				echo "No TAN found, waiting ".(120 - $i)."!\n";
				continue;
			}
			
			break;
		}
		
		$this->finishSEPATAN($response, $tan);
	}
	
	public function executeSEPADirectDebit(SEPAAccount $account, $painMessage, \Closure $tanCallback, $interval = 1) {
		$painMessage = $this->clearXML($painMessage);
		
		
        $dialog = $this->getDialog();
        #$dialog->syncDialog();
        #$dialog->initDialog();

		$hkcdbAccount = new Kti(
			$account->getIban(),
			$account->getBic(),
			$account->getAccountNumber(),
			$account->getSubAccount(),
			new Kik(280, $account->getBlz())
		);

		$hkdsx = new HKDSE(HKDSE::VERSION, 3, $hkcdbAccount, "urn?:iso?:std?:iso?:20022?:tech?:xsd?:pain.008.003.02", $painMessage);
		if(strpos($painMessage, "<Cd>COR1</Cd>") !== false)
			$hkdsx = new HKDSC(HKDSC::VERSION, 3, $hkcdbAccount, "urn?:iso?:std?:iso?:20022?:tech?:xsd?:pain.008.003.02", $painMessage);
		
        $message = new Message(
            $this->bankCode,
            $this->username,
            $this->pin,
            $dialog->getSystemId(),
            $dialog->getDialogId(),
            $dialog->getMessageNumber(),
            array(
                $hkdsx,
				new HKTAN(HKTAN::VERSION, 4)
            ),
            array(
                AbstractMessage::OPT_PINTAN_MECH => $this->getUsedPinTanMechanism($dialog)
            )
        );
		
		$class = explode("\\", get_class($hkdsx));
		$this->logger->info('');
		$this->logger->info($class[count($class) - 1].' (SEPA direct debit) initialize');
		$response = $dialog->sendMessage($message);
		$this->logger->info($class[count($class) - 1].' end');
		
        $response = new GetTANRequest($response->rawResponse);
		#print_r($response);
		
		#var_dump($response->get()->getProcessID());
		$this->logger->info("Waiting max. 120 seconds for TAN from callback. Checking every $interval second(s)…");
		#echo "Waiting max. 120 seconds for TAN from callback. Checking every $interval second(s)…\n";
		for($i = 0; $i < 120; $i += $interval){
			sleep($interval);
			
			$tan = trim($tanCallback());
			if($tan == ""){
				$this->logger->info("No TAN found, waiting ".(120 - $i)."!");
				#echo "No TAN found, waiting ".(120 - $i)."!\n";
				continue;
			}
			
			break;
		}
		
		
		if($tan == "")
			throw new TANException("No TAN received!");
			#echo "No TAN found, exiting!\n";
			#return;
		
		
        $message = new Message(
            $this->bankCode,
            $this->username,
            $this->pin,
            $dialog->getSystemId(),
            $dialog->getDialogId(),
            $dialog->getMessageNumber(),
            array(
				new HKTAN(HKTAN::VERSION, 3, $response->get()->getProcessID())
            ),
            array(
                AbstractMessage::OPT_PINTAN_MECH => $this->getUsedPinTanMechanism($dialog)
            ),
			$tan
        );
		
		$this->logger->info('');
		$this->logger->info('HKTAN (Zwei-Schritt-TAN-Einreichung) initialize');
		$response = $dialog->sendMessage($message);
		$this->logger->info('HKTAN end');
		
	}
	
	/**
	 * Executes SEPA delete standing order
	 * 
	 * You have to call finishSEPATAN(), if $tanCallback is not set
	 * 
	 * @param SEPAAccount $account
	 * @param SEPAStandingOrder $order
	 * @param \Closure $tanCallback
	 */
	public function deleteSEPAStandingOrder(SEPAAccount $account, SEPAStandingOrder $order, \Closure $tanCallback = null) {
		$response = $this->startDeleteSEPAStandingOrder($account, $order);

		if($tanCallback === null)
			return $response;
		
		echo "Waiting max. 120 seconds for TAN from callback\n";
		for($i = 0; $i < 120; $i++){
			sleep(1);
			
			$tan = trim($tanCallback());
			if($tan == ""){
				echo "No TAN found, waiting ".(120 - $i)."!\n";
				continue;
			}
			
			break;
		}
		
		$this->finishSEPATAN($tan, $response);
	}
	
	public function getSEPAStandingOrders(SEPAAccount $account) {
        $dialog = $this->getDialog();
        #$dialog->syncDialog(false);
		#$dialog->initDialog();

		$hkcdbAccount = new Kti(
			$account->getIban(),
			$account->getBic(),
			$account->getAccountNumber(),
			$account->getSubAccount(),
			new Kik(280, $account->getBlz())
		);

        $message = new Message(
            $this->bankCode,
            $this->username,
            $this->pin,
            $dialog->getSystemId(),
            $dialog->getDialogId(),
            $dialog->getMessageNumber(),
            array(
                new HKCDB(HKCDB::VERSION, 3, $hkcdbAccount, array("urn?:iso?:std?:iso?:20022?:tech?:xsd?:pain.001.003.03"))#, "pain.008.003.02.xsd"))
            ),
            array(
                AbstractMessage::OPT_PINTAN_MECH => $this->getUsedPinTanMechanism($dialog)
            )
        );

		
		$this->logger->info('');
		$this->logger->info('HKCDB (SEPA standing orders) initialize');
        $response = $dialog->sendMessage($message);
		
		#$dialog->endDialog();
		$this->logger->info('HKCDB end');
        $response = new GetSEPAStandingOrders($response->rawResponse);
		
        return $response->getSEPAStandingOrdersArray();
	}
}

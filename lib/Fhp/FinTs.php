<?php

namespace Fhp;

use Fhp\Adapter\AdapterInterface;
use Fhp\Adapter\Curl;
use Fhp\DataTypes\Kik;
use Fhp\DataTypes\Kti;
use Fhp\DataTypes\Ktv;
use Fhp\Dialog\Dialog;
use Fhp\Message\AbstractMessage;
use Fhp\Message\Message;
use Fhp\Model\SEPAAccount;
use Fhp\Model\SEPAStandingOrder;
use Fhp\Response\GetAccounts;
use Fhp\Response\GetSaldo;
use Fhp\Response\GetSEPAAccounts;
use Fhp\Response\GetStatementOfAccount;
use Fhp\Response\GetSEPAStandingOrders;
use Fhp\Response\GetTANRequest;
use Fhp\Segment\HKKAZ;
use Fhp\Segment\HKSAL;
use Fhp\Segment\HKSPA;
use Fhp\Segment\HKCDB;
use Fhp\Segment\HKCDL;
use Fhp\Segment\HKCCS;
use Fhp\Segment\HKTAN;
use Fhp\Segment\HKDSE;
use Fhp\Segment\HKDSC;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

/**
 * Class FinTs.
 *
 * @package Fhp
 */
class FinTs
{
    const DEFAULT_COUNTRY_CODE = 280;

    /** @var LoggerInterface */
    protected $logger;
    /** @var  string */
    protected $server;
    /** @var int */
    protected $port;
    /** @var string */
    protected $bankCode;
    /** @var string */
    protected $username;
    /** @var string */
    protected $pin;
    /** @var  Connection */
    protected $connection;
    /** @var  AdapterInterface */
    protected $adapter;
    /** @var int */
    protected $systemId = 0;
    /** @var string */
    protected $bankName;
	/** @var int */
	protected $tanMechanism;
	
	protected $dialog;
    /**
     * FinTs constructor.
     * @param string $server
     * @param int $port
     * @param string $bankCode
     * @param string $username
     * @param string $pin
     * @param LoggerInterface|null $logger
     */
    public function __construct(
        $server,
        $port,
        $bankCode,
        $username,
        $pin,
        LoggerInterface $logger = null
    ) {
        $this->server = $server;
        $this->port = $port;
        $this->logger = null == $logger ? new NullLogger() : $logger;

        // escaping of bank code not really needed here as it should
        // never have special chars. But we just do it to ensure
        // that the request will not get messed up and the user
        // can receive a valid error response from the HBCI server.
        $this->bankCode = $this->escapeString($bankCode);

        // Here, escaping is needed for usernames or pins with
        // HBCI special chars.
        $this->username = $this->escapeString($username);
        $this->pin = $this->escapeString($pin);

        $this->adapter = new Curl($this->server, $this->port);
        $this->connection = new Connection($this->adapter);
    }

    /**
     * Sets the tan mechanism to use. Uses first found tan mechanism if not set.
     * 901: mobileTAN
	 * 
     * @param int $mode
     */
	public function setTANMechanism($mode)
	{
		$this->tanMechanism = $mode;
	}
	
    /**
     * Sets the adapter to use.
     *
     * @param AdapterInterface $adapter
     */
    public function setAdapter(AdapterInterface $adapter)
    {
        $this->adapter = $adapter;
        $this->connection = new Connection($this->adapter);
    }

    /**
     * Gets array of all accounts.
     *
     * @return Model\Account[]
     */
    public function getAccounts()
    {
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
     * @throws Adapter\Exception\AdapterException
     * @throws Adapter\Exception\CurlException
     */
    public function getSEPAAccounts()
    {
        $dialog = $this->getDialog();
        #$dialog->initDialog();

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
    public function getBankName()
    {
        if (null == $this->bankName) {
            $this->getDialog()->syncDialog();
        }

        return $this->bankName;
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
    public function getStatementOfAccount(SEPAAccount $account, \DateTime $from, \DateTime $to)
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
                $touchdowns[HKKAZ::NAME]
            );

            $r = $dialog->sendMessage($message);
            $touchdowns = $r->getTouchDowns($message);
            $soaResponse = new GetStatementOfAccount($r->rawResponse);
            $responses[] = $soaResponse->getRawMt940();
        }

		$this->logger->info('HKKAZ end');

        #$dialog->endDialog();

        return GetStatementOfAccount::createModelFromRawMt940(implode('', $responses));
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
     * Gets the saldo of given SEPAAccount.
     *
     * @param SEPAAccount $account
     * @return Model\Saldo|null
     * @throws Adapter\Exception\AdapterException
     * @throws Adapter\Exception\CurlException
     * @throws \Exception
     */
    public function getSaldo(SEPAAccount $account)
    {
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

	public function executeSEPATransfer(SEPAAccount $account, $painMessage, Closure $tanCallback)
	{
		$painMessage = $this->clearXML($painMessage);
		#file_put_contents($tanFilePath, "");
		
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

        $message = new Message(
            $this->bankCode,
            $this->username,
            $this->pin,
            $dialog->getSystemId(),
            $dialog->getDialogId(),
            $dialog->getMessageNumber(),
            array(
                new HKCCS(HKCCS::VERSION, 3, $hkcdbAccount, "urn?:iso?:std?:iso?:20022?:tech?:xsd?:pain.001.003.03", $painMessage),
				new HKTAN(HKTAN::VERSION, 4)
            ),
            array(
                AbstractMessage::OPT_PINTAN_MECH => $this->getUsedPinTanMechanism($dialog)
            )
        );
		
        $response = $dialog->sendMessage($message);
        $response = new GetTANRequest($response->rawResponse);
		#print_r($response);
		
		#var_dump($response->get()->getProcessID());
		echo "Waiting max. 60 seconds for TAN from callback\n";
		for($i = 0; $i < 60; $i++){
			sleep(1);
			
			$tan = trim($tanCallback());
			if($tan == ""){
				echo "No TAN found, waiting ".(60 - $i)."!\n";
				continue;
			}
			
			break;
		}
		
		
		if($tan == ""){
			echo "No TAN found, exiting!\n";
			return;
		}
		
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
		$result = $dialog->sendMessage($message);
	}
	
	public function executeSEPADirectDebit(SEPAAccount $account, $painMessage, Closure $tanCallback)
	{
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
		
		$this->logger->info('');
		$this->logger->info(get_class($hkdsx).' (Terminierte SEPA-Einzellastschrift einreichen) initialize');
		$response = $dialog->sendMessage($message);
		$this->logger->info(get_class($hkdsx).' end');
		
        $response = new GetTANRequest($response->rawResponse);
		#print_r($response);
		
		#var_dump($response->get()->getProcessID());
		echo "Waiting max. 60 seconds for TAN from callback\n";
		for($i = 0; $i < 60; $i++){
			sleep(1);
			
			$tan = trim($tanCallback());
			if($tan == ""){
				echo "No TAN found, waiting ".(60 - $i)."!\n";
				continue;
			}
			
			break;
		}
		
		
		if($tan == ""){
			echo "No TAN found, exiting!\n";
			return;
		}
		
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
	
	public function deleteSEPAStandingOrder(SEPAAccount $account, SEPAStandingOrder $order, Closure $tanCallback)
	{
		#file_put_contents($tanFilePath, "");
		
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

        $message = new Message(
            $this->bankCode,
            $this->username,
            $this->pin,
            $dialog->getSystemId(),
            $dialog->getDialogId(),
            $dialog->getMessageNumber(),
            array(
                new HKCDL(HKCDL::VERSION, 3, $hkcdbAccount, "urn?:iso?:std?:iso?:20022?:tech?:xsd?:pain.001.001.03", $order),
				new HKTAN(HKTAN::VERSION, 4)
            ),
            array(
                AbstractMessage::OPT_PINTAN_MECH => $this->getUsedPinTanMechanism($dialog)
            )
        );
		#$message->
        $response = $dialog->sendMessage($message);
        $response = new GetTANRequest($response->rawResponse);
		#var_dump($response->get()->getProcessID());
		echo "Waiting max. 60 seconds for TAN from callback\n";
		for($i = 0; $i < 60; $i++){
			sleep(1);
			
			$tan = trim($tanCallback());
			if($tan == ""){
				echo "No TAN found, waiting ".(60 - $i)."!\n";
				continue;
			}
			
			break;
		}
		
		
		if($tan == ""){
			echo "No TAN found, exiting!\n";
			return;
		}
		
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
		$dialog->sendMessage($message);
	}
	
	public function getSEPAStandingOrders(SEPAAccount $account)
    {
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
	
    /**
     * Helper method to retrieve a pre configured message object.
     * Factory for poor people :)
     *
     * @param Dialog $dialog
     * @param array $segments
     * @param array $options
     * @return Message
     */
    protected function getNewMessage(Dialog $dialog, array $segments, array $options)
    {
        return new Message(
            $this->bankCode,
            $this->username,
            $this->pin,
            $dialog->getSystemId(),
            $dialog->getDialogId(),
            $dialog->getMessageNumber(),
            $segments,
            $options
        );
    }

    /**
     * Helper method to retrieve a pre configured dialog object.
     * Factory for poor people :)
     *
     * @return Dialog
     */
    protected function getDialog($sync = true)
    {
		if($this->dialog)
			return $this->dialog;
			
        $D = new Dialog(
            $this->connection,
            $this->bankCode,
            $this->username,
            $this->pin,
            $this->systemId,
            $this->logger
        );
		
		if($sync)
	        $D->syncDialog(false);
		
		$this->dialog = $D;
		
		return $this->dialog;
    }

    /**
     * Needed for escaping userdata.
     * HBCI escape char is "?"
     *
     * @param string $string
     * @return string
     */
    protected function escapeString($string)
    {
        return str_replace(
            array('?', '@', ':', '+', '\''),
            array('??', '?@', '?:', '?+', '?\''),
            $string
        );
    }
	
	protected function clearXML($xml)
	{
		$dom = new \DOMDocument;
		$dom->preserveWhiteSpace = FALSE;
		$dom->loadXML($xml);
		$dom->formatOutput = false;
		return $dom->saveXml();
	}
	
	private function getUsedPinTanMechanism($dialog)
	{
		if($this->tanMechanism !== null AND in_array($this->tanMechanism, $dialog->getSupportedPinTanMechanisms()))
			return array($this->tanMechanism);
		
		return $dialog->getSupportedPinTanMechanisms();
	}
}

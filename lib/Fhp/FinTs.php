<?php

namespace Fhp;

use Fhp\DataTypes\Kik;
use Fhp\DataTypes\Kti;
use Fhp\DataTypes\Ktv;
use Fhp\Dialog\Dialog;
use Fhp\Dialog\Exception\TANRequiredException;
use Fhp\Message\Message;
use Fhp\Message\AbstractMessage;
use Fhp\Model\Account;
use Fhp\Model\SEPAAccount;
use Fhp\Model\SEPAStandingOrder;
use Fhp\MT940\Dialect\PostbankMT940;
use Fhp\MT940\Dialect\SpardaMT940;
use Fhp\MT940\MT940;
use Fhp\Response\GetAccounts;
use Fhp\Response\GetSaldo;
use Fhp\Response\GetSEPAAccounts;
use Fhp\Response\Response;
use Fhp\Response\GetStatementOfAccount;
use Fhp\Response\GetSEPAStandingOrders;
use Fhp\Response\GetTANRequest;
use Fhp\Response\GetVariables;
use Fhp\Response\BankToCustomerAccountReportHICAZ;
use Fhp\Segment\HKKAZ;
use Fhp\Segment\HKSAL;
use Fhp\Segment\HKSPA;
use Fhp\Segment\HKCDB;
use Fhp\Segment\HKDSE;
use Fhp\Segment\HKDSC;
use Fhp\Segment\HKCAZ;
use Fhp\Segment\HKVVB;
use Fhp\Segment\HKIDN;
use Fhp\Segment\HKTAB;
use Fhp\Segment\HNSHK;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Fhp\Dialog\Exception\TANException;

class FinTs extends FinTsInternal
{
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
    /** @var Account[] */
    protected $accounts;
	/** @var int */
	protected $tanMechanism;
	/** @var Dialog */
	protected $dialog = null;
	/** @var string */
	protected $productName;
	/** @var string */
	protected $productVersion;

	/**
	 * FinTs constructor.
	 * @param string $server
	 * @param string $bankCode
	 * @param string $username
	 * @param string $pin
	 * @param string $productName
	 * @param string $productVersion
	 */
	public function __construct(
		$server,
		$bankCode,
		$username,
		$pin,
		$productName,
		$productVersion
	) {
		if(trim($productName) == '')
			throw new \Exception ("Product name required!");
		
		if(trim($productVersion) == '')
			throw new \Exception ("Product version required!");
		
		$this->url = trim($server);
		$this->logger = new NullLogger();

		// escaping of bank code not really needed here as it should
		// never have special chars. But we just do it to ensure
		// that the request will not get messed up and the user
		// can receive a valid error response from the HBCI server.
		$this->bankCode = self::escapeString($bankCode);

		// Here, escaping is needed for usernames or pins with
		// HBCI special chars.
		$this->username = self::escapeString($username);
		$this->pin = self::escapeString($pin);

		if ($productName != '') {
			$this->productName = self::escapeString($productName);
		}
		if ($productVersion != '') {
			$this->productVersion = self::escapeString($productVersion);
		}
	}
	
	public function setLogger(LoggerInterface $logger){
		$this->logger = $logger;
	}

	public function setTimeouts($connect, $response)
	{
		$this->timeoutConnect = $connect;
		$this->timeoutResponse = $response;
	}


	/**
	 * Gets array of all accounts.
	 *
	 * @return Model\Account[]
	 */
    public function getAccounts()
    {
        return $this->accounts;
    }

	/**
	 * Gets array of all SEPA Accounts.
	 *
	 * @return Model\SEPAAccount[]
	 * @throws \CurlException
	 */
	public function getSEPAAccounts(\Closure $tanCallback = null)
	{
	    $this->logger->debug(__CLASS__ . ':' . __FUNCTION__ . ' called');

        $dialog = $this->getDialog();

		$message = $this->getNewMessage(
			$dialog,
            array(new HKSPA(3))
		);

		$this->logger->info('');
		$this->logger->info('HKSPA (SEPA accounts) initialize');
		$result = $dialog->sendMessage($message, null, $tanCallback);
		$this->logger->info('HKSPA end');

		$sepaAccounts = new GetSEPAAccounts($result->rawResponse);

		return $sepaAccounts->getSEPAAccountsArray();
	}

    public function getVariables()
    {
        $this->logger->debug(__CLASS__ . ':' . __FUNCTION__ . ' called');

        $dialog = $this->getDialog();
        $response = $dialog->syncDialog();
        $this->end();

        $vars = new GetVariables($response->rawResponse);
        $obj = $vars->get();

        #if (!empty($obj->tanModes)) {
        #    $this->setTANMechanism(array_keys($obj->tanModes)[0], 'A'); // some banks need nonempty methodName
        #    $obj->TANMediaNames = $this->getTANDevices(); //does not work with every Bank. Needs to be called separately
        #}
        return $obj;
    }
    public function getTANDevices($tanMechanism)
    {
        $this->logger->debug(__CLASS__ . ':' . __FUNCTION__ . ' called');

        $dialog = $this->getDialog();

        $message = $this->getNewMessage(
            $dialog,
            array(
                new HKTAB(3)
            ),
            array(AbstractMessage::OPT_PINTAN_MECH => $tanMechanism)
        );
        $response = $dialog->sendMessage($message);
        $segment = $response->findSegment('HITAB');
        $segment = $response->splitSegment($segment);
        $segment = array_slice($segment, 2);
        
        $devices = array();
        
        foreach ($segment as $deg) {
            $deg_cleaned = str_replace("?:", "______________", $deg);
            $name = explode(':', $deg_cleaned)[12];
            $devices[] = $this->unescapeString(str_replace("______________", "?:", $name));
        }
        return $devices;
    }

	public function getTANRequest()
	{
        $this->logger->debug(__CLASS__ . ':' . __FUNCTION__ . ' called');

        $dialog = $this->getDialog();
		$response = $dialog->syncDialog();
		if ($response->isTANRequest()) {
			return $response;
		}
		return null;
	}

	public function end()
	{
		if (!$this->dialog) {
			return;
		}

		$this->dialog->endDialog();
		$this->dialog = null;
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
			$this->end();
		}

		return $this->bankName;
	}

	/**
	 * Gets statement of account.
	 *
	 * @param SEPAAccount $account
	 * @param \DateTime $from
	 * @param \DateTime $to
	 * @param \Closure $tanCallback
	 * @param $interval
	 * @return Model\StatementOfAccount\StatementOfAccount|null
	 * @throws \Exception
	 */
	public function getStatementOfAccount(SEPAAccount $account, \DateTime $from, \DateTime $to, \Closure $tanCallback = null, $interval = 1)
	{
        $this->logger->debug(__CLASS__ . ':' . __FUNCTION__ . ' called');

		$this->logger->info('');
		$this->logger->info('HKKAZ (statement of accounts) initialize');
		$this->logger->info('Start date: ' . $from->format('Y-m-d'));
		$this->logger->info('End date  : ' . $to->format('Y-m-d'));

		$dialog = $this->getDialog();

		$message = $this->createStateOfAccountMessage($dialog, $account, $from, $to, null);
		$response = $dialog->sendMessage($message, $this->getUsedPinTanMechanism($dialog), $tanCallback, $interval);
		#echo get_class($response);
		if ($response->isTANRequest()) {
			return $response;
		}

		return $this->finishStatementOfAccount($response, $account, $from, $to);
	}

	public function finishStatementOfAccount(Response $response, SEPAAccount $account, \DateTime $from, \DateTime $to, $tan = null)
	{
        $this->logger->debug(__CLASS__ . ':' . __FUNCTION__ . ' called');

		$dialog = $response->getDialog();
		$this->dialog = $dialog;

		if ($tan) {
			$response = $dialog->submitTAN($response, $this->getUsedPinTanMechanism($dialog), $tan);
		}

		$message = $this->createStateOfAccountMessage($dialog, $account, $from, $to, null);

		$responses = array();
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

		$rawMt940 = implode('', $responses);


		$urlParts = parse_url($this->url);
		// Evtl. Groß und Kleinschreibungen des Hosts normalisieren
		$dialectId = strtr($this->url, [
			$urlParts['scheme'] => strtolower($urlParts['scheme']),
			$urlParts['host'] => strtolower($urlParts['host']),
		]);

        switch ($dialectId) {
            case SpardaMT940::DIALECT_ID:
                $parser = new SpardaMT940();
                break;
            case PostbankMT940::DIALECT_ID:
                $parser = new PostbankMT940();
                break;
            default:
                $parser = new MT940();
                break;
        }

        return GetStatementOfAccount::createModelFromArray($parser->parse($rawMt940));
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
        $this->logger->debug(__CLASS__ . ':' . __FUNCTION__ . ' called');

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
	 * Gets the saldo of given SEPAAccount.
	 *
	 * @param SEPAAccount $account
	 * @return Model\Saldo|null
	 * @throws \CurlException
	 * @throws \Exception
	 */
	public function getSaldo(SEPAAccount $account)
	{
        $this->logger->debug(__CLASS__ . ':' . __FUNCTION__ . ' called');

		$dialog = $this->getDialog();

		$addEncSegments = array();

		switch ((int) $dialog->getHksalMaxVersion()) {
			case 4:
			case 5:
				$hksalAccount = new Deg(
					$account->getAccountNumber(),
					$account->getSubAccount(),
					static::DEFAULT_COUNTRY_CODE,
					$account->getBlz()
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

        $message = $this->getNewMessage($dialog,
			array_merge(
				array(new HKSAL($dialog->getHksalMaxVersion(), 3, $hksalAccount, HKSAL::ALL_ACCOUNTS_N)),
				$addEncSegments
			)
		);

		$response = $dialog->sendMessage($message);
		$response = new GetSaldo($response->rawResponse);

		return $response->getSaldoModel();
	}

	public function finishSEPATAN(GetTANRequest $tanRequest, $tan)
	{
        $this->logger->debug(__CLASS__ . ':' . __FUNCTION__ . ' called');

		if ($tan == '') {
			throw new TANException('No TAN received!');
		}

        $this->tanMechanism = $tanRequest->getTanMechnism();
        #$this->tanMediaName = $tanMediaName;
        
		$dialog = $tanRequest->getDialog();
		$this->dialog = $dialog;

		$dialog->submitTAN($tanRequest, $this->tanMechanism, $tan);
	}

	/**
	 * Executes SEPA transfer
	 * You have to call finishSEPATAN(), if $tanCallback is not set
	 *
	 * @param SEPAAccount $account
	 * @param string $painMessage
	 * @param \Closure $tanCallback
	 */
	public function executeSEPATransfer(SEPAAccount $account, $painMessage, \Closure $tanCallback = null)
	{
        $this->logger->debug(__CLASS__ . ':' . __FUNCTION__ . ' called');

		$response = $this->startSEPATransfer($account, $painMessage);

		if ($tanCallback === null) {
			return $response;
		}

		echo "Waiting max. 120 seconds for TAN from callback\n";
		for ($i = 0; $i < 120; $i++) {
			sleep(1);

			$tan = trim($tanCallback());
			if ($tan == '') {
				echo 'No TAN found, waiting '.(120 - $i)."!\n";
				continue;
			}

			break;
		}

		$this->finishSEPATAN($response, $tan);
	}

	public function executeSEPADirectDebit(SEPAAccount $account, $painMessage, \Closure $tanCallback, $interval = 1)
	{
        $this->logger->debug(__CLASS__ . ':' . __FUNCTION__ . ' called');

		$painMessage = $this->clearXML($painMessage);


		$dialog = $this->getDialog();

		$hkcdbAccount = new Kti(
			$account->getIban(),
			$account->getBic(),
			$account->getAccountNumber(),
			$account->getSubAccount(),
			new Kik(280, $account->getBlz())
		);

		$hkdsx = new HKDSE(HKDSE::VERSION, 3, $hkcdbAccount, 'urn?:iso?:std?:iso?:20022?:tech?:xsd?:pain.008.003.02', $painMessage);
		if (strpos($painMessage, '<Cd>COR1</Cd>') !== false) {
			$hkdsx = new HKDSC(HKDSC::VERSION, 3, $hkcdbAccount, 'urn?:iso?:std?:iso?:20022?:tech?:xsd?:pain.008.003.02', $painMessage);
		}

        $message = $this->getNewMessage($dialog,
			array(
                $hkdsx
			)
		);

		$class = explode('\\', get_class($hkdsx));
		$this->logger->info('');
		$this->logger->info($class[count($class) - 1].' (SEPA direct debit) initialize');
		$response = $dialog->sendMessage($message, $this->tanMechanism, $tanCallback);
		$this->logger->info($class[count($class) - 1].' end');

		#$response = new GetTANRequest($response->rawResponse);
		#print_r($response);

		#var_dump($response->get()->getProcessID());
		#$this->logger->info("Waiting max. 120 seconds for TAN from callback. Checking every $interval second(s)...");
		#echo "Waiting max. 120 seconds for TAN from callback. Checking every $interval second(s)…\n";
		/*for ($i = 0; $i < 120; $i += $interval) {
			sleep($interval);

			$tan = trim($tanCallback());
			if ($tan == '') {
				$this->logger->info('No TAN found, waiting '.(120 - $i).'!');
				continue;
			}

			break;
		}


		if ($tan == '') {
			throw new TANException('No TAN received!');
		}

		$dialog->submitTAN($response, $this->getUsedPinTanMechanism($dialog), $tan);*/
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
	public function deleteSEPAStandingOrder(SEPAAccount $account, SEPAStandingOrder $order, \Closure $tanCallback = null)
	{
        $this->logger->debug(__CLASS__ . ':' . __FUNCTION__ . ' called');

		$response = $this->startDeleteSEPAStandingOrder($account, $order);

		if ($tanCallback === null) {
			return $response;
		}

		echo "Waiting max. 120 seconds for TAN from callback\n";
		for ($i = 0; $i < 120; $i++) {
			sleep(1);

			$tan = trim($tanCallback());
			if ($tan == '') {
				echo 'No TAN found, waiting '.(120 - $i)."!\n";
				continue;
			}

			break;
		}

		$this->finishSEPATAN($tan, $response);
	}

	public function getSEPAStandingOrders(SEPAAccount $account)
	{
        $this->logger->debug(__CLASS__ . ':' . __FUNCTION__ . ' called');

		$dialog = $this->getDialog();

		$hkcdbAccount = new Kti(
			$account->getIban(),
			$account->getBic(),
			$account->getAccountNumber(),
			$account->getSubAccount(),
			new Kik(280, $account->getBlz())
		);

        $message = $this->getNewMessage($dialog,
			array(
				new HKCDB(HKCDB::VERSION, 3, $hkcdbAccount, array('urn?:iso?:std?:iso?:20022?:tech?:xsd?:pain.001.003.03'))#, "pain.008.003.02.xsd"))
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
     * Retrieve a pre configured dialog object.
     *
     * @param boolean
     * @return Dialog
     * @throws \Exception
     */
    protected function getDialog()
    {
        if ($this->dialog !== null) {
            return $this->dialog;
        }

        if ($this->connection === null) {
            $this->connection = new Connection($this->url, $this->timeoutConnect, $this->timeoutResponse);
        }

        $dialog = new Dialog(
            $this->connection,
            $this->bankCode,
            $this->username,
            $this->pin,
            $this->systemId,
            $this->logger,
            $this->productName,
            $this->productVersion
        );

        $this->dialog = $dialog;

        return $this->dialog;
    }

    /**
     * @return Response|GetTANRequest
     */
    public function login($tanMechanism = HNSHK::SECURITY_FUNC_999, $tanMediaName = null, \Closure $tanCallback = null)
    {
        $dialog = $this->getDialog();
        // System-ID anfragen, Anfrage ohne TAN-Mechanismus
        $dialog->syncDialog();
        // Dialog muss anschließend beendet werden
        $dialog->endDialog();

        $this->tanMechanism = $tanMechanism;
        $this->tanMediaName = $tanMediaName;

        // Es wurde keine TAN-Mechanismus angegeben
        if ($tanMechanism == HNSHK::SECURITY_FUNC_999) {
            $mechs = $dialog->getSupportedPinTanMechanisms();
            if (count($mechs) == 1) {
                $this->tanMechanism = key($mechs);
            } else {
                // Der Nutzer muss entscheiden welchen er will
                $names = [];
                foreach ($mechs as $mechanism => $name) {
                    $names[] = $mechanism . ' (' . $name . ')';
                }
                throw new \Exception('Bitte einen der folgenden Sicherheitsfunktionen wählen: ' . implode(', ', $names));
            }
        }

        // Manche Banken brauchen einen TAN-Medium-Namen
        // https://www.hbci-zka.de/dokumente/spezifikation_deutsch/fintsv3/FinTS_3.0_Security_Sicherheitsverfahren_PINTAN_2018-02-23_final_version.pdf
        // B.4.3.1.3

        if (is_null($this->tanMediaName) && $dialog->bpd->allTanModes[$this->tanMechanism]->needsTanMedium()) {
            // TODO: Beim Erstzugang mit einem neuen TAN-Verfahren liegt einem Kundenprodukt
            //ggf. noch keine TAN-Medien-Bezeichnung für dieses Verfahren vor. In diesem
            //Fall muss der Geschäftsvorfall Anzeige der verfügbaren TAN-Medien
            //(HKTAB) ohne starke Kundenauthentifizierung durchführbar sein. Dies ist bei
            //der Prüfung der Kriterien im Kreditinstitut zu berücksichtigen.

            $dialog->initDialog($this->tanMechanism, '??', $tanCallback);

            // TODO: Nur TAN-Medien-Namen anzeigen, die zum TAN-Mechanismus passen
            $devices = $this->getTANDevices($tanMechanism);
            $dialog->endDialog();
            throw new \Exception('Bitte einen der folgenden TAN-Media-Namen angeben: ' . implode(', ', $devices));
        }

        $response = $dialog->initDialog($this->tanMechanism, $this->tanMediaName, $tanCallback);

        $this->bankName = $dialog->getBankName();

        // Sonderbehandlung bis es die TANRequiredException gibt
        if ($response->isTANRequest()) {
            return $response;
        }

        $this->accounts = (new GetAccounts($response))->getAccountsArray();


        return $response;
    }

    public function submitTanForMechanism($tanMechanism, $tanMediaName = null, $processId, $tan, $systemId = null, $dialogId = null, $messageNumber = null)
    {
        $dialog = $this->getDialog();

        // Wenn kein bestehender Dialog fortgeführt werden soll, z.B. weil die Bank das nicht benötigt
        // die Werte aus dem Dialog nehmen, der vorher mit ::login gestartet wurde
        $systemId = $systemId ?? $dialog->getSystemId();
        $dialogId = $dialogId ?? $dialog->getDialogId();
        $messageNumber = $messageNumber ?? $dialog->getMessageNumber();

        $message = new Message(
            $this->bankCode,
            $this->username,
            $this->pin,
            $systemId,
            $dialogId,
            $messageNumber,
            array(
                new HKTAN(HKTAN::VERSION, 3, $processId, $tanMediaName)
            ),
            array(
                AbstractMessage::OPT_PINTAN_MECH => $tanMechanism
            ),
            $tan
        );
        $this->logger->info('');
        $this->logger->info('HKTAN (Zwei-Schritt-TAN-Einreichung) initialize');
        $response = $dialog->sendMessage($message);
        $this->logger->info('HKTAN end');
        return $response;
    }

    public function submitTanForToken(string $tanToken, string $tan)
    {
        $values = array_combine(TANRequiredException::TAN_TOKEN_VALUE_ORDER, explode('~', $tanToken));

        return $this->submitTanForMechanism($values['tanMechanism'], $values['tanMediaName'] ?? null, $values['processId'], $tan, $values['systemId'], $values['dialogId'], $values['messageNumber']);
    }

    /**
     * @param SEPAAccount $account The account to test the support for
     * @param string $requestName The request that shall be sent to the bank.
     * @return boolean True if the given request can be used by the current user for the given account.
     */
    public function isRequestSupportedForAccount(SEPAAccount $account, $requestName)
    {
        return $this->dialog->upd->isRequestSupportedForAccount($account, $requestName);
    }
}

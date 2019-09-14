<?php
namespace Fhp\Dialog;

use Fhp\CurlException;
use Fhp\Connection;
use Fhp\Dialog\Exception\FailedRequestException;
use Fhp\Message\AbstractMessage;
use Fhp\Message\Message;
use Fhp\Response\Initialization;
use Fhp\Response\Response;
use Fhp\Response\GetTANRequest;
use Fhp\Segment\HKEND;
use Fhp\Segment\HKIDN;
use Fhp\Segment\HKSYN;
use Fhp\Segment\HKTAN;
use Fhp\Segment\HKVVB;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;

/**
 * Class Dialog
 * @package Fhp\Dialog
 */
class Dialog
{
    const DEFAULT_COUNTRY_CODE = 280;

    /**
     * @var Connection
     */
    protected $connection;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var int
     */
    protected $messageNumber = 1;

    /**
     * @var int
     */
    protected $dialogId = 0;

    /**
     * @var int|string
     */
    protected $systemId = 0;

    /**
     * @var string
     */
    protected $bankCode;

    /**
     * @var string
     */
    protected $username;

    /**
     * @var string
     */
    protected $pin;

    /**
     * @var string
     */
    protected $bankName;

    /**
     * @var array
     */
    protected $supportedTanMechanisms = array();

    /**
     * @var int
     */
    protected $hksalVersion = 6;

    /**
     * @var int
     */
    protected $hkkazVersion = 6;

    /**
     * @var string
     */
    protected $productName;

    /**
     * @var string
     */
    protected $productVersion;

    /**
     * Dialog constructor.
     *
     * @param Connection $connection
     * @param string $bankCode
     * @param string $username
     * @param string $pin
     * @param string $systemId
     * @param LoggerInterface $logger
     * @param string $productName
     * @param string $productVersion
     */
    public function __construct(
        Connection $connection,
        $bankCode,
        $username,
        $pin, $systemId,
        LoggerInterface $logger,
        $productName,
        $productVersion
    ) {
        $this->connection = $connection;
        $this->bankCode = $bankCode;
        $this->username = $username;
        $this->pin = $pin;
        $this->systemId = $systemId;
        $this->logger = $logger;
        $this->productName = $productName;
        $this->productVersion = $productVersion;
    }

    /**
     * @param AbstractMessage $message
	 * @param \Closure $tanCallback
	 * @param $interval
     * @return Response|GetTANRequest
     * @throws CurlException
     * @throws FailedRequestException
     */
    public function sendMessage(AbstractMessage $message, $tanMechanism = null, \Closure $tanCallback = null, $interval = 1)
    {
        try {
			$this->logger->debug("> ".$message);
            $message->setMessageNumber($this->messageNumber);
            $message->setDialogId($this->dialogId);

            $result = $this->connection->send($message);
            $this->messageNumber++;
			
			$this->logger->debug("< ".$result);
			
            $response = new Response($result, $this);
            $this->handleResponse($response);
            #$this->logger->info('Response reads:');
			#$this->logger->info($response->rawResponse);
			
            if (!$response->isSuccess()) {
                $summaryS = $response->getSegmentSummary();
                $summaryM = $response->getMessageSummary();
				
				$summary = array();
				foreach($summaryS AS $k => $v)
					$summary[$k] = $v;
				
				foreach($summaryM AS $k => $v){
					if(isset($summary[$k]))
						$summary[$k] .= "($v)";
					else
						$summary[$k] = $v;
				}
				
                $ex = new FailedRequestException($summary);
                $this->logger->error($ex->getMessage());
                throw $ex;
            }

			$hitan = $response->splitSegment($response->findSegment("HITAN"));
			
			if(!isset($hitan[4]) OR $hitan[4] == "nochallenge")
				return $response;
			
			$response = new GetTANRequest($response->rawResponse, $this);
			
			if(!$tanCallback)
				return $response;
			
			$this->logger->info("Waiting max. 120 seconds for TAN from callback. Checking every $interval second(s)...");
			for($i = 0; $i < 120; $i += $interval){
				sleep($interval);

				$tan = trim($tanCallback());
				if($tan == ""){
					$this->logger->info("No TAN found, waiting ".(120 - $i)."!");
					continue;
				}

				break;
			}

			if($tan == "")
				throw new TANException("No TAN received!");

			$response = $this->submitTAN($response, $tanMechanism, $tan);
			
            return $response;
        } catch (\Exception $e) {
            $this->logger->critical($e->getMessage());
            if ($e instanceof CurlException) {
                $this->logger->debug(print_r($e->getCurlInfo(), true));
            }

            throw $e;
        }
    }

	public function submitTAN($response, $tanMechanism, $tan){
		$message = new Message(
			$this->bankCode,
			$this->username,
			$this->pin,
			$this->getSystemId(),
			$this->getDialogId(),
			$this->getMessageNumber(),
			array(
				new HKTAN(HKTAN::VERSION, 3, $response->get()->getProcessID())
			),
			array(
				AbstractMessage::OPT_PINTAN_MECH => $tanMechanism
			),
			$tan
		);

		$this->logger->info('');
		$this->logger->info('HKTAN (Zwei-Schritt-TAN-Einreichung) initialize');
		$response = $this->sendMessage($message);
		$this->logger->info('HKTAN end');
		
		return $response;
	}
	
    /**
     * @param Response $response
     * @throws \Exception
     */
    protected function handleResponse(Response $response)
    {
        $summary = $response->getMessageSummary();
        $segSum  = $response->getSegmentSummary();

        foreach ($summary as $code => $message) {
            $this->logMessage('HIRMG', $code, $message);
        }

        foreach ($segSum as $code => $message) {
            $this->logMessage('HIRMS', $code, $message);
        }
		#$this->logger->log(LogLevel::INFO, "");
    }

    /**
     * @param string $type
     * @param string $code
     * @param $message
     */
    protected function logMessage($type, $code, $message)
    {
        switch (substr($code, 0, 1)) {
            case '0':
                $level = LogLevel::INFO;
                break;
            case "3":
                $level = LogLevel::WARNING;
                break;
            case "9":
                $level = LogLevel::ERROR;
                break;
            default:
                $level = LogLevel::INFO;
        }

        $this->logger->log($level, '[' . $type . '] ' . $message);
    }

    /**
     * Gets the dialog ID.
     *
     * @return integer
     */
    public function getDialogId()
    {
        return $this->dialogId;
    }

    /**
     * Gets the current message number.
     *
     * @return int
     */
    public function getMessageNumber()
    {
        return $this->messageNumber;
    }

    /**
     * Gets the system ID.
     *
     * @return int|string
     */
    public function getSystemId()
    {
        return $this->systemId;
    }

    /**
     * Gets all supported TAN mechanisms.
     *
     * @return array
     */
    public function getSupportedPinTanMechanisms()
    {
        return $this->supportedTanMechanisms;
    }

    /**
     * Gets the max possible HKSAL version.
     *
     * @return int
     */
    public function getHksalMaxVersion()
    {
        return $this->hksalVersion;
    }

    /**
     * Gets the max possible HKKAZ version.
     *
     * @return int
     */
    public function getHkkazMaxVersion()
    {
        return $this->hkkazVersion;
    }

    /**
     * Gets the bank name.
     *
     * @return string
     */
    public function getBankName()
    {
        return $this->bankName;
    }

    /**
     * Initializes a dialog.
     *
     * @return string|null
     * @throws CurlException
     * @throws FailedRequestException
     * @throws \Exception
     */
    public function initDialog()
    {
        $this->logger->info('');
        $this->logger->info('DIALOG initialize');
        $this->logger->debug('Registered product: ' . trim($this->productName . ' ' . $this->productVersion));

        $identification = new HKIDN(3, $this->bankCode, $this->username, $this->systemId);
        $prepare        = new HKVVB(
            4,
            HKVVB::DEFAULT_BPD_VERSION,
            HKVVB::DEFAULT_UPD_VERSION,
            HKVVB::LANG_DEFAULT,
            $this->productName,
            $this->productVersion
        );

        $message = new Message(
            $this->bankCode,
            $this->username,
            $this->pin,
            $this->systemId,
            0,
            1,
            array(
				$identification, 
				$prepare,
				new HKTAN(HKTAN::VERSION, 5)
			),
            array(AbstractMessage::OPT_PINTAN_MECH => $this->supportedTanMechanisms)
        );

        #$this->logger->debug('Sending INIT message:');
        #$this->logger->debug((string) $message);

        $response = $this->sendMessage($message)->rawResponse;
        #$this->logger->debug('Got INIT response:');
        #$this->logger->debug($response);

        $result = new Initialization($response);
        $this->dialogId = $result->getDialogId();
        $this->logger->info('Received dialog ID: ' . $this->dialogId);

        $this->logger->info('DIALOG end');
		
        return $this->dialogId;
    }

    /**
     * Sends sync request.
     *
     * @param boolean
     * @return string
     * @throws CurlException
     * @throws FailedRequestException
     * @throws \Exception
     */
    public function syncDialog($sendHKTan = true, $tanMechanism = null)
    {
		
        $this->logger->info('');
        $this->logger->info('SYNC initialize');
        $this->messageNumber = 1;
        $this->systemId = 0;
        $this->dialogId = 0;

        $identification = new HKIDN(3, $this->bankCode, $this->username, 0);
        $prepare        = new HKVVB(
            4,
            HKVVB::DEFAULT_BPD_VERSION,
            HKVVB::DEFAULT_UPD_VERSION,
            HKVVB::LANG_DEFAULT,
            $this->productName,
            $this->productVersion
        );

		if($sendHKTan){
			$options = array();
			if($tanMechanism)
				$options[AbstractMessage::OPT_PINTAN_MECH] = array($tanMechanism);
			
			$syncMsg = new Message(
				$this->bankCode,
				$this->username,
				$this->pin,
				$this->systemId,
				$this->dialogId,
				$this->messageNumber,
				array(
					$identification,
					$prepare, 
					new HKTAN(HKTAN::VERSION, 5),
					new HKSYN(6)
				),
				$options
			);
		} else
			$syncMsg = new Message(
				$this->bankCode,
				$this->username,
				$this->pin,
				$this->systemId,
				$this->dialogId,
				$this->messageNumber,
				array(
					$identification,
					$prepare, 
					new HKSYN(5)
				)
			);

        #$this->logger->debug('Sending SYNC message:');
        #$this->logger->debug((string) $syncMsg);
        $response = $this->sendMessage($syncMsg);

		#$this->checkResponse($response);

        #$this->logger->debug('Received SYNC response:');
        #$this->logger->debug($response->rawResponse);

        // save BPD (Bank Parameter Daten)
        $this->systemId = $response->getSystemId();
        $this->dialogId = $response->getDialogId();
        $this->bankName = $response->getBankName();

        // max version for segment HKSAL (Saldo abfragen)
        $this->hksalVersion = $response->getHksalMaxVersion();
        $this->supportedTanMechanisms = $response->getSupportedTanMechanisms();

        // max version for segment HKKAZ (Kontoumsätze anfordern / Zeitraum)
        $this->hkkazVersion = $response->getHkkazMaxVersion();

        $this->logger->info('Received system id: ' . $response->getSystemId());
        $this->logger->info('Received dialog id: ' . $response->getDialogId());
        $this->logger->info('Supported TAN mechanisms: ' . implode(', ', $this->supportedTanMechanisms));
        $this->logger->info('SYNC end');
		
        return $response;
    }

	/*public function checkResponse(Response $response){
		foreach($response->getSegmentSummary() AS $k => $v)
			if(substr($k, 0, 1) == "9")
				throw new \Exception($v, $k);
		
	}*/
	
    /**
     * Ends a previous started dialog.
     *
     * @return string
     * @throws CurlException
     * @throws FailedRequestException
     */
    public function endDialog()
    {
        $this->logger->info('');
        $this->logger->info('END initialize');

        $endMsg = new Message(
            $this->bankCode,
            $this->username,
            $this->pin,
            $this->systemId,
            $this->dialogId,
            $this->messageNumber,
            array(
                new HKEND(3, $this->dialogId)
            )
        );

        #$this->logger->debug("S ".(string) $endMsg);
        $response = $this->sendMessage($endMsg);
        #$this->logger->debug("R ".$response->rawResponse);

        $this->logger->info('Resetting dialog ID and message number count');
        $this->dialogId = 0;
        $this->messageNumber = 1;
		
        $this->logger->info('END end');

        return $response->rawResponse;
    }
}

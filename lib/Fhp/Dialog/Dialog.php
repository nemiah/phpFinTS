<?php
namespace Fhp\Dialog;

use Fhp\Adapter\Exception\AdapterException;
use Fhp\Adapter\Exception\CurlException;
use Fhp\Connection;
use Fhp\Dialog\Exception\FailedRequestException;
use Fhp\Message\AbstractMessage;
use Fhp\Message\Message;
use Fhp\Response\Initialization;
use Fhp\Response\Response;
use Fhp\Segment\HKEND;
use Fhp\Segment\HKIDN;
use Fhp\Segment\HKSYN;
use Fhp\Segment\HKVVB;
use Monolog\Logger;
use Psr\Log\LoggerInterface;

class Dialog
{
    const DEFAULT_COUNTRY_CODE = 280;

    /**
     * @var Connection
     */
    protected $connection;
    protected $logger;
    protected $messageNumber = 1;
    protected $dialogId = 0;
    protected $systemId = 0;
    protected $bankCode;
    protected $username;
    protected $pin;
    protected $bankName;

    protected $supportedTanMechanisms = [];
    protected $hksalVersion = 6;
    protected $hkkazVersion = 6;

    public function __construct(Connection $connection, $bankCode, $username, $pin, $systemId, LoggerInterface $logger)
    {
        $this->connection = $connection;
        $this->bankCode = $bankCode;
        $this->username = $username;
        $this->pin = $pin;
        $this->systemId = $systemId;
        $this->logger = $logger;
    }

    public function sendMessage(AbstractMessage $message)
    {
        try {
            $this->logger->info('Sending Message');
            $message->setMessageNumber($this->messageNumber);
            $message->setDialogId($this->dialogId);

            $result = $this->connection->send($message);
            $this->messageNumber++;
            $response = new Response($result);

            $this->handleResponse($response);

            if (!$response->isSuccess()) {
                $summary = $response->getMessageSummary();
                $ex = new FailedRequestException($summary);
                $this->logger->error($ex->getMessage());
                throw $ex;
            }

            return $response;
        } catch (AdapterException $e) {
            $this->logger->critical($e->getMessage());
            if ($e instanceof CurlException) {
                $this->logger->debug(print_r($e->getCurlInfo(), true));
            }

            throw $e;
        }
    }

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
    }

    protected function logMessage($type, $code, $message)
    {
        switch (substr($code, 0, 1)) {
            case '0':
                $level = Logger::INFO;
                break;
            case "3":
                $level = Logger::WARNING;
                break;
            case "9":
                $level = Logger::ERROR;
                break;
            default:
                $level = Logger::INFO;
        }

        $this->logger->log($level, '[' . $type . '] ' . $message);
    }

    public function getDialogId()
    {
        return $this->dialogId;
    }

    public function getMessageNumber()
    {
        return $this->messageNumber;
    }

    public function getSystemId()
    {
        return $this->systemId;
    }

    public function getSupportedPinTanMechanisms()
    {
        return $this->supportedTanMechanisms;
    }

    public function getHksalMaxVersion()
    {
        return $this->hksalVersion;
    }

    public function getHkkazMaxVersion()
    {
        return $this->hkkazVersion;
    }

    public function getBankName()
    {
        return $this->bankName;
    }

    public function initDialog()
    {
        $this->logger->info('Initialize Dialog');
        $identification = new HKIDN(3, $this->bankCode, $this->username, $this->systemId);
        $prepare        = new HKVVB(4, HKVVB::DEFAULT_BPD_VERSION, HKVVB::DEFAULT_UPD_VERSION, HKVVB::LANG_DEFAULT);

        $message = new Message(
            $this->bankCode,
            $this->username,
            $this->pin,
            $this->systemId,
            0,
            1,
            array($identification, $prepare),
            array(AbstractMessage::OPT_PINTAN_MECH => $this->supportedTanMechanisms)
        );

        $this->logger->debug('Sending INIT message: ' . (string) $message);

        $response = $this->sendMessage($message)->rawResponse;
        $this->logger->debug('Got INIT response: ' . $response);

        $result = new Initialization($response);
        $this->dialogId = $result->getDialogId();
        $this->logger->info('Received dialog ID: ' . $this->dialogId);

        return $this->dialogId;
    }

    public function syncDialog()
    {
        $this->logger->info('Initialize SYNC');
        $this->messageNumber = 1;
        $this->systemId = 0;
        $this->dialogId = 0;

        $identification = new HKIDN(3, $this->bankCode, $this->username, 0);
        $prepare        = new HKVVB(4, HKVVB::DEFAULT_BPD_VERSION, HKVVB::DEFAULT_UPD_VERSION, HKVVB::LANG_DEFAULT);
        $sync           = new HKSYN(5);

        $syncMsg = new Message(
            $this->bankCode,
            $this->username,
            $this->pin,
            $this->systemId,
            $this->dialogId,
            $this->messageNumber,
            array($identification, $prepare, $sync)
        );

        $this->logger->debug('Sending SYNC message: ' . (string) $syncMsg);
        $response = $this->sendMessage($syncMsg);

        $this->logger->debug('Got SYNC response: ' . $response->rawResponse);

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

        $this->endDialog();

        return $response->rawResponse;
    }

    public function endDialog()
    {
        $this->logger->info('Initialize END dialog message');

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

        $this->logger->debug('Sending END message: ' . (string) $endMsg);
        $response = $this->sendMessage($endMsg);
        $this->logger->debug('Got END response: ' . $response->rawResponse);

        $this->logger->info('Resetting dialog ID and message number count');
        $this->dialogId = 0;
        $this->messageNumber = 1;

        return $response->rawResponse;
    }
}

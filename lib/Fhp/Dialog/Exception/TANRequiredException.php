<?php

namespace Fhp\Dialog\Exception;

use Fhp\Dialog\Dialog;
use Fhp\Message\Message;
use Fhp\Response\GetTANRequest;

/**
 *
 * @package Fhp\Dialog\Exception
 */
class TANRequiredException extends \Exception
{
    const TAN_TOKEN_VALUE_ORDER = ['processId', 'systemId', 'dialogId', 'messageNumber', 'tanMechanism', 'tanMediaName'];

    /** @var string */
    protected $tanMechanism;
    /** @var null|string */
    protected $tanMediaName = null;
    /** @var string */
    protected $systemId;
    /** @var string */
    protected $dialogId;
    /** @var int */
    protected $messageNumber;
    /** @var string */
    protected $processId;

    /** @var Message */
    protected $cause;

    /** @var GetTANRequest */
    protected $response;

    public function __construct(GetTANRequest $response, Message $cause, Dialog $dialog)
    {
        $this->response = $response;
        $this->cause = $cause;
        $this->tanMechanism = $cause->getSecurityFunction();
        // TODO TanMediaName ermitteln
        //$this->tanMediaName = ;

        $this->systemId = $dialog->getSystemId();
        $this->dialogId = $dialog->getDialogId();
        $this->messageNumber = $dialog->getMessageNumber();
        $this->processId = $response->get()->getProcessID();
        parent::__construct(implode('\n', $response->getSegmentSummary())
            . "\nSystem-ID:" . $this->systemId . ' Dialog-ID:' . $this->dialogId . ' Nachrichtennummer:' . $this->messageNumber . ' Auftrags-Referenz:' . $this->processId
            #. "\n" . '"' . $this->systemId . ' ' . $this->dialogId . ' ' . $this->messageNumber . ' ' . $this->processId . '"'
        );
    }

    /**
     * @return GetTANRequest
     */
    public function getResponse(): GetTANRequest
    {
        return $this->response;
    }

    /**
     * @return string
     */
    public function getTanMechanism(): string
    {
        return $this->tanMechanism;
    }

    /**
     * @return string|null
     */
    public function getTanMediaName(): ?string
    {
        return $this->tanMediaName;
    }

    /**
     * @return string
     */
    public function getSystemId()
    {
        return $this->systemId;
    }

    /**
     * @return string
     */
    public function getDialogId()
    {
        return $this->dialogId;
    }

    /**
     * @return int
     */
    public function getMessageNumber()
    {
        return $this->messageNumber;
    }

    /**
     * @return string
     */
    public function getProcessId()
    {
        return $this->processId;
    }

    /**
     * Konkateniert die benötigten Wert mit Tilde (~),
     * da Tilde nicht im FinTS-Basiszeichensatz enthalten ist und somit nicht in einem
     * dieser Wert vorkommen kann und ~ außerdem URL-Safe ist.
     *
     * @return string
     */
    public function getTANToken()
    {
        $values = [];
        foreach (self::TAN_TOKEN_VALUE_ORDER as $name) {
            $values[] = $this->$name;
        }
        return implode('~', $values);
    }
}
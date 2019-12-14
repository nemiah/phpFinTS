<?php

namespace Fhp\Dialog\Exception;

use Fhp\Dialog\Dialog;
use Fhp\Message\Message;
use Fhp\Response\GetTANRequest;

class TANRequiredException extends \Exception
{
    const TAN_TOKEN_VALUE_ORDER = ['processId', 'systemId', 'dialogId', 'messageNumber', 'tanMechanism', 'tanMediaName'];

    /** @var string */
    protected $tanMechanism;
    /** @var string|null */
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
        $this->tanMediaName = $response->getTanMediumName();

        $this->systemId = $dialog->getSystemId();
        $this->dialogId = $dialog->getDialogId();
        $this->messageNumber = $dialog->getMessageNumber();
        $this->processId = $response->get()->getProcessID();

        parent::__construct('Sicherheitsfreigabe erforderlich; Challenge: ' . $response->getTanChallenge());
    }

    public function getResponse(): GetTANRequest
    {
        return $this->response;
    }

    public function getTanMechanism(): string
    {
        return $this->tanMechanism;
    }

    public function getTanMediaName(): ?string
    {
        return $this->tanMediaName;
    }

    public function getSystemId(): string
    {
        return $this->systemId;
    }

    public function getDialogId(): string
    {
        return $this->dialogId;
    }

    public function getMessageNumber(): int
    {
        return $this->messageNumber;
    }

    public function getProcessId(): string
    {
        return $this->processId;
    }

    /**
     * Konkateniert die benötigten Wert mit Tilde (~),
     * da Tilde nicht im FinTS-Basiszeichensatz enthalten ist und somit nicht in einem
     * dieser Wert vorkommen kann und ~ außerdem URL-Safe ist.
     */
    public function getTANToken(): string
    {
        $values = [];
        foreach (self::TAN_TOKEN_VALUE_ORDER as $name) {
            $values[] = $this->$name;
        }
        return implode('~', $values);
    }
}

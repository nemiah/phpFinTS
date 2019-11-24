<?php

namespace Fhp\Model;

class TANRequest
{
    /** @var string */
    protected $processID;

    public function __construct($processID)
    {
        $this->processID = (string) $processID;
    }

    public function getProcessID()
    {
        return $this->processID;
    }
}

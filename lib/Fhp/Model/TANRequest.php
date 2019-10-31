<?php

namespace Fhp\Model;

class TANRequest
{
    /** @var string */
    protected $processID;
	
	
	public function setProcessID($processID)
	{
		$this->processID = (string) $processID;
		
        return $this;
	}
	
	public function getProcessID()
	{
		return $this->processID;
	}
}

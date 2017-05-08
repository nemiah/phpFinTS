<?php

namespace Fhp\Model;

/**
 * Class TANRequest
 * @package Fhp\Model
 * @author Nena Furtmeier <support@furtmeier.it>
 */
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

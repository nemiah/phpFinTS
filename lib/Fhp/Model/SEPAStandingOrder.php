<?php

namespace Fhp\Model;

/**
 * Class SEPAStandingOrder
 * @package Fhp\Model
 * @author Nena Furtmeier <support@furtmeier.it>
 */
class SEPAStandingOrder
{
    /** @var string */
    protected $id;
    /** @var string */
    protected $iban;
    /** @var string */
    protected $bic;
    /** @var string */
	protected $creditor;
	/** @var float */
	protected $amount;
    /** @var string */
	protected $xml;
    /** @var string */
	protected $firstExecution;
    /** @var string */
	protected $timeUnit;
    /** @var string */
	protected $interval;
    /** @var string */
	protected $executionDay;
	
	
	public function setFirstExecution($date)
	{
		$this->firstExecution = (string) $date;
		
        return $this;
	}
	
	public function setTimeUnit($u)
	{
		$this->timeUnit = (string) $u;
		
        return $this;
	}
	
	public function setInterval($interval)
	{
		$this->interval = (string) $interval;
		
        return $this;
	}
	
	public function setExecutionDay($day)
	{
		$this->executionDay = (string) $day;
		
        return $this;
	}
	
	public function setXML($xml)
	{
		$this->xml = (string) $xml;
		
        return $this;
	}
	
	public function setId($id)
	{
		$this->id = (string) $id;
		
        return $this;
	}
	
	public function setCreditor($name)
	{
		$this->creditor = (string) $name;
		
        return $this;
	}
	
	public function setIban($iban)
	{
		$this->iban = (string) $iban;
		
        return $this;
	}
	
    public function setBic($bic)
    {
        $this->bic = (string) $bic;

        return $this;
    }
	
	public function setAmount($amount)
	{
		$this->amount = (float) $amount;
		
		return $this;
	}
	
	
	public function getId()
	{
		return $this->id;
	}
	
	public function getCreditor()
	{
		return $this->creditor;
	}
	
	public function getIban()
	{
		return $this->iban;
	}
	
	public function getBic()
	{
		return $this->bic;
	}
	
	public function getAmount()
	{
		return $this->amount;
	}
	
	public function getXML()
	{
		return $this->xml;
	}
	
	public function getFirstExecution()
	{
		return $this->firstExecution;
	}
	
	public function getTimeUnit()
	{
		return $this->timeUnit;
	}
	
	public function getInterval()
	{
		return $this->interval;
	}
	
	public function getExecutionDay()
	{
		return $this->executionDay;
	}
}

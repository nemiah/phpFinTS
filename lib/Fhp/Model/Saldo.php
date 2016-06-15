<?php

namespace Fhp\Model;

/**
 * Class Saldo
 * @package Fhp\Model
 */
class Saldo
{
    /**
     * @var string
     */
    protected $currency;

    /**
     * @var float
     */
    protected $amount;

    /**
     * @var \DateTime
     */
    protected $valuta;

    /**
     * Get currency
     *
     * @return string
     */
    public function getCurrency()
    {
        return $this->currency;
    }

    /**
     * Set currency
     *
     * @param string $currency
     *
     * @return $this
     */
    public function setCurrency($currency)
    {
        $this->currency = (string) $currency;

        return $this;
    }

    /**
     * Get amount
     *
     * @return float
     */
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * Set amount
     *
     * @param float $amount
     *
     * @return $this
     */
    public function setAmount($amount)
    {
        $this->amount = (float) $amount;

        return $this;
    }

    /**
     * Get valuta
     *
     * @return \DateTime
     */
    public function getValuta()
    {
        return $this->valuta;
    }

    /**
     * Set valuta
     *
     * @param \DateTime $valuta
     *
     * @return $this
     */
    public function setValuta(\DateTime $valuta)
    {
        $this->valuta = $valuta;

        return $this;
    }
}

<?php

namespace Fhp\Model;

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
    public function getCurrency(): string
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
    public function setCurrency(string $currency)
    {
        $this->currency = (string) $currency;

        return $this;
    }

    /**
     * Get amount
     *
     * @return float
     */
    public function getAmount(): float
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
    public function setAmount(float $amount)
    {
        $this->amount = (float) $amount;

        return $this;
    }

    /**
     * Get valuta
     *
     * @return \DateTime
     */
    public function getValuta(): \DateTime
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

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
     */
    public function getCurrency(): string
    {
        return $this->currency;
    }

    /**
     * Set currency
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
     */
    public function getAmount(): float
    {
        return $this->amount;
    }

    /**
     * Set amount
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
     */
    public function getValuta(): \DateTime
    {
        return $this->valuta;
    }

    /**
     * Set valuta
     *
     * @return $this
     */
    public function setValuta(\DateTime $valuta)
    {
        $this->valuta = $valuta;

        return $this;
    }
}

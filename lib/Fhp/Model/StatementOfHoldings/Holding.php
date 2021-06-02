<?php

namespace Fhp\Model\StatementOfHoldings;

class Holding
{
    /**
     * @var string
     */
    protected $isin;

    /**
     * @var string
     */
    protected $wkn;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var float
     */
    protected $price = 0.0;

    /**
     * @var float
     */
    protected $amount = 0.0;

    /**
     * @var float
     */
    protected $value = 0.0;

    /**
     * @var \DateTime|null
     */
    protected $date;

    /**
     * @var \DateTime|null
     */
    protected $time;

    /**
     * @var string
     */
    protected $currency;

    /**
     * Set ISIN
     *
     * @return $this
     */
    public function setISIN(?string $isin)
    {
        $this->isin = $isin;

        return $this;
    }

    /**
     * Set WKN
     *
     * @return $this
     */
    public function setWKN(?string $wkn)
    {
        $this->wkn = $wkn;

        return $this;
    }

    /**
     * Set Name
     *
     * @return $this
     */
    public function setName(?string $name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Set value
     *
     * @return $this
     */
    public function setValue(?float $value)
    {
        $this->value = (float) $value;

        return $this;
    }

    /**
     * Set price
     *
     * @return $this
     */
    public function setPrice(?float $price)
    {
        $this->price = (float) $price;

        return $this;
    }

    /**
     * Set amount
     *
     * @return $this
     */
    public function setAmount(?float $amount)
    {
        $this->amount = (float) $amount;

        return $this;
    }

    /**
     * Set currency
     *
     * @return $this
     */
    public function setCurrency(?string $currency)
    {
        $this->currency = $currency;

        return $this;
    }

    /**
     * Set date
     *
     * @return $this
     */
    public function setDate(\DateTime $date)
    {
        $this->date = $date;

        return $this;
    }

    /**
     * Set time
     *
     * @return $this
     */
    public function setTime(\DateTime $time)
    {
        $this->time = $time;

        return $this;
    }

    /**
     * get ISIN
     *
     * @return ISIN
     */
    public function getISIN(): ?string
    {
        return $this->isin;
    }

    /**
     * Set WKN
     *
     * @return $this
     */
    public function getWKN(): ?string
    {
        return $this->wkn;
    }

    /**
     * Set Name
     *
     * @return $this
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * Set price
     *
     * @return $this
     */
    public function getValue(): ?float
    {
        return $this->price;
    }

    /**
     * Set price
     *
     * @return $this
     */
    public function getPrice(): ?float
    {
        return $this->price;
    }

    /**
     * Set amount
     *
     * @return $this
     */
    public function getAmount(): ?float
    {
        return $this->amount;
    }

    /**
     * Set currency
     *
     * @return $this
     */
    public function getCurrency(): ?string
    {
        return $this->currency;
    }

    /**
     * Set time
     *
     * @return $this
     */
    public function getTime(): \DateTime
    {
        return $this->time;
    }

    /**
     * Get date
     */
    public function getDate(): \DateTime
    {
        return $this->date;
    }
}

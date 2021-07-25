<?php

namespace Fhp\Model\StatementOfHoldings;

class Holding
{
    /**
     * @var string|null
     */
    protected $isin;

    /**
     * @var string|null
     */
    protected $wkn;

    /**
     * @var string|null
     */
    protected $name;

    /**
     * @var float|null
     */
    protected $price;

    /**
     * @var float|null
     */
    protected $amount;

    /**
     * @var float|null
     */
    protected $value;

    /**
     * @var \DateTime|null
     */
    protected $date;

    /**
     * @var \DateTime|null
     */
    protected $time;

    /**
     * @var string|null
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
        $this->value = $value;

        return $this;
    }

    /**
     * Set price
     *
     * @return $this
     */
    public function setPrice(?float $price)
    {
        $this->price = $price;

        return $this;
    }

    /**
     * Set amount
     *
     * @return $this
     */
    public function setAmount(?float $amount)
    {
        $this->amount = $amount;

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
     * Get ISIN
     *
     * @return string|null
     */
    public function getISIN(): ?string
    {
        return $this->isin;
    }

    /**
     * Get WKN
     *
     * @return string|null
     */
    public function getWKN(): ?string
    {
        return $this->wkn;
    }

    /**
     * Get Name
     *
     * @return string|null
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * Get value
     *
     * @return float|null
     */
    public function getValue(): ?float
    {
        return $this->value;
    }

    /**
     * Get price
     *
     * @return float|null
     */
    public function getPrice(): ?float
    {
        return $this->price;
    }

    /**
     * Get amount
     *
     * @return float|null
     */
    public function getAmount(): ?float
    {
        return $this->amount;
    }

    /**
     * Get currency
     *
     * @return string|null
     */
    public function getCurrency(): ?string
    {
        return $this->currency;
    }

    /**
     * Get time
     *
     * @return \DateTime|null
     */
    public function getTime(): ?\DateTime
    {
        return $this->time;
    }

    /**
     * Get date
     *
     * @return \DateTime|null
     */
    public function getDate(): ?\DateTime
    {
        return $this->date;
    }
}

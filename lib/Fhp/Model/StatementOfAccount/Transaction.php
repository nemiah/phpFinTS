<?php

namespace Fhp\Model\StatementOfAccount;

/**
 * Class Transaction
 * @package Fhp\Model\StatementOfAccount
 */
class Transaction
{
    /**
     * @var \DateTime|null
     */
    protected $date;

    /**
     * @var float
     */
    protected $amount;

    /**
     * @var string
     */
    protected $creditDebit;

    /**
     * @var string
     */
    protected $bookingText;

    /**
     * @var string
     */
    protected $description1;

    /**
     * @var string
     */
    protected $description2;

    /**
     * @var string
     */
    protected $bankCode;

    /**
     * @var string
     */
    protected $accountNumber;

    /**
     * @var string
     */
    protected $name;

    /**
     * Get date
     *
     * @return \DateTime|null
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * Set date
     *
     * @param \DateTime|null $date
     *
     * @return $this
     */
    public function setDate(\DateTime $date = null)
    {
        $this->date = $date;

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
     * Get creditDebit
     *
     * @return string
     */
    public function getCreditDebit()
    {
        return $this->creditDebit;
    }

    /**
     * Set creditDebit
     *
     * @param string $creditDebit
     *
     * @return $this
     */
    public function setCreditDebit($creditDebit)
    {
        $this->creditDebit = $creditDebit;

        return $this;
    }

    /**
     * Get bookingText
     *
     * @return string
     */
    public function getBookingText()
    {
        return $this->bookingText;
    }

    /**
     * Set bookingText
     *
     * @param string $bookingText
     *
     * @return $this
     */
    public function setBookingText($bookingText)
    {
        $this->bookingText = (string) $bookingText;

        return $this;
    }

    /**
     * Get description1
     *
     * @return string
     */
    public function getDescription1()
    {
        return $this->description1;
    }

    /**
     * Set description1
     *
     * @param string $description1
     *
     * @return $this
     */
    public function setDescription1($description1)
    {
        $this->description1 = (string) $description1;

        return $this;
    }

    /**
     * Get description2
     *
     * @return string
     */
    public function getDescription2()
    {
        return $this->description2;
    }

    /**
     * Set description2
     *
     * @param string $description2
     *
     * @return $this
     */
    public function setDescription2($description2)
    {
        $this->description2 = (string) $description2;

        return $this;
    }

    /**
     * Get bankCode
     *
     * @return string
     */
    public function getBankCode()
    {
        return $this->bankCode;
    }

    /**
     * Set bankCode
     *
     * @param string $bankCode
     *
     * @return $this
     */
    public function setBankCode($bankCode)
    {
        $this->bankCode = (string) $bankCode;

        return $this;
    }

    /**
     * Get accountNumber
     *
     * @return string
     */
    public function getAccountNumber()
    {
        return $this->accountNumber;
    }

    /**
     * Set accountNumber
     *
     * @param string $accountNumber
     *
     * @return $this
     */
    public function setAccountNumber($accountNumber)
    {
        $this->accountNumber = (string) $accountNumber;

        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set name
     *
     * @param string $name
     *
     * @return $this
     */
    public function setName($name)
    {
        $this->name = (string) $name;

        return $this;
    }
}

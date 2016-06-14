<?php

namespace Fhp\Model\StatementOfAccount;

class Transaction
{
    protected $date;
    protected $amount;
    protected $creditDebit;
    protected $bookingText;
    protected $description1;
    protected $description2;
    protected $bankCode;
    protected $accountNumber;
    protected $name;

    /**
     * Get date
     *
     * @return mixed
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * Set date
     *
     * @param mixed $date
     *
     * @return $this
     */
    public function setDate($date)
    {
        $this->date = $date;

        return $this;
    }

    /**
     * Get amount
     *
     * @return mixed
     */
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * Set amount
     *
     * @param mixed $amount
     *
     * @return $this
     */
    public function setAmount($amount)
    {
        $this->amount = $amount;

        return $this;
    }

    /**
     * Get creditDebit
     *
     * @return mixed
     */
    public function getCreditDebit()
    {
        return $this->creditDebit;
    }

    /**
     * Set creditDebit
     *
     * @param mixed $creditDebit
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
     * @return mixed
     */
    public function getBookingText()
    {
        return $this->bookingText;
    }

    /**
     * Set bookingText
     *
     * @param mixed $bookingText
     *
     * @return $this
     */
    public function setBookingText($bookingText)
    {
        $this->bookingText = $bookingText;

        return $this;
    }

    /**
     * Get description1
     *
     * @return mixed
     */
    public function getDescription1()
    {
        return $this->description1;
    }

    /**
     * Set description1
     *
     * @param mixed $description1
     *
     * @return $this
     */
    public function setDescription1($description1)
    {
        $this->description1 = $description1;

        return $this;
    }

    /**
     * Get description2
     *
     * @return mixed
     */
    public function getDescription2()
    {
        return $this->description2;
    }

    /**
     * Set description2
     *
     * @param mixed $description2
     *
     * @return $this
     */
    public function setDescription2($description2)
    {
        $this->description2 = $description2;

        return $this;
    }

    /**
     * Get bankCode
     *
     * @return mixed
     */
    public function getBankCode()
    {
        return $this->bankCode;
    }

    /**
     * Set bankCode
     *
     * @param mixed $bankCode
     *
     * @return $this
     */
    public function setBankCode($bankCode)
    {
        $this->bankCode = $bankCode;

        return $this;
    }

    /**
     * Get accountNumber
     *
     * @return mixed
     */
    public function getAccountNumber()
    {
        return $this->accountNumber;
    }

    /**
     * Set accountNumber
     *
     * @param mixed $accountNumber
     *
     * @return $this
     */
    public function setAccountNumber($accountNumber)
    {
        $this->accountNumber = $accountNumber;

        return $this;
    }

    /**
     * Get name
     *
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set name
     *
     * @param mixed $name
     *
     * @return $this
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

}

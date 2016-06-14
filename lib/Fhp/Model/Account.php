<?php

namespace Fhp\Model;

class Account
{
    protected $id;
    protected $accountNumber;
    protected $bankCode;
    protected $iban;
    protected $customerId;
    protected $currency;
    protected $accountOwnerName;
    protected $accountDescription;

    /**
     * Get id
     *
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set id
     *
     * @param mixed $id
     *
     * @return $this
     */
    public function setId($id)
    {
        $this->id = $id;

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
     * Get iban
     *
     * @return mixed
     */
    public function getIban()
    {
        return $this->iban;
    }

    /**
     * Set iban
     *
     * @param mixed $iban
     *
     * @return $this
     */
    public function setIban($iban)
    {
        $this->iban = $iban;

        return $this;
    }

    /**
     * Get customerId
     *
     * @return mixed
     */
    public function getCustomerId()
    {
        return $this->customerId;
    }

    /**
     * Set customerId
     *
     * @param mixed $customerId
     *
     * @return $this
     */
    public function setCustomerId($customerId)
    {
        $this->customerId = $customerId;

        return $this;
    }

    /**
     * Get currency
     *
     * @return mixed
     */
    public function getCurrency()
    {
        return $this->currency;
    }

    /**
     * Set currency
     *
     * @param mixed $currency
     *
     * @return $this
     */
    public function setCurrency($currency)
    {
        $this->currency = $currency;

        return $this;
    }

    /**
     * Get accountOwnerName
     *
     * @return mixed
     */
    public function getAccountOwnerName()
    {
        return $this->accountOwnerName;
    }

    /**
     * Set accountOwnerName
     *
     * @param mixed $accountOwnerName
     *
     * @return $this
     */
    public function setAccountOwnerName($accountOwnerName)
    {
        $this->accountOwnerName = $accountOwnerName;

        return $this;
    }

    /**
     * Get accountDescription
     *
     * @return mixed
     */
    public function getAccountDescription()
    {
        return $this->accountDescription;
    }

    /**
     * Set accountDescription
     *
     * @param mixed $accountDescription
     *
     * @return $this
     */
    public function setAccountDescription($accountDescription)
    {
        $this->accountDescription = $accountDescription;

        return $this;
    }
}

<?php

namespace Fhp\Model;

/**
 * Class Account
 * @package Fhp\Model
 */
class Account
{
    /** @var string */
    protected $id;
    /** @var string */
    protected $accountNumber;
    /** @var string */
    protected $bankCode;
    /** @var string */
    protected $iban;
    /** @var string */
    protected $customerId;
    /** @var string */
    protected $currency;
    /** @var string */
    protected $accountOwnerName;
    /** @var string */
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
     * @param string $id
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
     * Get iban
     *
     * @return string
     */
    public function getIban()
    {
        return $this->iban;
    }

    /**
     * Set iban
     *
     * @param string $iban
     *
     * @return $this
     */
    public function setIban($iban)
    {
        $this->iban = (string) $iban;

        return $this;
    }

    /**
     * Get customerId
     *
     * @return string
     */
    public function getCustomerId()
    {
        return $this->customerId;
    }

    /**
     * Set customerId
     *
     * @param string $customerId
     *
     * @return $this
     */
    public function setCustomerId($customerId)
    {
        $this->customerId = (string) $customerId;

        return $this;
    }

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
     * Get accountOwnerName
     *
     * @return string
     */
    public function getAccountOwnerName()
    {
        return $this->accountOwnerName;
    }

    /**
     * Set accountOwnerName
     *
     * @param string $accountOwnerName
     *
     * @return $this
     */
    public function setAccountOwnerName($accountOwnerName)
    {
        $this->accountOwnerName = (string) $accountOwnerName;

        return $this;
    }

    /**
     * Get accountDescription
     *
     * @return string
     */
    public function getAccountDescription()
    {
        return $this->accountDescription;
    }

    /**
     * Set accountDescription
     *
     * @param string $accountDescription
     *
     * @return $this
     */
    public function setAccountDescription($accountDescription)
    {
        $this->accountDescription = (string) $accountDescription;

        return $this;
    }
}

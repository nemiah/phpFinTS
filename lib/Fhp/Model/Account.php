<?php

namespace Fhp\Model;

/**
 * Note: This account information is obtained from the HIUPD contained in the UPD data, but it lacks the BIC.
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
     * @return $this
     */
    public function setId(string $id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * Get accountNumber
     */
    public function getAccountNumber(): string
    {
        return $this->accountNumber;
    }

    /**
     * Set accountNumber
     *
     * @return $this
     */
    public function setAccountNumber(string $accountNumber)
    {
        $this->accountNumber = (string) $accountNumber;

        return $this;
    }

    /**
     * Get bankCode
     */
    public function getBankCode(): string
    {
        return $this->bankCode;
    }

    /**
     * Set bankCode
     *
     * @return $this
     */
    public function setBankCode(string $bankCode)
    {
        $this->bankCode = (string) $bankCode;

        return $this;
    }

    /**
     * Get iban
     */
    public function getIban(): string
    {
        return $this->iban;
    }

    /**
     * Set iban
     *
     * @return $this
     */
    public function setIban(string $iban)
    {
        $this->iban = (string) $iban;

        return $this;
    }

    /**
     * Get customerId
     */
    public function getCustomerId(): string
    {
        return $this->customerId;
    }

    /**
     * Set customerId
     *
     * @return $this
     */
    public function setCustomerId(string $customerId)
    {
        $this->customerId = (string) $customerId;

        return $this;
    }

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
     * Get accountOwnerName
     */
    public function getAccountOwnerName(): string
    {
        return $this->accountOwnerName;
    }

    /**
     * Set accountOwnerName
     *
     * @return $this
     */
    public function setAccountOwnerName(string $accountOwnerName)
    {
        $this->accountOwnerName = (string) $accountOwnerName;

        return $this;
    }

    /**
     * Get accountDescription
     */
    public function getAccountDescription(): string
    {
        return $this->accountDescription;
    }

    /**
     * Set accountDescription
     *
     * @return $this
     */
    public function setAccountDescription(string $accountDescription)
    {
        $this->accountDescription = (string) $accountDescription;

        return $this;
    }
}

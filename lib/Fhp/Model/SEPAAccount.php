<?php

namespace Fhp\Model;

/**
 * Note: This account information is obtained from the HISPA response to a HKSPA request.
 */
class SEPAAccount
{
    /** @var string */
    protected $iban;
    /** @var string */
    protected $bic;
    /** @var string */
    protected $accountNumber;
    /** @var string */
    protected $subAccount;
    /** @var string */
    protected $blz;

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
     * Get bic
     */
    public function getBic(): string
    {
        return $this->bic;
    }

    /**
     * Set bic
     *
     * @return $this
     */
    public function setBic(string $bic)
    {
        $this->bic = (string) $bic;

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
     * Get subAccount
     */
    public function getSubAccount(): string
    {
        return $this->subAccount;
    }

    /**
     * Set subAccount
     *
     * @return $this
     */
    public function setSubAccount(string $subAccount)
    {
        $this->subAccount = (string) $subAccount;

        return $this;
    }

    /**
     * Get blz
     */
    public function getBlz(): string
    {
        return $this->blz;
    }

    /**
     * Set blz
     *
     * @return $this
     */
    public function setBlz(string $blz)
    {
        $this->blz = (string) $blz;

        return $this;
    }
}

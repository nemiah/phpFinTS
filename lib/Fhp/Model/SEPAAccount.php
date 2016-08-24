<?php

namespace Fhp\Model;

/**
 * Class SEPAAccount
 * @package Fhp\Model
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
     * Get bic
     *
     * @return string
     */
    public function getBic()
    {
        return $this->bic;
    }

    /**
     * Set bic
     *
     * @param string $bic
     *
     * @return $this
     */
    public function setBic($bic)
    {
        $this->bic = (string) $bic;

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
     * Get subAccount
     *
     * @return string
     */
    public function getSubAccount()
    {
        return $this->subAccount;
    }

    /**
     * Set subAccount
     *
     * @param string $subAccount
     *
     * @return $this
     */
    public function setSubAccount($subAccount)
    {
        $this->subAccount = (string) $subAccount;

        return $this;
    }

    /**
     * Get blz
     *
     * @return string
     */
    public function getBlz()
    {
        return $this->blz;
    }

    /**
     * Set blz
     *
     * @param string $blz
     *
     * @return $this
     */
    public function setBlz($blz)
    {
        $this->blz = (string) $blz;

        return $this;
    }
}

<?php


namespace Fhp\Model;


class SEPAAccount
{
    protected $isSepaCapable;
    protected $iban;
    protected $bic;
    protected $accountNumber;
    protected $subAccount;
    protected $blz;

    /**
     * Get isSepaCapable
     *
     * @return mixed
     */
    public function getIsSepaCapable()
    {
        return $this->isSepaCapable;
    }

    /**
     * Set isSepaCapable
     *
     * @param mixed $isSepaCapable
     *
     * @return $this
     */
    public function setIsSepaCapable($isSepaCapable)
    {
        $this->isSepaCapable = $isSepaCapable;

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
     * Get bic
     *
     * @return mixed
     */
    public function getBic()
    {
        return $this->bic;
    }

    /**
     * Set bic
     *
     * @param mixed $bic
     *
     * @return $this
     */
    public function setBic($bic)
    {
        $this->bic = $bic;

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
     * Get subAccount
     *
     * @return mixed
     */
    public function getSubAccount()
    {
        return $this->subAccount;
    }

    /**
     * Set subAccount
     *
     * @param mixed $subAccount
     *
     * @return $this
     */
    public function setSubAccount($subAccount)
    {
        $this->subAccount = $subAccount;

        return $this;
    }

    /**
     * Get blz
     *
     * @return mixed
     */
    public function getBlz()
    {
        return $this->blz;
    }

    /**
     * Set blz
     *
     * @param mixed $blz
     *
     * @return $this
     */
    public function setBlz($blz)
    {
        $this->blz = $blz;

        return $this;
    }
}

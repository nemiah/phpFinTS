<?php

namespace Fhp\Model;

/**
 * Note: This account information is obtained from the HISPA response to a HKSPA request.
 */
class SEPAAccount
{
    // All fields are nullable, but the overall SEPAAccount is only valid if at least {IBAN,BIC} or {accountNumber,blz} are present.

    /** @var string|null */
    protected $iban;
    /** @var string|null */
    protected $bic;
    /** @var string|null */
    protected $accountNumber;
    /** @var string|null */
    protected $subAccount;
    /** @var string|null */
    protected $blz;

    public function getIban(): ?string
    {
        return $this->iban;
    }

    /**
     * @return $this
     */
    public function setIban(?string $iban)
    {
        $this->iban = $iban;

        return $this;
    }

    public function getBic(): ?string
    {
        return $this->bic;
    }

    /**
     * @return $this
     */
    public function setBic(?string $bic)
    {
        $this->bic = $bic;

        return $this;
    }

    public function getAccountNumber(): ?string
    {
        return $this->accountNumber;
    }

    /**
     * @return $this
     */
    public function setAccountNumber(?string $accountNumber)
    {
        $this->accountNumber = $accountNumber;

        return $this;
    }

    public function getSubAccount(): ?string
    {
        return $this->subAccount;
    }

    /**
     * @return $this
     */
    public function setSubAccount(?string $subAccount)
    {
        $this->subAccount = $subAccount;

        return $this;
    }

    public function getBlz(): ?string
    {
        return $this->blz;
    }

    /**
     * @return $this
     */
    public function setBlz(?string $blz)
    {
        $this->blz = $blz;

        return $this;
    }
}

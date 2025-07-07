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

    /**
     * Determines how single lines in transaction descriptions are joined, see #481.
     * Defaults to an empty string for maximum compatibility but some banks implicitly assume line breaks for this.
     * This is not provided by the bank and needs to be set manually.
     * @var string|null
     */
    protected $transactionDescriptionLineGlue;

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

    public function getTransactionDescriptionLineGlue(): ?string
    {
        return $this->transactionDescriptionLineGlue;
    }

    /**
     * @return $this
     */
    public function setTransactionDescriptionLineGlue($transactionDescriptionLineGlue)
    {
        $this->transactionDescriptionLineGlue = $transactionDescriptionLineGlue;
        return $this;
    }
}

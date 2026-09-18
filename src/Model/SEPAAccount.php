<?php

namespace Fhp\Model;

/**
 * Note: This account information is obtained from the HISPA response to a HKSPA request. The descriptive fields
 * (holder name, product name, currency, account type) come from the HIUPD segment of the UPD that belongs to the
 * account; {@link \Fhp\Action\GetSEPAAccounts} fills them in when the bank reports such a segment. They are not needed
 * to address the account in a request, so an application that constructs a SEPAAccount itself can leave them empty.
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
    /** The account holder name, as reported in the UPD. */
    protected ?string $name = null;
    /** The bank's product name, e.g. "Girokonto Komfort", as reported in the UPD. */
    protected ?string $productName = null;
    /** The account currency, as reported in the UPD. */
    protected ?string $currency = null;
    /**
     * The FinTS account type, as reported in the UPD (not available before HIUPD v6): 1-9 Kontokorrent/Giro,
     * 10-19 Spar, 20-29 Festgeld, 30-39 Wertpapierdepot, 40-49 Kredit/Darlehen, 60-69 Fonds-Depot, 70-79 Bauspar,
     * 80-89 Versicherung, 90-99 Sonstige. Credit card accounts (50-59) have no IBAN and are therefore never a
     * SEPAAccount, see {@link CreditCardAccount}.
     */
    protected ?int $accountType = null;

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

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getProductName(): ?string
    {
        return $this->productName;
    }

    public function setProductName(?string $productName): static
    {
        $this->productName = $productName;

        return $this;
    }

    public function getCurrency(): ?string
    {
        return $this->currency;
    }

    public function setCurrency(?string $currency): static
    {
        $this->currency = $currency;

        return $this;
    }

    public function getAccountType(): ?int
    {
        return $this->accountType;
    }

    public function setAccountType(?int $accountType): static
    {
        $this->accountType = $accountType;

        return $this;
    }
}

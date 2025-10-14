<?php
/** @noinspection PhpUnused */

namespace Fhp\Model\StatementOfAccount;

class Transaction
{
    public const CD_CREDIT = 'credit';
    public const CD_DEBIT = 'debit';

    protected ?\DateTime $bookingDate = null;
    protected ?\DateTime $valutaDate = null;
    protected float $amount;
    protected string $creditDebit;
    protected bool $isStorno;
    protected string $bookingCode;
    protected string $bookingText;
    protected string $description1;
    protected string $description2;

    /**
     * Array keys are identifiers like "SVWZ" for the main description.
     * @var string[]
     */
    protected array $structuredDescription;

    protected string $bankCode;
    protected string $accountNumber;
    protected string $name;
    protected bool $booked;
    protected int $pn;
    protected int $textKeyAddition;

    /**
     * Get booking date.
     *
     * @deprecated Use getBookingDate() instead
     * @codeCoverageIgnore
     */
    public function getDate(): ?\DateTime
    {
        return $this->getBookingDate();
    }

    /**
     * Get booking date
     */
    public function getBookingDate(): ?\DateTime
    {
        return $this->bookingDate;
    }

    /**
     * Get date
     */
    public function getValutaDate(): ?\DateTime
    {
        return $this->valutaDate;
    }

    /**
     * Set booking date
     *
     * @return $this
     */
    public function setBookingDate(?\DateTime $date = null): static
    {
        $this->bookingDate = $date;
        return $this;
    }

    /**
     * Set valuta date
     *
     * @return $this
     */
    public function setValutaDate(?\DateTime $date = null): static
    {
        $this->valutaDate = $date;
        return $this;
    }

    /**
     * Get amount
     */
    public function getAmount(): float
    {
        return $this->amount;
    }

    /**
     * Set booked status
     *
     * @return $this
     */
    public function setBooked(bool $booked): static
    {
        $this->booked = $booked;
        return $this;
    }

    /**
     * Set amount
     *
     * @return $this
     */
    public function setAmount(float $amount): static
    {
        $this->amount = $amount;
        return $this;
    }

    /**
     * Get creditDebit
     */
    public function getCreditDebit(): string
    {
        return $this->creditDebit;
    }

    /**
     * Set creditDebit
     *
     * @return $this
     */
    public function setCreditDebit(string $creditDebit): static
    {
        $this->creditDebit = $creditDebit;
        return $this;
    }

    /**
     * Get isStorno
     */
    public function isStorno(): bool
    {
        return $this->isStorno;
    }

    /**
     * Set isStorno
     *
     * @return $this
     */
    public function setIsStorno(bool $isStorno): static
    {
        $this->isStorno = $isStorno;
        return $this;
    }

    /**
     * Get bookingCode
     */
    public function getBookingCode(): string
    {
        return $this->bookingCode;
    }

    /**
     * Set bookingCode
     *
     * @return $this
     */
    public function setBookingCode(string $bookingCode): static
    {
        $this->bookingCode = $bookingCode;
        return $this;
    }

    /**
     * Get bookingText
     */
    public function getBookingText(): string
    {
        return $this->bookingText;
    }

    /**
     * Set bookingText
     *
     * @return $this
     */
    public function setBookingText(string $bookingText): static
    {
        $this->bookingText = $bookingText;
        return $this;
    }

    /**
     * Get description1
     */
    public function getDescription1(): string
    {
        return $this->description1;
    }

    /**
     * Set description1
     *
     * @return $this
     */
    public function setDescription1(string $description1): static
    {
        $this->description1 = $description1;
        return $this;
    }

    /**
     * Get description2
     */
    public function getDescription2(): string
    {
        return $this->description2;
    }

    /**
     * Set description2
     *
     * @return $this
     */
    public function setDescription2(string $description2): static
    {
        $this->description2 = $description2;
        return $this;
    }

    /**
     * Get structuredDescription
     *
     * @return string[]
     */
    public function getStructuredDescription(): array
    {
        return $this->structuredDescription;
    }

    /**
     * Set structuredDescription
     *
     * @param string[] $structuredDescription
     * @return $this
     */
    public function setStructuredDescription(array $structuredDescription): static
    {
        $this->structuredDescription = $structuredDescription;

        return $this;
    }

    /**
     * Get the main description (SVWZ)
     */
    public function getMainDescription(): string
    {
        if (array_key_exists('SVWZ', $this->structuredDescription)) {
            return $this->structuredDescription['SVWZ'];
        } else {
            return '';
        }
    }

    /**
     * Get the end to end id (EREF)
     */
    public function getEndToEndID(): string
    {
        if (array_key_exists('EREF', $this->structuredDescription)) {
            return $this->structuredDescription['EREF'];
        } else {
            return '';
        }
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
    public function setBankCode(string $bankCode): static
    {
        $this->bankCode = $bankCode;
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
    public function setAccountNumber(string $accountNumber): static
    {
        $this->accountNumber = $accountNumber;
        return $this;
    }

    /**
     * Get name
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Get booked status
     */
    public function getBooked(): bool
    {
        return $this->booked;
    }

    /**
     * Set name
     *
     * @return $this
     */
    public function setName(string $name): static
    {
        $this->name = $name;
        return $this;
    }

    /**
     * Get primanota number
     */
    public function getPN(): int
    {
        return $this->pn;
    }

    /**
     * Set primanota number
     *
     * @param int|mixed $nr Will be parsed to an int.
     * @return $this
     */
    public function setPN($nr): static
    {
        $this->pn = intval($nr);
        return $this;
    }

    /**
     * Get text key addition
     */
    public function getTextKeyAddition(): int
    {
        return $this->textKeyAddition;
    }

    /**
     * Set text key addition
     *
     * @param int|mixed $textKeyAddition Will be parsed to an int.
     * @return $this
     */
    public function setTextKeyAddition($textKeyAddition): static
    {
        $this->textKeyAddition = intval($textKeyAddition);
        return $this;
    }
}

<?php

namespace Fhp\Model\StatementOfAccount;

class Transaction
{
    const CD_CREDIT = 'credit';
    const CD_DEBIT = 'debit';

    /**
     * @var \DateTime|null
     */
    protected $bookingDate;

    /**
     * @var \DateTime|null
     */
    protected $valutaDate;

    /**
     * @var float
     */
    protected $amount;

    /**
     * @var string
     */
    protected $creditDebit;

    /**
     * @var string
     */
    protected $bookingCode;

    /**
     * @var string
     */
    protected $bookingText;

    /**
     * @var string
     */
    protected $description1;

    /**
     * @var string
     */
    protected $description2;

    /**
     * Array keys are identifiers like "SVWZ" for the main description.
     * @var string[]
     */
    protected $structuredDescription;

    /**
     * @var string
     */
    protected $bankCode;

    /**
     * @var string
     */
    protected $accountNumber;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var bool
     */
    protected $booked;

    /**
     * @var int
     */
    protected $pn;

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
    public function setBookingDate(\DateTime $date = null)
    {
        $this->bookingDate = $date;

        return $this;
    }

    /**
     * Set valuta date
     *
     * @return $this
     */
    public function setValutaDate(\DateTime $date = null)
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
    public function setBooked(bool $booked)
    {
        $this->booked = $booked;

        return $this;
    }

    /**
     * Set amount
     *
     * @return $this
     */
    public function setAmount(float $amount)
    {
        $this->amount = (float) $amount;

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
    public function setCreditDebit(string $creditDebit)
    {
        $this->creditDebit = $creditDebit;

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
    public function setBookingCode(string $bookingCode)
    {
        $this->bookingCode = (string) $bookingCode;

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
    public function setBookingText(string $bookingText)
    {
        $this->bookingText = (string) $bookingText;

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
    public function setDescription1(string $description1)
    {
        $this->description1 = (string) $description1;

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
    public function setDescription2(string $description2)
    {
        $this->description2 = (string) $description2;

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
     *
     * @return $this
     */
    public function setStructuredDescription(array $structuredDescription)
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
    public function setBankCode(string $bankCode)
    {
        $this->bankCode = (string) $bankCode;

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
    public function setName(string $name)
    {
        $this->name = (string) $name;

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
    public function setPN($nr)
    {
        $this->pn = intval($nr);
        return $this;
    }
}

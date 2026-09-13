<?php
/** @noinspection PhpUnused */

namespace Fhp\Model\CreditCardStatement;

use Fhp\Segment\KKU\Kreditkartenumsatz;

/**
 * A single credit card transaction, as returned by {@link \Fhp\Action\GetCreditCardStatement}.
 *
 * Unlike a regular {@link \Fhp\Model\StatementOfAccount\Transaction} (which stems from MT940), credit
 * card records are flat and carry a distinct set of fields.
 */
class CreditCardTransaction
{
    public const CD_CREDIT = 'credit';
    public const CD_DEBIT = 'debit';

    protected ?string $accountNumber = null;
    protected ?\DateTime $valutaDate = null;
    protected ?\DateTime $bookingDate = null;
    /** Signed amount: negative for debits, positive for credits. */
    protected float $amount = 0.0;
    protected string $creditDebit = self::CD_CREDIT;
    protected string $currency = 'EUR';
    /** The signed amount in the currency the merchant charged, if it differs from {@link $amount}. */
    protected ?float $originalAmount = null;
    protected ?string $originalCurrency = null;
    /** The exchange rate applied, or null if no conversion took place. */
    protected ?float $exchangeRate = null;
    protected string $purpose = '';
    /**
     * The individual Verwendungszweck lines, in order, non-empty only. {@link $purpose} is these
     * joined by spaces. Usually the first line is the merchant name and the second the merchant
     * location followed by the masked card number.
     */
    protected array $purposeLines = [];
    protected ?string $reference = null;
    /** ISO 18245 merchant category code, e.g. 5411 for grocery stores. Null if the booking has no merchant. */
    protected ?string $merchantCategoryCode = null;

    public function getAccountNumber(): ?string
    {
        return $this->accountNumber;
    }

    public function setAccountNumber(?string $accountNumber): static
    {
        $this->accountNumber = $accountNumber;
        return $this;
    }

    public function getValutaDate(): ?\DateTime
    {
        return $this->valutaDate;
    }

    public function setValutaDate(?\DateTime $valutaDate): static
    {
        $this->valutaDate = $valutaDate;
        return $this;
    }

    public function getBookingDate(): ?\DateTime
    {
        return $this->bookingDate;
    }

    public function setBookingDate(?\DateTime $bookingDate): static
    {
        $this->bookingDate = $bookingDate;
        return $this;
    }

    public function getAmount(): float
    {
        return $this->amount;
    }

    public function setAmount(float $amount): static
    {
        $this->amount = $amount;
        return $this;
    }

    public function getCreditDebit(): string
    {
        return $this->creditDebit;
    }

    public function setCreditDebit(string $creditDebit): static
    {
        $this->creditDebit = $creditDebit;
        return $this;
    }

    public function getCurrency(): string
    {
        return $this->currency;
    }

    public function setCurrency(string $currency): static
    {
        $this->currency = $currency;
        return $this;
    }

    public function getPurpose(): string
    {
        return $this->purpose;
    }

    public function setPurpose(string $purpose): static
    {
        $this->purpose = $purpose;
        return $this;
    }

    /**
     * @return string[] The individual Verwendungszweck lines, in order, non-empty only. Usually the
     *     first line is the merchant name and the second the merchant location followed by the masked
     *     card number. {@link getPurpose()} returns these joined by spaces.
     */
    public function getPurposeLines(): array
    {
        return $this->purposeLines;
    }

    /**
     * @param string[] $purposeLines
     */
    public function setPurposeLines(array $purposeLines): static
    {
        $this->purposeLines = $purposeLines;
        return $this;
    }

    /**
     * The merchant name, taken from the first Verwendungszweck line. Prefer this over
     * {@link getPurpose()} when you need a stable counterparty name (e.g. to name an expense account),
     * because the full purpose also contains the location and the masked card number, which vary
     * between bookings of the same merchant.
     *
     * For bookings without a merchant, such as the monthly settlement, this returns whatever
     * descriptive text the bank placed in the first line (e.g. "Ausgleich Kreditkartenabrechnung").
     *
     * @return string|null Null only if the record carries no Verwendungszweck at all.
     */
    public function getMerchant(): ?string
    {
        return $this->purposeLines[0] ?? null;
    }

    public function getReference(): ?string
    {
        return $this->reference;
    }

    public function setReference(?string $reference): static
    {
        $this->reference = $reference;
        return $this;
    }

    /** @return float|null The signed amount the merchant charged, if a currency conversion took place. */
    public function getOriginalAmount(): ?float
    {
        return $this->originalAmount;
    }

    public function setOriginalAmount(?float $originalAmount): static
    {
        $this->originalAmount = $originalAmount;
        return $this;
    }

    public function getOriginalCurrency(): ?string
    {
        return $this->originalCurrency;
    }

    public function setOriginalCurrency(?string $originalCurrency): static
    {
        $this->originalCurrency = $originalCurrency;
        return $this;
    }

    /** @return float|null The exchange rate applied, or null if no conversion took place. */
    public function getExchangeRate(): ?float
    {
        return $this->exchangeRate;
    }

    public function setExchangeRate(?float $exchangeRate): static
    {
        $this->exchangeRate = $exchangeRate;
        return $this;
    }

    /** @return string|null The ISO 18245 merchant category code, e.g. 5411 for grocery stores. */
    public function getMerchantCategoryCode(): ?string
    {
        return $this->merchantCategoryCode;
    }

    public function setMerchantCategoryCode(?string $merchantCategoryCode): static
    {
        $this->merchantCategoryCode = $merchantCategoryCode;
        return $this;
    }

    /**
     * Builds a model transaction from a parsed {@link Kreditkartenumsatz} record.
     */
    public static function fromSegment(Kreditkartenumsatz $umsatz): CreditCardTransaction
    {
        $result = new CreditCardTransaction();
        $result->accountNumber = $umsatz->kontonummer;
        $result->valutaDate = self::parseDate($umsatz->belegdatum);
        $result->bookingDate = self::parseDate($umsatz->buchungsdatum);

        // $betrag is the amount actually billed to the account, in the account's currency.
        $isDebit = self::isDebit($umsatz->sollHabenKennzeichen);
        $result->creditDebit = $isDebit ? self::CD_DEBIT : self::CD_CREDIT;
        $result->amount = ($isDebit ? -1 : 1) * abs($umsatz->betrag->wert);
        $result->currency = $umsatz->betrag->waehrung;

        // Only report the original amount when a conversion actually took place, so that callers can
        // simply check for null instead of comparing amounts.
        if ($umsatz->ursprungsbetrag->waehrung !== $umsatz->betrag->waehrung) {
            $originalIsDebit = self::isDebit($umsatz->ursprungsSollHabenKennzeichen);
            $result->originalAmount = ($originalIsDebit ? -1 : 1) * abs($umsatz->ursprungsbetrag->wert);
            $result->originalCurrency = $umsatz->ursprungsbetrag->waehrung;
            $result->exchangeRate = $umsatz->umrechnungskurs;
        }

        $result->purposeLines = $umsatz->getVerwendungszweckLines();
        $result->purpose = trim(implode(' ', $result->purposeLines));
        $result->reference = $umsatz->referenz;
        $result->merchantCategoryCode = $umsatz->branchenschluessel;
        return $result;
    }

    /** Banks use 'D'/'C' (Debit/Credit); 'S'/'H' (Soll/Haben) is accepted as well. */
    private static function isDebit(string $sollHabenKennzeichen): bool
    {
        return in_array(strtoupper($sollHabenKennzeichen), ['D', 'S'], true);
    }

    private static function parseDate(?string $date): ?\DateTime
    {
        if ($date === null || $date === '') {
            return null;
        }
        $parsed = \DateTime::createFromFormat('Ymd', $date);
        return $parsed === false ? null : $parsed->setTime(0, 0);
    }
}

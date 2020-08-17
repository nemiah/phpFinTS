<?php

namespace Fhp\Segment\Common;

use Fhp\Segment\BaseDeg;

/**
 * Mehrfach verwendetes Element: Saldo (Version 2)
 *
 * @link https://www.hbci-zka.de/dokumente/spezifikation_deutsch/fintsv3/FinTS_3.0_Messages_Geschaeftsvorfaelle_2015-08-07_final_version.pdf
 * Section: B.4
 *
 * Note: Version 2 is compatible with version 1, which essentially just inlined the Btg.
 * @link https://www.hbci-zka.de/dokumente/spezifikation_deutsch/archiv/HBCI_V2.x_FV.zip
 * File: Gesamtdok_HBCI21o.pdf
 * Section: II.5.3.4
 */
class Sdo extends BaseDeg
{
    const CREDIT = 'C';  // "Haben"
    const DEBIT = 'D'; // "Soll"

    /**
     * Allowed values:
     *  "C" = Credit (the signum of $wert is positive)
     *  "D" = Debit (the signum of $wert is negative)
     * @var string
     */
    public $sollHabenKennzeichen;
    /** @var Btg */
    public $betrag;
    /** @var string JJJJMMTT gemäß ISO 8601 */
    public $datum;
    /** @var string|null hhmmss gemäß ISO 8601, local time (no time zone support). */
    public $uhrzeit;

    public function getAmount(): float
    {
        if ($this->sollHabenKennzeichen === self::CREDIT) {
            return $this->betrag->wert;
        } elseif ($this->sollHabenKennzeichen === self::DEBIT) {
            return -1 * $this->betrag->wert;
        } else {
            throw new \InvalidArgumentException("Invalid sollHabenKennzeichen: $this->sollHabenKennzeichen");
        }
    }

    public function getCurrency(): string
    {
        return $this->betrag->waehrung;
    }

    public function getTimestamp(): \DateTime
    {
        return \DateTime::createFromFormat('Ymd His', $this->datum . ' ' . ($this->uhrzeit ?? '000000'));
    }

    public static function create(float $amount, string $currency, \DateTime $timestamp)
    {
        $result = new Sdo();
        $result->sollHabenKennzeichen = $amount < 0 ? self::DEBIT : self::CREDIT;
        $result->betrag = Btg::create($amount, $currency);
        $result->datum = $timestamp->format('Ymd');
        $result->uhrzeit = $timestamp->format('His');
        if ($result->uhrzeit == '000000') {
            $result->uhrzeit = null;
        }
        return $result;
    }
}

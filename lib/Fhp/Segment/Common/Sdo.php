<?php

namespace Fhp\Segment\Common;

use Fhp\Segment\BaseDeg;

/**
 * Mehrfach verwendetes Element: Saldo (Version 2)
 *
 * @link https://www.fints.org/securedl/sdl-eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpYXQiOjE3NTYzMTc2NjMsImV4cCI6MTc1NjQwNzY2MywidXNlciI6MCwiZ3JvdXBzIjpbMCwtMV0sImZpbGUiOiJmaWxlYWRtaW4vc2VjdXJlZC9kb2t1bWVudGUvc3BlemlmaWthdGlvbl9kZXV0c2NoL2ZpbnRzdjMvRmluVFNfMy4wX01lc3NhZ2VzX0dlc2NoYWVmdHN2b3JmYWVsbGVfMjAyMi0wNC0xNV9maW5hbF92ZXJzaW9uLnBkZiIsInBhZ2UiOjEyN30.nQ1tJDZlRp30Fh2ZXZK147v2xOOrEHIrmTu-gjeHHMQ/FinTS_3.0_Messages_Geschaeftsvorfaelle_2022-04-15_final_version.pdf
 * Section: B.4
 *
 * Note: Version 2 is compatible with version 1, which essentially just inlined the Btg.
 * @link https://www.fints.org/securedl/sdl-eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpYXQiOjE3NTYzMjAzNjEsImV4cCI6MTc1NjQxMDM2MSwidXNlciI6MCwiZ3JvdXBzIjpbMCwtMV0sImZpbGUiOiJmaWxlYWRtaW4vc2VjdXJlZC9kb2t1bWVudGUvc3BlemlmaWthdGlvbl9kZXV0c2NoL2FyY2hpdi9IQkNJX1YyLnhfRlYuemlwIiwicGFnZSI6MTI0fQ.oG30ZAXKp18HuWl7YnErp-8QTKG5c_XGVpbxh_B7foE/HBCI_V2.x_FV.zip
 * File: Gesamtdok_HBCI21o.pdf
 * Section: II.5.3.4
 */
class Sdo extends BaseDeg
{
    public const CREDIT = 'C';  // "Haben"
    public const DEBIT = 'D'; // "Soll"

    /**
     * Allowed values:
     *  "C" = Credit (the signum of $wert is positive)
     *  "D" = Debit (the signum of $wert is negative)
     */
    public string $sollHabenKennzeichen;
    public Btg $betrag;
    /** JJJJMMTT gemäß ISO 8601 */
    public string $datum;
    /** hhmmss gemäß ISO 8601, local time (no time zone support). */
    public ?string $uhrzeit = null;

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

    public static function create(float $amount, string $currency, \DateTime $timestamp): Sdo
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

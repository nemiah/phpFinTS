<?php
/** @noinspection PhpUnused */

namespace Fhp\Segment\Common;

use Fhp\Model\SEPAAccount;
use Fhp\Segment\BaseDeg;

/**
 * Data Element Group: Kontoverbindung
 *
 * This is an older version of {@link KtvV3}, used only by some old segments.
 *
 * @link https://www.fints.org/securedl/sdl-eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpYXQiOjE3NTYzMjAzNjEsImV4cCI6MTc1NjQxMDM2MSwidXNlciI6MCwiZ3JvdXBzIjpbMCwtMV0sImZpbGUiOiJmaWxlYWRtaW4vc2VjdXJlZC9kb2t1bWVudGUvc3BlemlmaWthdGlvbl9kZXV0c2NoL2FyY2hpdi9IQkNJX1YyLnhfRlYuemlwIiwicGFnZSI6MTI0fQ.oG30ZAXKp18HuWl7YnErp-8QTKG5c_XGVpbxh_B7foE/HBCI_V2.x_FV.zip
 * File: Gesamtdok_HBCI210.pdf
 * Section: II.5.3.3
 */
class Kto extends BaseDeg implements AccountInfo
{
    public string $kontonummer; // Aka Depotnummer
    public Kik $kik;

    public static function create(string $kontonummer, Kik $kik): Kto
    {
        $result = new Kto();
        $result->kontonummer = $kontonummer;
        $result->kik = $kik;
        return $result;
    }

    public static function fromAccount(SEPAAccount $account): Kto
    {
        return static::create($account->getAccountNumber(), Kik::create($account->getBlz()));
    }

    /** {@inheritdoc} */
    public function getAccountNumber(): string
    {
        return $this->kontonummer;
    }

    /** {@inheritdoc} */
    public function getBankIdentifier(): ?string
    {
        return $this->kik->kreditinstitutscode;
    }
}

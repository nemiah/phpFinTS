<?php
/** @noinspection PhpUnused */

namespace Fhp\Segment\Common;

use Fhp\Model\SEPAAccount;
use Fhp\Segment\BaseDeg;

/**
 * Data Element Group: Kontoverbindung (Version 3)
 *
 * @link https://www.fints.org/securedl/sdl-eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpYXQiOjE3NTYzMTc2NjMsImV4cCI6MTc1NjQwNzY2MywidXNlciI6MCwiZ3JvdXBzIjpbMCwtMV0sImZpbGUiOiJmaWxlYWRtaW4vc2VjdXJlZC9kb2t1bWVudGUvc3BlemlmaWthdGlvbl9kZXV0c2NoL2ZpbnRzdjMvRmluVFNfMy4wX01lc3NhZ2VzX0dlc2NoYWVmdHN2b3JmYWVsbGVfMjAyMi0wNC0xNV9maW5hbF92ZXJzaW9uLnBkZiIsInBhZ2UiOjEyN30.nQ1tJDZlRp30Fh2ZXZK147v2xOOrEHIrmTu-gjeHHMQ/FinTS_3.0_Messages_Geschaeftsvorfaelle_2022-04-15_final_version.pdf
 * Section: B.3.1
 *
 * @link https://www.fints.org/securedl/sdl-eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpYXQiOjE3NTYzMjAzNjEsImV4cCI6MTc1NjQxMDM2MSwidXNlciI6MCwiZ3JvdXBzIjpbMCwtMV0sImZpbGUiOiJmaWxlYWRtaW4vc2VjdXJlZC9kb2t1bWVudGUvc3BlemlmaWthdGlvbl9kZXV0c2NoL2FyY2hpdi9IQkNJX1YyLnhfRlYuemlwIiwicGFnZSI6MTI0fQ.oG30ZAXKp18HuWl7YnErp-8QTKG5c_XGVpbxh_B7foE/HBCI_V2.x_FV.zip
 * File: HBCI22 Final.pdf
 * Section: II.5.3.3
 * Note that this older specification document contains no version number and has the Kik inlined, which is equivalent.
 */
class KtvV3 extends BaseDeg implements AccountInfo
{
    public ?string $kontonummer = null;  // Officially it's mandatory, but in practice it can be missing.
    public ?string $unterkontomerkmal = null;
    public ?Kik $kik = null;  // Officially it's mandatory, but in practice it can be missing.

    public static function create(string $kontonummer, ?string $unterkontomerkmal, Kik $kik): KtvV3
    {
        $result = new KtvV3();
        $result->kontonummer = $kontonummer;
        $result->unterkontomerkmal = $unterkontomerkmal;
        $result->kik = $kik;
        return $result;
    }

    public static function fromAccount(SEPAAccount $account): KtvV3
    {
        return static::create($account->getAccountNumber(), $account->getSubAccount(), Kik::create($account->getBlz()));
    }

    /** {@inheritdoc} */
    public function getAccountNumber(): string
    {
        return $this->kontonummer ?: '';
    }

    /** {@inheritdoc} */
    public function getBankIdentifier(): ?string
    {
        return $this->kik->kreditinstitutscode;
    }
}

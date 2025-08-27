<?php
/** @noinspection PhpUnused */

namespace Fhp\Segment\HIUPD;

use Fhp\Model\SEPAAccount;
use Fhp\Segment\BaseSegment;

/**
 * Segment: Kontoinformation (Version 4)
 * Bezugssegment: HKVVB
 * Sender: Kreditinstitut
 *
 * Note: This is a repeated segment, there is one instance per account.
 *
 * @link https://www.fints.org/securedl/sdl-eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpYXQiOjE3NTYzMjAzNjEsImV4cCI6MTc1NjQxMDM2MSwidXNlciI6MCwiZ3JvdXBzIjpbMCwtMV0sImZpbGUiOiJmaWxlYWRtaW4vc2VjdXJlZC9kb2t1bWVudGUvc3BlemlmaWthdGlvbl9kZXV0c2NoL2FyY2hpdi9IQkNJX1YyLnhfRlYuemlwIiwicGFnZSI6MTI0fQ.oG30ZAXKp18HuWl7YnErp-8QTKG5c_XGVpbxh_B7foE/HBCI_V2.x_FV.zip
 * File: HBCI22 Final.pdf
 * Section: V.3 "Kontoinformation"
 */
class HIUPDv4 extends BaseSegment implements HIUPD
{
    public \Fhp\Segment\Common\KtvV3 $kontoverbindung;
    public string $kundenId;
    public ?string $kontowaehrung = null;
    public string $name1;
    public ?string $name2 = null;
    public ?string $kontoproduktbezeichnung = null;
    public ?KontolimitV1 $kontolimit = null;
    /** @var ErlaubteGeschaeftsvorfaelleV1[]|null @Max(98) */
    public ?array $erlaubteGeschaeftsvorfaelle = null;

    /** {@inheritdoc} */
    public function matchesAccount(SEPAAccount $account): bool
    {
        return !is_null($this->kontoverbindung->kontonummer)
            && $this->kontoverbindung->kontonummer == $account->getAccountNumber();
    }

    /** {@inheritdoc} */
    public function getErlaubteGeschaeftsvorfaelle(): array
    {
        return $this->erlaubteGeschaeftsvorfaelle ?? [];
    }
}

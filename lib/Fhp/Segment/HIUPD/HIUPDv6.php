<?php
/** @noinspection PhpUnused */

namespace Fhp\Segment\HIUPD;

use Fhp\Model\SEPAAccount;
use Fhp\Segment\BaseSegment;

/**
 * Segment: Kontoinformation (Version 6)
 * Bezugssegment: HKVVB
 * Sender: Kreditinstitut
 *
 * Note: This is a repeated segment, there is one instance per account.
 *
 * @link https://www.fints.org/securedl/sdl-eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpYXQiOjE3NTYzMTc2NjMsImV4cCI6MTc1NjQwNzY2MywidXNlciI6MCwiZ3JvdXBzIjpbMCwtMV0sImZpbGUiOiJmaWxlYWRtaW4vc2VjdXJlZC9kb2t1bWVudGUvc3BlemlmaWthdGlvbl9kZXV0c2NoL2ZpbnRzdjMvRmluVFNfMy4wX0Zvcm1hbHNfMjAxNy0xMC0wNl9maW5hbF92ZXJzaW9uLnBkZiIsInBhZ2UiOjEyN30.dJGVOO7AaB3sDnr8_UJ2q_GnJniSajEC2g2NCyTIqZc/FinTS_3.0_Formals_2017-10-06_final_version.pdf
 * Section: E.3 "Kontoinformation"
 */
class HIUPDv6 extends BaseSegment implements HIUPD
{
    // Note: Specification wants version 2, but only specifies version 3.
    public ?\Fhp\Segment\Common\KtvV3 $kontoverbindung = null;
    /** Max length: 34 */
    public ?string $iban = null;
    public string $kundenId;
    /**
     * 1 – 9: Kontokorrent-/Girokonto
     * 10 – 19: Sparkonto
     * 20 – 29: Festgeldkonto (Termineinlagen)
     * 30 – 39: Wertpapierdepot
     * 40 – 49: Kredit-/Darlehenskonto
     * 50 – 59: Kreditkartenkonto
     * 60 – 69: Fonds-Depot bei einer Kapitalanlagegesellschaft
     * 70 – 79: Bausparvertrag
     * 80 – 89: Versicherungsvertrag
     * 90 – 99: Sonstige (nicht zuordenbar)
     */
    public ?int $kontoart = null;
    public ?string $kontowaehrung = null;
    public string $name1;
    public ?string $name2 = null;
    public ?string $kontoproduktbezeichnung = null;
    public ?KontolimitV2 $kontolimit = null;
    /** @var ErlaubteGeschaeftsvorfaelleV2[]|null @Max(999) */
    public ?array $erlaubteGeschaeftsvorfaelle = null;
    /**
     * JSON-encoded extra information.
     * @link https://www.fints.org/securedl/sdl-eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpYXQiOjE3NTYzMTc2NjMsImV4cCI6MTc1NjQwNzY2MywidXNlciI6MCwiZ3JvdXBzIjpbMCwtMV0sImZpbGUiOiJmaWxlYWRtaW4vc2VjdXJlZC9kb2t1bWVudGUvc3BlemlmaWthdGlvbl9kZXV0c2NoL2ZpbnRzdjMvRmluVFNfMy4wX0Zvcm1hbHNfMjAxNy0xMC0wNl9maW5hbF92ZXJzaW9uLnBkZiIsInBhZ2UiOjEyN30.dJGVOO7AaB3sDnr8_UJ2q_GnJniSajEC2g2NCyTIqZc/FinTS_3.0_Formals_2017-10-06_final_version.pdf
     * Section: E.3.1 "Aufbau der UPD-Erweiterung, kontobezogen"
     * Max length: 2048
     */
    public ?string $erweiterungKontobezogen = null;

    /** {@inheritdoc} */
    public function matchesAccount(SEPAAccount $account): bool
    {
        return !is_null($this->iban) && $this->iban == $account->getIban();
    }

    /** {@inheritdoc} */
    public function getErlaubteGeschaeftsvorfaelle(): array
    {
        return $this->erlaubteGeschaeftsvorfaelle ?? [];
    }
}

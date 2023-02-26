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
 * @link https://www.hbci-zka.de/dokumente/spezifikation_deutsch/archiv/HBCI_V2.x_FV.zip
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

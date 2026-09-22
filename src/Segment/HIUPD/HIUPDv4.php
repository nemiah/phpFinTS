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
    use AccountHolderNameTrait; // For HIUPD.

    public \Fhp\Segment\Common\KtvV3 $kontoverbindung;
    public string $kundenId;
    public ?string $kontowaehrung = null;
    public string $name1;
    public ?string $name2 = null;
    public ?string $kontoproduktbezeichnung = null;
    public ?KontolimitV1 $kontolimit = null;
    /** @var ErlaubteGeschaeftsvorfaelleV1[]|null @Max(98) */
    public ?array $erlaubteGeschaeftsvorfaelle = null;

    public function matchesAccount(SEPAAccount $account): bool
    {
        return !is_null($this->kontoverbindung->kontonummer)
            && $this->kontoverbindung->kontonummer == $account->getAccountNumber();
    }

    public function getErlaubteGeschaeftsvorfaelle(): array
    {
        return $this->erlaubteGeschaeftsvorfaelle ?? [];
    }

    public function getKontoverbindung(): ?\Fhp\Segment\Common\KtvV3
    {
        return $this->kontoverbindung;
    }

    /** This segment version does not carry the account type yet. */
    public function getKontoart(): ?int
    {
        return null;
    }

    public function getName1(): ?string
    {
        return $this->name1;
    }

    public function getName2(): ?string
    {
        return $this->name2;
    }

    public function getKontoproduktbezeichnung(): ?string
    {
        return $this->kontoproduktbezeichnung;
    }

    public function getKontowaehrung(): ?string
    {
        return $this->kontowaehrung;
    }
}

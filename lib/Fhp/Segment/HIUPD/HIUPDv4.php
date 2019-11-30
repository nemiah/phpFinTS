<?php /** @noinspection PhpUnused */

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
    /** @var \Fhp\Segment\Common\KtvV3 */
    public $kontoverbindung;
    /** @var string */
    public $kundenId;
    /** @var string|null */
    public $kontowaehrung;
    /** @var string */
    public $name1;
    /** @var string|null */
    public $name2;
    /** @var string|null */
    public $kontoproduktbezeichnung;
    /** @var KontolimitV1|null */
    public $kontolimit;
    /** @var ErlaubteGeschaeftsvorfaelleV1[]|null @Max(98) */
    public $erlaubteGeschaeftsvorfaelle;

    /** {@inheritdoc} */
    public function matchesAccount(SEPAAccount $account)
    {
        return !is_null($this->kontoverbindung) && !is_null($this->kontoverbindung->kontonummer)
            && $this->kontoverbindung->kontonummer == $account->getAccountNumber();
    }

    /** {@inheritdoc} */
    public function getErlaubteGeschaeftsvorfaelle()
    {
        return $this->erlaubteGeschaeftsvorfaelle ?? [];
    }
}

<?php /** @noinspection PhpUnused */

namespace Fhp\Segment\HIUPD;

use Fhp\Segment\BaseSegment;

/**
 * Class HIUPDv4
 * Segment: Kontoinformation (Version 4)
 * Bezugssegment: HKVVB
 * Sender: Kreditinstitut
 *
 * @link https://www.hbci-zka.de/dokumente/spezifikation_deutsch/archiv/HBCI_V2.x_FV.zip
 * File: HBCI22 Final.pdf
 * Section: V.3 "Kontoinformation"
 *
 * @package Fhp\Segment\HIUPD
 */
class HIUPDv4 extends BaseSegment
{
    /** @var KtvV3 */
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
    /** @var ErlaubteGeschaeftsvorfaelleV1[] @Max(98) */
    public $erlaubteGeschaeftsvorfaelle;
}

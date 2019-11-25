<?php /** @noinspection PhpUnused */

namespace Fhp\Segment\HIUPD;

use Fhp\Segment\BaseDeg;

/**
 * @link https://www.hbci-zka.de/dokumente/spezifikation_deutsch/archiv/HBCI_V2.x_FV.zip
 * File: HBCI22 Final.pdf
 * Section: V.3 "Kontoinformation" > Nr. 9
 */
class ErlaubteGeschaeftsvorfaelleV1 extends BaseDeg
{
    /** @var string References a segment type name (Segmentkennung) */
    public $geschaeftsvorfall;
    /** @var int Allowed values: 0, 1, 2, 3 */
    public $anzahlBenoetigterSignaturen;
    /** @var string|null Allowed values: E, T, W, M, Z */
    public $limitart;
    /** @var \Fhp\Segment\Common\Btg|null */
    public $limitbetrag;
    /** @var int|null If present, must be greater than 0 */
    public $limitTage;
}

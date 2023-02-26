<?php
/** @noinspection PhpUnused */

namespace Fhp\Segment\HIUPD;

use Fhp\Segment\BaseDeg;

/**
 * @link https://www.hbci-zka.de/dokumente/spezifikation_deutsch/archiv/HBCI_V2.x_FV.zip
 * File: HBCI22 Final.pdf
 * Section: V.3 "Kontoinformation" > Nr. 9
 */
class ErlaubteGeschaeftsvorfaelleV1 extends BaseDeg implements ErlaubteGeschaeftsvorfaelle
{
    /** References a segment type name (Segmentkennung) */
    public string $geschaeftsvorfall;
    /** Allowed values: 0, 1, 2, 3 */
    public int $anzahlBenoetigterSignaturen;
    /** Allowed values: E, T, W, M, Z */
    public ?string $limitart = null;
    public ?\Fhp\Segment\Common\Btg $limitbetrag = null;
    /** If present, must be greater than 0 */
    public ?int $limitTage = null;

    /** {@inheritdoc} */
    public function getGeschaeftsvorfall(): string
    {
        return $this->geschaeftsvorfall;
    }
}

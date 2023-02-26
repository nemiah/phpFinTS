<?php
/** @noinspection PhpUnused */

namespace Fhp\Segment\HIUPD;

use Fhp\Segment\BaseDeg;

/**
 * @link https://www.hbci-zka.de/dokumente/spezifikation_deutsch/fintsv3/FinTS_3.0_Formals_2017-10-06_final_version.pdf
 * Section: F (letter E)
 */
class ErlaubteGeschaeftsvorfaelleV2 extends BaseDeg implements ErlaubteGeschaeftsvorfaelle
{
    /** References a segment type name (Segmentkennung) */
    public string $geschaeftsvorfall;
    /** Allowed values: 0, 1, 2, 3 */
    public int $anzahlBenoetigterSignaturen;
    /** Allowed values: E, T, W, M, Z */
    public ?string $limitart = null;
    /** Not allowed for limitart==Z. */
    public ?\Fhp\Segment\Common\Btg $limitbetrag = null;
    /** Only allowed for limitart==Z, must be greater than zero. */
    public ?int $limitTage = null;

    /** {@inheritdoc} */
    public function getGeschaeftsvorfall(): string
    {
        return $this->geschaeftsvorfall;
    }
}

<?php /** @noinspection PhpUnused */

namespace Fhp\Segment\HIUPD;

use Fhp\Segment\BaseDeg;

/**
 * @link https://www.hbci-zka.de/dokumente/spezifikation_deutsch/fintsv3/FinTS_3.0_Formals_2017-10-06_final_version.pdf
 * Section: F (letter E)
 */
class ErlaubteGeschaeftsvorfaelleV2 extends BaseDeg implements ErlaubteGeschaeftsvorfaelle
{
    /** @var string References a segment type name (Segmentkennung) */
    public $geschaeftsvorfall;
    /** @var int Allowed values: 0, 1, 2, 3 */
    public $anzahlBenoetigterSignaturen;
    /** @var string|null Allowed values: E, T, W, M, Z */
    public $limitart;
    /** @var \Fhp\Segment\Common\Btg|null Not allowed for limitart==Z. */
    public $limitbetrag;
    /** @var int|null Only allowed for limitart==Z, must be greater than zero. */
    public $limitTage;

    /** {@inheritdoc} */
    public function getGeschaeftsvorfall()
    {
        return $this->geschaeftsvorfall;
    }
}

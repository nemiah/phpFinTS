<?php /** @noinspection PhpUnused */

namespace Fhp\Segment\HIBPA;

use Fhp\Segment\BaseDeg;

/**
 * Data Element Group: Unterstützte HBCI-Versionen (Version 2)
 *
 * @link https://www.hbci-zka.de/dokumente/spezifikation_deutsch/fintsv3/FinTS_3.0_Formals_2017-10-06_final_version.pdf
 * Section: F (letter U)
 */
class UnterstuetzteHbciVersionenV2 extends BaseDeg
{
    /** @var int[] @Max(9) */
    public $unterstuetzteHbciVersion;
}

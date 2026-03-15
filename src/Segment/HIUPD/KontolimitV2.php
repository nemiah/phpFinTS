<?php
/** @noinspection PhpUnused */

namespace Fhp\Segment\HIUPD;

use Fhp\Segment\BaseDeg;

/**
 * Data Element Group: Kontolimit (Version 2)
 *
 * @link https://www.hbci-zka.de/dokumente/spezifikation_deutsch/fintsv3/FinTS_3.0_Formals_2017-10-06_final_version.pdf
 * Section: F (letter K)
 */
class KontolimitV2 extends BaseDeg
{
    /** Allowed values: E, T, W, M, Z */
    public string $limitart;
    /** Not allowed for limitart==Z. */
    public ?\Fhp\Segment\Common\Btg $limitbetrag = null;
    /** Only allowed for limitart==Z, must be greater than zero. */
    public ?int $limitTage = null;
}

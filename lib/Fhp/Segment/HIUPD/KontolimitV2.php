<?php

/** @noinspection PhpUnused */

namespace Fhp\Segment\HIUPD;

use Fhp\Segment\BaseDeg;

/**
 * Data Element Group: Kontolimit (Version 2).
 *
 * @see https://www.hbci-zka.de/dokumente/spezifikation_deutsch/fintsv3/FinTS_3.0_Formals_2017-10-06_final_version.pdf
 * Section: F (letter K)
 */
class KontolimitV2 extends BaseDeg
{
    /** @var string Allowed values: E, T, W, M, Z */
    public $limitart;
    /** @var \Fhp\Segment\Common\Btg|null Not allowed for limitart==Z. */
    public $limitbetrag;
    /** @var int|null Only allowed for limitart==Z, must be greater than zero. */
    public $limitTage;
}

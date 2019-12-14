<?php

/** @noinspection PhpUnused */

namespace Fhp\Segment\HKSYN;

use Fhp\Segment\BaseSegment;

/**
 * Segment: Synchronisierung (Version 3)
 *
 * @link https://www.hbci-zka.de/dokumente/spezifikation_deutsch/fintsv3/FinTS_3.0_Formals_2017-10-06_final_version.pdf
 * Section: C.8.1.2
 */
class HKSYNv3 extends BaseSegment
{
    /**
     * 0: Neue Kundensystem-ID zurückmelden
     * 1: Letzte verarbeitete Nachrichtennummer zurückmelden
     * 2: Signatur-ID zurückmelden
     * @var int integer
     */
    public $synchronisierungsmodus = 0; // The only mode we need in practice.
}

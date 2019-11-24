<?php

/** @noinspection PhpUnused */

namespace Fhp\Segment\HIBPA;

use Fhp\Segment\BaseDeg;

/**
 * Data Element Group: Unterstützte Sprachen (Version 2).
 *
 * @see https://www.hbci-zka.de/dokumente/spezifikation_deutsch/fintsv3/FinTS_3.0_Formals_2017-10-06_final_version.pdf
 * Section: F (letter U)
 */
class UnterstuetzteSprachenV2 extends BaseDeg
{
    /**
     * 0: Standard
     * 1: Deutsch, Code ‚de’ (German), Subset Deutsch, Codeset 1 (Latin 1)
     * 2: Englisch, Code ‚en’ (English), Subset Englisch, Codeset 1 (Latin 1)
     * 3: Französisch, Code ‚fr’ (French), Subset Französisch, Codeset 1 (Latin 1).
     *
     * @var int[] @Max(9)
     */
    public $unterstuetzteSprache;
}

<?php

namespace Fhp\Segment\Common;

use Fhp\Segment\BaseDeg;

/**
 * Mehrfach verwendetes Element: Kursqualität (Version 2)
 *
 * @link https://www.hbci-zka.de/dokumente/spezifikation_deutsch/fintsv3/FinTS_3.0_Messages_Geschaeftsvorfaelle_2015-08-07_final_version.pdf
 * Section: D
 */
class Kursqualitaet extends BaseDeg
{
    public const DELAYED = 1;  // delayed-Kurs
    public const REALTIME = 2; // Echtzeit-Kurs

    /** @var int */
    public $kursqualitaet;
}

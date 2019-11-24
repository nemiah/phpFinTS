<?php

/** @noinspection PhpUnused */

namespace Fhp\Segment\TAB;

use Fhp\Segment\BaseSegment;

/**
 * Segment: TAN-Generator/Liste anzeigen Bestand (Version 4).
 *
 * @see https://www.hbci-zka.de/dokumente/spezifikation_deutsch/fintsv3/FinTS_3.0_Security_Sicherheitsverfahren_PINTAN_2018-02-23_final_version.pdf
 * Section: C.3.1.1 a)
 */
class HKTABv4 extends BaseSegment
{
    /**
     * 0: Alle
     * 1: Aktiv
     * 2: Verfügbar.
     *
     * @var int
     */
    public $tanMediumArt = 0;
    /**
     * A: Alle Medien
     * L: Liste
     * * G: TAN-Generator
     * M: Mobiltelefon mit mobileTAN
     * S: Secoder.
     *
     * @var string
     */
    public $tanMediumKlasse = 'A';
}

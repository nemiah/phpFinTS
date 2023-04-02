<?php
/** @noinspection PhpUnused */

namespace Fhp\Segment\TAB;

use Fhp\Segment\BaseSegment;

/**
 * Segment: TAN-Generator/Liste anzeigen Bestand (Version 5)
 *
 * @link https://www.hbci-zka.de/dokumente/spezifikation_deutsch/fintsv3/FinTS_3.0_Security_Sicherheitsverfahren_PINTAN_2018-02-23_final_version.pdf
 * Section: C.3.1.1 a)
 */
class HKTABv5 extends BaseSegment
{
    /**
     * 0: Alle
     * 1: Aktiv
     * 2: Verfügbar
     */
    public int $tanMediumArt = 0;
    /**
     * A: Alle Medien
     * L: Liste
     * * G: TAN-Generator
     * M: Mobiltelefon mit mobileTAN
     * S: Secoder
     * B: Bilateral vereinbart
     */
    public string $tanMediumKlasse = 'A';
}

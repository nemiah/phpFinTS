<?php

/** @noinspection PhpUnused */

namespace Fhp\Segment\TAB;

use Fhp\Segment\BaseSegment;

/**
 * Segment: TAN-Generator/Liste anzeigen Bestand Rückmeldung (Version 4).
 *
 * @see https://www.hbci-zka.de/dokumente/spezifikation_deutsch/fintsv3/FinTS_3.0_Security_Sicherheitsverfahren_PINTAN_2018-02-23_final_version.pdf
 * Section: C.3.1.1 b)
 */
class HITABv4 extends BaseSegment implements HITAB
{
    /**
     * 0: Kunde kann alle „aktiven“ Medien parallel nutzen
     * 1: Kunde kann genau ein Medium (z. B. ein Mobiltelefon oder einen TAN-Generator) zu einer Zeit nutzen
     * 2: Kunde kann ein Mobiltelefon und einen TAN-Generator parallel nutzen.
     *
     * @var int
     */
    public $tanEinsatzoption;
    /** @var TanMediumListeV4[]|null @Max(99) */
    public $tanMediumListe;

    /** {@inheritdoc} */
    public function getTanMediumListe()
    {
        return $this->tanMediumListe;
    }
}

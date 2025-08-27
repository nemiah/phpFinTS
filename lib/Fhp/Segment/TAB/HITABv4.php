<?php
/** @noinspection PhpUnused */

namespace Fhp\Segment\TAB;

use Fhp\Segment\BaseSegment;

/**
 * Segment: TAN-Generator/Liste anzeigen Bestand Rückmeldung (Version 4)
 *
 * @link https://www.fints.org/securedl/sdl-eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpYXQiOjE3NTYzMTc2NjMsImV4cCI6MTc1NjQwNzY2MywidXNlciI6MCwiZ3JvdXBzIjpbMCwtMV0sImZpbGUiOiJmaWxlYWRtaW4vc2VjdXJlZC9kb2t1bWVudGUvc3BlemlmaWthdGlvbl9kZXV0c2NoL2ZpbnRzdjMvRmluVFNfMy4wX1NlY3VyaXR5X1NpY2hlcmhlaXRzdmVyZmFocmVuX1BJTlRBTl8yMDIwLTA3LTEwX2ZpbmFsX3ZlcnNpb24ucGRmIiwicGFnZSI6MTI3fQ.FJHEt1OwhZgDgfpwfO_ikZRn_hX8rbiSuesG2yyEle0/FinTS_3.0_Security_Sicherheitsverfahren_PINTAN_2020-07-10_final_version.pdf
 * Section: C.3.1.1 b)
 */
class HITABv4 extends BaseSegment implements HITAB
{
    /**
     * 0: Kunde kann alle „aktiven“ Medien parallel nutzen
     * 1: Kunde kann genau ein Medium (z. B. ein Mobiltelefon oder einen TAN-Generator) zu einer Zeit nutzen
     * 2: Kunde kann ein Mobiltelefon und einen TAN-Generator parallel nutzen
     */
    public int $tanEinsatzoption;
    /** @var TanMediumListeV4[]|null @Max(99) */
    public ?array $tanMediumListe = null;

    /** {@inheritdoc} */
    public function getTanMediumListe(): array
    {
        return $this->tanMediumListe;
    }
}

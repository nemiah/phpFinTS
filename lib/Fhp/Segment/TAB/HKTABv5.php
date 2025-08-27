<?php
/** @noinspection PhpUnused */

namespace Fhp\Segment\TAB;

use Fhp\Segment\BaseSegment;

/**
 * Segment: TAN-Generator/Liste anzeigen Bestand (Version 5)
 *
 * @link https://www.fints.org/securedl/sdl-eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpYXQiOjE3NTYzMTc2NjMsImV4cCI6MTc1NjQwNzY2MywidXNlciI6MCwiZ3JvdXBzIjpbMCwtMV0sImZpbGUiOiJmaWxlYWRtaW4vc2VjdXJlZC9kb2t1bWVudGUvc3BlemlmaWthdGlvbl9kZXV0c2NoL2ZpbnRzdjMvRmluVFNfMy4wX1NlY3VyaXR5X1NpY2hlcmhlaXRzdmVyZmFocmVuX1BJTlRBTl8yMDIwLTA3LTEwX2ZpbmFsX3ZlcnNpb24ucGRmIiwicGFnZSI6MTI3fQ.FJHEt1OwhZgDgfpwfO_ikZRn_hX8rbiSuesG2yyEle0/FinTS_3.0_Security_Sicherheitsverfahren_PINTAN_2020-07-10_final_version.pdf
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

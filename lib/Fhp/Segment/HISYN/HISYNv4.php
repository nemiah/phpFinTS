<?php

/** @noinspection PhpUnused */

namespace Fhp\Segment\HISYN;

use Fhp\Segment\BaseSegment;

/**
 * Segment: Synchronisierungsantwort (Version 4)
 *
 * @link https://www.hbci-zka.de/dokumente/spezifikation_deutsch/fintsv3/FinTS_3.0_Formals_2017-10-06_final_version.pdf
 * Section: C.8.2.2
 */
class HISYNv4 extends BaseSegment
{
    /** Present if HKSYN.synchronisierungsmodus==0 */
    public ?string $kundensystemId = null;
    /** Present if HKSYN.synchronisierungsmodus==1 */
    public ?int $nachrichtennummer = null;
    /** Present if HKSYN.synchronisierungsmodus==2 */
    public ?int $sicherheitsreferenznummerFuerSignierschluessel = null;
    /** Present if HKSYN.synchronisierungsmodus==2 */
    public ?int $sicherheitsreferenznummerFuerDigitaleSignatur = null;
}

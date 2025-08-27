<?php
/** @noinspection PhpUnused */

namespace Fhp\Segment\HIBPA;

use Fhp\Segment\BaseSegment;

/**
 * Segment: Bankparameter allgemein (Version 3)
 * Contains the main Bankparameterdaten (BPD) data.
 *
 * @link https://www.fints.org/securedl/sdl-eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpYXQiOjE3NTYzMTc2NjMsImV4cCI6MTc1NjQwNzY2MywidXNlciI6MCwiZ3JvdXBzIjpbMCwtMV0sImZpbGUiOiJmaWxlYWRtaW4vc2VjdXJlZC9kb2t1bWVudGUvc3BlemlmaWthdGlvbl9kZXV0c2NoL2ZpbnRzdjMvRmluVFNfMy4wX0Zvcm1hbHNfMjAxNy0xMC0wNl9maW5hbF92ZXJzaW9uLnBkZiIsInBhZ2UiOjEyN30.dJGVOO7AaB3sDnr8_UJ2q_GnJniSajEC2g2NCyTIqZc/FinTS_3.0_Formals_2017-10-06_final_version.pdf
 * Section: D.2
 */
class HIBPAv3 extends BaseSegment
{
    public int $bpdVersion;
    public \Fhp\Segment\Common\Kik $kreditinstitutskennung;
    /** Max length: 60 */
    public string $kreditinstitutsbezeichnung;
    public int $anzahlGeschaeftsvorfallarten;
    public UnterstuetzteSprachenV2 $unterstuetzteSprachen;
    public UnterstuetzteHbciVersionenV2 $unterstuetzteHbciVersionen;
    public ?int $maximaleNachrichtengroesse = null;
    public ?int $minimalerTimeoutWert = null;
    public ?int $maximalerTimeoutWert = null;
}

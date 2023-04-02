<?php
/** @noinspection PhpUnused */

namespace Fhp\Segment\HIBPA;

use Fhp\Segment\BaseSegment;

/**
 * Segment: Bankparameter allgemein (Version 3)
 * Contains the main Bankparameterdaten (BPD) data.
 *
 * @link https://www.hbci-zka.de/dokumente/spezifikation_deutsch/fintsv3/FinTS_3.0_Formals_2017-10-06_final_version.pdf
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

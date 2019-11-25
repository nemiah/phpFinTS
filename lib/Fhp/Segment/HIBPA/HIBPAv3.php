<?php /** @noinspection PhpUnused */

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
    /** @var integer */
    public $bpdVersion;
    /** @var \Fhp\Segment\Common\Kik */
    public $kreditinstitutskennung;
    /** @var string Max length: 60 */
    public $kreditinstitutsbezeichnung;
    /** @var integer */
    public $anzahlGeschaeftsvorfallarten;
    /** @var UnterstuetzteSprachenV2 */
    public $unterstuetzteSprachen;
    /** @var UnterstuetzteHbciVersionenV2 */
    public $unterstuetzteHbciVersionen;
    /** @var integer|null */
    public $maximaleNachrichtengroesse;
    /** @var integer|null */
    public $minimalerTimeoutWert;
    /** @var integer|null */
    public $maximalerTimeoutWert;
}

<?php /** @noinspection PhpUnused */

namespace Fhp\Segment\TAN;

use Fhp\Segment\BaseDeg;

/**
 * Data Element Group: Challenge-Klasse Parameter (Version 1)
 *
 * @link https://www.hbci-zka.de/dokumente/spezifikation_deutsch/fintsv3/FinTS_3.0_Security_Sicherheitsverfahren_PINTAN_2018-02-23_final_version.pdf
 * Section: D (Data-Dictionary under letter P)
 */
class ParameterChallengeKlasse extends BaseDeg
{
    /** @var string|null Max length 999 */
    public $challengeKlasseParameter;
}

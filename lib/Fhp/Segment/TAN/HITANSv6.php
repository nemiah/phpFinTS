<?php /** @noinspection PhpUnused */

namespace Fhp\Segment\TAN;

use Fhp\Segment\BaseGeschaeftsvorfallparameter;

/**
 * Segment: Zwei-Schritt-TAN-Einreichung, Parameter (Version 6)
 * Parameters for: HKTANv6
 * Bezugssegment: HKVVB
 * Sender: Kreditinstitut
 *
 * @link: https://www.hbci-zka.de/dokumente/spezifikation_deutsch/fintsv3/FinTS_3.0_Security_Sicherheitsverfahren_PINTAN_2018-02-23_final_version.pdf
 * Section: B.5.1 c)
 */
class HITANSv6 extends BaseGeschaeftsvorfallparameter
{
    /** @var ParameterZweiSchrittTanEinreichungV6 */
    public $parameterZweiSchrittTanEinreichung;
}

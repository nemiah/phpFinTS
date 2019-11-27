<?php /** @noinspection PhpUnused */

namespace Fhp\Segment\HITANS;

use Fhp\Segment\BaseGeschaeftsvorfallparameter;

/**
 * Segment: Zwei-Schritt-TAN-Einreichung, Parameter (Version 6)
 * Parameters for: HKTANv6
 * Bezugssegment: HKVVB
 * Sender: Kreditinstitut
 *
 * @link: https://www.hbci-zka.de/dokumente/spezifikation_deutsch/fintsv3/FinTS_3.0_Security_Sicherheitsverfahren_PINTAN_2018-02-23_final_version.pdf
 * Section: B.5.1 c)
 *
 * TODO Move HITANS to the TAN namespace. Probably we can drop all HITANS older than v6.
 */
class HITANSv6 extends BaseGeschaeftsvorfallparameter implements HITANS
{
    /** @var ParameterZweiSchrittTanEinreichungV6 */
    public $parameterZweiSchrittTanEinreichung;

    /** @return ParameterZweiSchrittTanEinreichungV6 */
    public function getParameterZweiSchrittTanEinreichung()
    {
        return $this->parameterZweiSchrittTanEinreichung;
    }
}

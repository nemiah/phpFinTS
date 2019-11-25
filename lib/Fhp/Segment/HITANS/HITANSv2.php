<?php /** @noinspection PhpUnused */

namespace Fhp\Segment\HITANS;

use Fhp\Segment\BaseGeschaeftsvorfallparameter;

/**
 * Segment: Zwei-Schritt-TAN-Einreichung, Parameter (Version 2)
 * Parameters for: HKTANv2
 * Bezugssegment: HKVVB
 * Sender: Kreditinstitut
 *
 * @link: https://www.hbci-zka.de/dokumente/spezifikation_deutsch/archiv/FinTS_V3.0_2017-10-06-FV_RM.zip
 */
class HITANSv2 extends BaseGeschaeftsvorfallparameter implements HITANS
{
    /** @var ParameterZweiSchrittTanEinreichungV2 */
    public $parameterZweiSchrittTanEinreichung;

    /** @return ParameterZweiSchrittTanEinreichungV2 */
    public function getParameterZweiSchrittTanEinreichung()
    {
        return $this->parameterZweiSchrittTanEinreichung;
    }
}

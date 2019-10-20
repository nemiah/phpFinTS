<?php /** @noinspection PhpUnused */

namespace Fhp\Segment\HITANS;

use Fhp\Segment\BaseGeschaeftsvorfallparameter;

/**
 * Class HITANSv4
 * Segment: Zwei-Schritt-TAN-Einreichung, Parameter (Version 4)
 * Parameters for: HKTANv4
 * Bezugssegment: HKVVB
 * Sender: Kreditinstitut
 *
 * @link: https://www.hbci-zka.de/dokumente/spezifikation_deutsch/archiv/FinTS_V3.0_2017-10-06-FV_RM.zip
 *
 * @package Fhp\Segment\HITANS
 */
class HITANSv4 extends BaseGeschaeftsvorfallparameter implements HITANS
{
    /** @var ParameterZweiSchrittTanEinreichungV4 */
    public $parameterZweiSchrittTanEinreichung;

    /** @return ParameterZweiSchrittTanEinreichungV4 */
    public function getParameterZweiSchrittTanEinreichung()
    {
        return $this->parameterZweiSchrittTanEinreichung;
    }
}

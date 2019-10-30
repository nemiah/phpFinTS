<?php /** @noinspection PhpUnused */

namespace Fhp\Segment\HITANS;

use Fhp\Segment\BaseGeschaeftsvorfallparameter;

/**
 * Class HITANSv2
 * Segment: Zwei-Schritt-TAN-Einreichung, Parameter (Version 2)
 * Parameters for: HKTANv2
 * Bezugssegment: HKVVB
 * Sender: Kreditinstitut
 *
 * @link: https://www.hbci-zka.de/dokumente/spezifikation_deutsch/archiv/FinTS_V3.0_2017-10-06-FV_RM.zip
 * File: FinTS_3.0_Security_Sicherheitsverfahren_PINTAN_2017-10-06_final_version_rm.pdf
 * Section: E.1.2 c)
 *
 * @package Fhp\Segment\HITANS
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

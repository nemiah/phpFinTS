<?php /** @noinspection PhpUnused */

namespace Fhp\Segment\HITANS;

use Fhp\Segment\BaseGeschaeftsvorfallparameter;

/**
 * Class HITANSv3
 * Segment: Zwei-Schritt-TAN-Einreichung, Parameter (Version 3)
 * Parameters for: HKTANv3
 * Bezugssegment: HKVVB
 * Sender: Kreditinstitut
 *
 * @link: https://www.hbci-zka.de/dokumente/spezifikation_deutsch/archiv/FinTS_V3.0_2017-10-06-FV_RM.zip
 * File: FinTS_3.0_Security_Sicherheitsverfahren_PINTAN_2017-10-06_final_version_rm.pdf
 * Section: E.1.3 c)
 *
 * @package Fhp\Segment\HITANS
 */
class HITANSv3 extends BaseGeschaeftsvorfallparameter implements HITANS
{
    /** @var ParameterZweiSchrittTanEinreichungV3 */
    public $parameterZweiSchrittTanEinreichung;

    /** @return ParameterZweiSchrittTanEinreichungV3 */
    public function getParameterZweiSchrittTanEinreichung()
    {
        return $this->parameterZweiSchrittTanEinreichung;
    }
}

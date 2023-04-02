<?php
/** @noinspection PhpUnused */

namespace Fhp\Segment\TAN;

use Fhp\Segment\BaseGeschaeftsvorfallparameter;

/**
 * Segment: Zwei-Schritt-TAN-Einreichung, Parameter (Version 7)
 * Parameters for: HKTANv7
 * Bezugssegment: HKVVB
 * Sender: Kreditinstitut
 *
 * @link: https://www.hbci-zka.de/dokumente/spezifikation_deutsch/fintsv3/FinTS_3.0_Security_Sicherheitsverfahren_PINTAN_2020-07-10_final_version.pdf
 * Section: B.5.2 c)
 */
class HITANSv7 extends BaseGeschaeftsvorfallparameter implements HITANS
{
    public ParameterZweiSchrittTanEinreichungV7 $parameterZweiSchrittTanEinreichung;

    public function getParameterZweiSchrittTanEinreichung(): ParameterZweiSchrittTanEinreichung
    {
        return $this->parameterZweiSchrittTanEinreichung;
    }
}

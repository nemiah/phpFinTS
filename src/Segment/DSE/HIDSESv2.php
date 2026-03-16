<?php

namespace Fhp\Segment\DSE;

use Fhp\Segment\BaseGeschaeftsvorfallparameter;
use Fhp\Segment\BaseSegment;

/**
 * Segment: Terminierte SEPA-Einzellastschrift einreichen Parameter
 *
 * @link https://www.hbci-zka.de/dokumente/spezifikation_deutsch/fintsv3/FinTS_3.0_Messages_Geschaeftsvorfaelle_2015-08-07_final_version.pdf
 * Section: C.10.2.5.4.2 c)
 */
class HIDSESv2 extends BaseGeschaeftsvorfallparameter implements HIDXES
{
    public ParameterTerminierteSEPAEinzellastschriftEinreichenV2 $parameter;

    public function getParameter(): ParameterTerminierteSEPAEinzellastschriftEinreichenV2
    {
        return $this->parameter;
    }

    public function createRequestSegment(): BaseSegment
    {
        return HKDSEv2::createEmpty();
    }
}

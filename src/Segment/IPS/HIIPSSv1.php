<?php

namespace Fhp\Segment\IPS;

use Fhp\Segment\BaseGeschaeftsvorfallparameter;
use Fhp\Segment\BaseSegment;

/**
 * Segment: SEPA-Instant Payment Status Parameter (Version 1)
 *
 * @link https://www.hbci-zka.de/dokumente/spezifikation_deutsch/fintsv3/FinTS_3.0_Messages_Geschaeftsvorfaelle_2022-04-15_final_version.pdf
 * Section: C.10.2.9.2 c)
 */
class HIIPSSv1 extends BaseGeschaeftsvorfallparameter
{
    public ParameterSEPAInstantPaymentStatusV1 $parameter;

    public function createRequestSegment(): BaseSegment
    {
        return HKIPSv1::createEmpty();
    }
}

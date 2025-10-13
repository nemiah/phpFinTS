<?php

namespace Fhp\Segment\IPZ;

use Fhp\Segment\IPZ\ParameterSEPAInstantPaymentZahlungV2;
use Fhp\Segment\BaseGeschaeftsvorfallparameter;
use Fhp\Segment\BaseSegment;

/**
 * Segment: SEPA-Instant Payment Zahlung (Version 2)
 *
 * @link https://www.hbci-zka.de/dokumente/spezifikation_deutsch/fintsv3/FinTS_3.0_Messages_Geschaeftsvorfaelle_2022-04-15_final_version.pdf
 * Section: C.10.2.9.1.2 c)
 */

class HIIPZSv2 extends BaseGeschaeftsvorfallparameter
{
    public ParameterSEPAInstantPaymentZahlungV2 $parameter;

    public function createRequestSegment(): BaseSegment
    {
        return HKIPZv2::createEmpty();
    }
}

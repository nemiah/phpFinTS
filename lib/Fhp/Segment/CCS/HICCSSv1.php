<?php

namespace Fhp\Segment\CCS;

use Fhp\Segment\BaseGeschaeftsvorfallparameter;
use Fhp\Segment\BaseSegment;

/**
 * Segment: SEPA Einzelüberweisung Parameter (Version 1)
 *
 * @link https://www.hbci-zka.de/dokumente/spezifikation_deutsch/fintsv3/FinTS_3.0_Messages_Geschaeftsvorfaelle_2015-08-07_final_version.pdf
 * Section: C.10.2.1 c)
 */
class HICCSSv1 extends BaseGeschaeftsvorfallparameter
{
    // No parameters.

    public function createRequestSegment(): BaseSegment
    {
        return HKCCSv1::createEmpty();
    }
}

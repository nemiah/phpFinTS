<?php

namespace Fhp\Segment\CCM;

use Fhp\Segment\BaseGeschaeftsvorfallparameter;
use Fhp\Segment\BaseSegment;

/**
 * Segment: SEPA Sammelüberweisung Parameter (Version 1)
 *
 * @link https://www.hbci-zka.de/dokumente/spezifikation_deutsch/fintsv3/FinTS_3.0_Messages_Geschaeftsvorfaelle_2015-08-07_final_version.pdf
 * Section: C.10.3.1.1 c)
 */
class HICCMSv1 extends BaseGeschaeftsvorfallparameter
{
    /** @var ParameterSEPASammelueberweisungV1 */
    public $parameter;

    public function getParameter()
    {
        return $this->parameter;
    }

    public function createRequestSegment(): BaseSegment
    {
        return HKCCMv1::createEmpty();
    }
}

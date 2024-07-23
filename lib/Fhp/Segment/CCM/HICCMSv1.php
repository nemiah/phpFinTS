<?php

namespace Fhp\Segment\CCM;

use Fhp\Segment\BaseGeschaeftsvorfallparameter;

/**
 * Segment: SEPA Einzelüberweisung Parameter (Version 1)
 *
 * @link https://www.hbci-zka.de/dokumente/spezifikation_deutsch/fintsv3/FinTS_3.0_Messages_Geschaeftsvorfaelle_2015-08-07_final_version.pdf
 * Section: C.10.2.1 c)
 */
class HICCMSv1 extends BaseGeschaeftsvorfallparameter
{
    public ParameterSEPASammelueberweisungV1 $parameter;

    public function getParameter()
    {
        return $this->parameter;
    }
}

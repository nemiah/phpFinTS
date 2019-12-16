<?php

namespace Fhp\Segment\DSE;

use Fhp\Segment\BaseGeschaeftsvorfallparameter;

/**
 * Segment: Terminierte SEPA-Einzellastschrift einreichen Parameter
 *
 * @link https://www.hbci-zka.de/dokumente/spezifikation_deutsch/fintsv3/FinTS_3.0_Messages_Geschaeftsvorfaelle_2015-08-07_final_version.pdf
 * Section: C.10.2.5.4.1 c)
 */
class HIDSESv1 extends BaseGeschaeftsvorfallparameter
{
    /** @var ParameterTerminierteSEPAEinzellastschriftEinreichenV1 */
    public $parameter;

    public function getParameter(): ParameterTerminierteSEPAEinzellastschriftEinreichenV1
    {
        return $this->parameter;
    }
}

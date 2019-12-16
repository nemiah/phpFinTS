<?php

namespace Fhp\Segment\DME;

use Fhp\Segment\BaseGeschaeftsvorfallparameter;

/**
 * Segment: Terminierte SEPA-Sammellastschrift einreichen Parameter
 *
 * @link https://www.hbci-zka.de/dokumente/spezifikation_deutsch/fintsv3/FinTS_3.0_Messages_Geschaeftsvorfaelle_2015-08-07_final_version.pdf
 * Section: C.10.3.2.2.1 c)
 */
class HIDMESv1 extends BaseGeschaeftsvorfallparameter
{
    /** @var ParameterTerminierteSEPASammellastschriftEinreichenV1 */
    public $parameter;

    public function getParameter(): ParameterTerminierteSEPASammellastschriftEinreichenV1
    {
        return $this->parameter;
    }
}

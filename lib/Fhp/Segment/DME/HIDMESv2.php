<?php

namespace Fhp\Segment\DME;

use Fhp\Segment\BaseGeschaeftsvorfallparameter;

/**
 * Segment: Terminierte SEPA-Sammellastschrift einreichen Parameter
 *
 * @link https://www.hbci-zka.de/dokumente/spezifikation_deutsch/fintsv3/FinTS_3.0_Messages_Geschaeftsvorfaelle_2015-08-07_final_version.pdf
 * Section: C.10.3.2.2.1 c)
 */
class HIDMESv2 extends BaseGeschaeftsvorfallparameter implements HIDXES
{
    /** @var ParameterTerminierteSEPASammellastschriftEinreichenV2 */
    public $parameter;

    public function getParameter(): ParameterTerminierteSEPASammellastschriftEinreichenV2
    {
        return $this->parameter;
    }
}

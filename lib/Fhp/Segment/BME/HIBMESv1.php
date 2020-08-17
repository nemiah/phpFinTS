<?php

namespace Fhp\Segment\BME;

use Fhp\Segment\BaseGeschaeftsvorfallparameter;
use Fhp\Segment\BaseSegment;
use Fhp\Segment\DSE\HIDXES;
use Fhp\Segment\DSE\SEPADirectDebitMinimalLeadTimeProvider;

/**
 * Segment: Terminierte SEPA-Sammellastschrift einreichen Parameter
 *
 * @link https://www.hbci-zka.de/dokumente/spezifikation_deutsch/fintsv3/FinTS_3.0_Messages_Geschaeftsvorfaelle_2015-08-07_final_version.pdf
 * Section: C.10.3.3.2.1 c)
 */
class HIBMESv1 extends BaseGeschaeftsvorfallparameter implements HIDXES
{
    /** @var ParameterTerminierteSEPAFirmenSammellastschriftEinreichenV1 */
    public $parameter;

    public function getParameter(): SEPADirectDebitMinimalLeadTimeProvider
    {
        return $this->parameter;
    }

    public function createRequestSegment(): BaseSegment
    {
        return HKBMEv1::createEmpty();
    }
}

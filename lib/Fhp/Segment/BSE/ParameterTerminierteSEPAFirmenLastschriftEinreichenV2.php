<?php

namespace Fhp\Segment\BSE;

use Fhp\Segment\BaseDeg;
use Fhp\Segment\DSE\MinimaleVorlaufzeitSEPALastschrift;
use Fhp\Segment\DSE\SEPADirectDebitMinimalLeadTimeProvider;

abstract class ParameterTerminierteSEPAFirmenLastschriftEinreichenV2 extends BaseDeg implements SEPADirectDebitMinimalLeadTimeProvider
{
    /** @var string */
    public $minimaleVorlaufzeitCodiert;

    /** @var string */
    public $maximaleVorlaufzeitCodiert;

    /** @return MinimaleVorlaufzeitSEPALastschrift[] */
    public function getMinimalLeadTime(string $seqType)
    {
        return array_map(function ($value) use ($seqType) {
            return $value[$seqType] ?? null;
        }, MinimaleVorlaufzeitSEPALastschrift::parseCodedB2B($this->minimaleVorlaufzeitCodiert));
    }
}

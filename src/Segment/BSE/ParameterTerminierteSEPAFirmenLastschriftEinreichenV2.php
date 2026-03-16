<?php

namespace Fhp\Segment\BSE;

use Fhp\Segment\BaseDeg;
use Fhp\Segment\DSE\MinimaleVorlaufzeitSEPALastschrift;
use Fhp\Segment\DSE\SEPADirectDebitMinimalLeadTimeProvider;

abstract class ParameterTerminierteSEPAFirmenLastschriftEinreichenV2 extends BaseDeg implements SEPADirectDebitMinimalLeadTimeProvider
{
    public string $minimaleVorlaufzeitCodiert;
    public string $maximaleVorlaufzeitCodiert;

    /** @return MinimaleVorlaufzeitSEPALastschrift[] */
    public function getMinimalLeadTime(string $seqType): array
    {
        return array_map(function ($value) use ($seqType) {
            return $value[$seqType] ?? null;
        }, MinimaleVorlaufzeitSEPALastschrift::parseCodedB2B($this->minimaleVorlaufzeitCodiert));
    }
}

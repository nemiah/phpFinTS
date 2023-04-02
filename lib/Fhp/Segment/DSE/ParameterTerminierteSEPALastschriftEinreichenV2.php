<?php

namespace Fhp\Segment\DSE;

use Fhp\Segment\BaseDeg;

abstract class ParameterTerminierteSEPALastschriftEinreichenV2 extends BaseDeg implements SEPADirectDebitMinimalLeadTimeProvider
{
    public string $minimaleVorlaufzeitCodiert;
    public string $maximaleVorlaufzeitCodiert;

    public function getMinimalLeadTime(string $seqType): array
    {
        return array_map(function ($value) use ($seqType) {
            return $value[$seqType] ?? null;
        }, MinimaleVorlaufzeitSEPALastschrift::parseCoded($this->minimaleVorlaufzeitCodiert));
    }
}

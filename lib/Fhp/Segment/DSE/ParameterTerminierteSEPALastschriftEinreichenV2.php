<?php

namespace Fhp\Segment\DSE;

use Fhp\Segment\BaseDeg;

abstract class ParameterTerminierteSEPALastschriftEinreichenV2 extends BaseDeg implements SEPADirectDebitMinimalLeadTimeProvider
{
    /** @var string */
    public $minimaleVorlaufzeitCodiert;

    /** @var string */
    public $maximaleVorlaufzeitCodiert;

    public function getMinimalLeadTime(string $seqType)
    {
        return array_map(function ($value) use ($seqType) {
            return $value[$seqType] ?? null;
        }, MinimaleVorlaufzeitSEPALastschrift::parseCoded($this->minimaleVorlaufzeitCodiert));
    }
}

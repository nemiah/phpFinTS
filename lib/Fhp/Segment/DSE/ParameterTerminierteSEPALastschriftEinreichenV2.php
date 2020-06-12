<?php

namespace Fhp\Segment\DSE;

use Fhp\Segment\BaseDeg;

abstract class ParameterTerminierteSEPALastschriftEinreichenV2 extends BaseDeg implements SEPADirectDebitMinimalLeadTimeProvider
{
    /** @var string */
    public $minimaleVorlaufzeitCodiert;

    /** @var string */
    public $maximaleVorlaufzeitCodiert;

    public function getMinimalLeadTime(string $seqType, string $coreType = 'CORE'): ?MinimaleVorlaufzeitSEPALastschrift
    {
        $parsed = MinimaleVorlaufzeitSEPALastschrift::parseCoded($this->minimaleVorlaufzeitCodiert);
        return $parsed[$coreType][$seqType] ?? null;
    }
}

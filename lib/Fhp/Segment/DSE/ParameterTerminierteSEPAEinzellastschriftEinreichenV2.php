<?php

namespace Fhp\Segment\DSE;

use Fhp\Segment\BaseDeg;
use Fhp\Segment\DME\MinimaleVorlaufzeitSEPALastschrift;

class ParameterTerminierteSEPAEinzellastschriftEinreichenV2 extends BaseDeg
{
    /** @var string */
    public $minimaleVorlaufzeitCodiert;

    /** @var string */
    public $maximaleVorlaufzeitCodiert;

    /** @var string[]|null @Max(4096) */
    public $zulaessigePurposecodes;

    /** @var string[]|null @Max(256) */
    public $unterstuetzteSEPADatenformate;

    public function getMinimaleVorlaufzeit(string $seqType, string $coreType = 'CORE')
    {
        $parsed = MinimaleVorlaufzeitSEPALastschrift::parseCoded($this->minimaleVorlaufzeitCodiert);
        return $parsed[$coreType][$seqType] ?? null;
    }
}

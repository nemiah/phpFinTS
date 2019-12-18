<?php

namespace Fhp\Segment\DSE;

use Fhp\Segment\BaseDeg;
use Fhp\Segment\DME\MinimaleVorlaufzeitSEPALastschrift;
use Fhp\Segment\DME\SEPADirectDebitMinimalLeadTimeProvider;

class ParameterTerminierteSEPAEinzellastschriftEinreichenV1 extends BaseDeg implements SEPADirectDebitMinimalLeadTimeProvider
{
    /** @var int Must be => 1 */
    public $minimaleVorlaufzeitFNALRCUR;

    /** @var int */
    public $maximaleVorlaufzeitFNALRCUR;

    /** @var int Must be => 1 */
    public $minimaleVorlaufzeitFRSTOOFF;

    /** @var int */
    public $maximaleVorlaufzeitFRSTOOFF;

    public function getMinimalLeadTime(string $seqType, string $coreType = 'CORE'): ?MinimaleVorlaufzeitSEPALastschrift
    {
        $leadTime = in_array($seqType, ['FRST', 'OOFF']) ? $this->minimaleVorlaufzeitFRSTOOFF : $this->minimaleVorlaufzeitFNALRCUR;
        return MinimaleVorlaufzeitSEPALastschrift::create($leadTime, '235959');
    }
}

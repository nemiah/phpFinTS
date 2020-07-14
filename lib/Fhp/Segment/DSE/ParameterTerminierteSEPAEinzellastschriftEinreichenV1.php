<?php

namespace Fhp\Segment\DSE;

use Fhp\Segment\BaseDeg;

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

    public function getMinimalLeadTime(string $seqType): ?MinimaleVorlaufzeitSEPALastschrift
    {
        $leadTime = in_array($seqType, ['FRST', 'OOFF']) ? $this->minimaleVorlaufzeitFRSTOOFF : $this->minimaleVorlaufzeitFNALRCUR;
        return MinimaleVorlaufzeitSEPALastschrift::create($leadTime, '235959');
    }
}

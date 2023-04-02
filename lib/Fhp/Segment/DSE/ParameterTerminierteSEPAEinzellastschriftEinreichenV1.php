<?php

namespace Fhp\Segment\DSE;

use Fhp\Segment\BaseDeg;

class ParameterTerminierteSEPAEinzellastschriftEinreichenV1 extends BaseDeg implements SEPADirectDebitMinimalLeadTimeProvider
{
    /** Must be => 1 */
    public int $minimaleVorlaufzeitFNALRCUR;
    public int $maximaleVorlaufzeitFNALRCUR;
    /** Must be => 1 */
    public int $minimaleVorlaufzeitFRSTOOFF;
    public int $maximaleVorlaufzeitFRSTOOFF;

    public function getMinimalLeadTime(string $seqType): ?MinimaleVorlaufzeitSEPALastschrift
    {
        $leadTime = in_array($seqType, ['FRST', 'OOFF']) ? $this->minimaleVorlaufzeitFRSTOOFF : $this->minimaleVorlaufzeitFNALRCUR;
        return MinimaleVorlaufzeitSEPALastschrift::create($leadTime, '235959');
    }
}

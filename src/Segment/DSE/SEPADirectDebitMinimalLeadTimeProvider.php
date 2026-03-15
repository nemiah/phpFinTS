<?php

namespace Fhp\Segment\DSE;

interface SEPADirectDebitMinimalLeadTimeProvider
{
    /** @return MinimaleVorlaufzeitSEPALastschrift|MinimaleVorlaufzeitSEPALastschrift[]|null*/
    public function getMinimalLeadTime(string $seqType): MinimaleVorlaufzeitSEPALastschrift|array|null;
}

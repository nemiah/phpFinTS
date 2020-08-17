<?php

namespace Fhp\Segment\DSE;

interface SEPADirectDebitMinimalLeadTimeProvider
{
    /** @return MinimaleVorlaufzeitSEPALastschrift|MinimaleVorlaufzeitSEPALastschrift[]*/
    public function getMinimalLeadTime(string $seqType);
}

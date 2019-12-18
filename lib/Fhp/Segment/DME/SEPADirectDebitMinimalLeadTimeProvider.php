<?php

namespace Fhp\Segment\DME;

interface SEPADirectDebitMinimalLeadTimeProvider
{
    public function getMinimalLeadTime(string $seqType, string $coreType = 'CORE'): ?MinimaleVorlaufzeitSEPALastschrift;
}

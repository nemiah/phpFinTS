<?php

namespace Fhp\Segment\DSE;

interface SEPADirectDebitMinimalLeadTimeProvider
{
    public function getMinimalLeadTime(string $seqType, string $coreType = 'CORE'): ?MinimaleVorlaufzeitSEPALastschrift;
}

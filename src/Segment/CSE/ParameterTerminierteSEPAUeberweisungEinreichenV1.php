<?php

namespace Fhp\Segment\CSE;

use Fhp\Segment\BaseDeg;

class ParameterTerminierteSEPAUeberweisungEinreichenV1 extends BaseDeg
{
    /** Must be => 1 */
    public int $minimaleVorlaufzeit;
    public int $maximaleVorlaufzeit;
}

<?php

namespace Fhp\Segment\DSE;

use Fhp\Segment\BaseDeg;

class ParameterTerminierteSEPAEinzellastschriftEinreichenV1 extends BaseDeg
{
    /** @var int Must be => 1 */
    public $minimaleVorlaufzeitFNALRCUR;

    /** @var int */
    public $maximaleVorlaufzeitFNALRCUR;

    /** @var int Must be => 1 */
    public $minimaleVorlaufzeitFRSTOOFF;

    /** @var int */
    public $maximaleVorlaufzeitFRSTOOFF;
}

<?php

namespace Fhp\Segment\VPP;

use Fhp\Segment\BaseSegment;
use Fhp\Syntax\Bin;

/**
 * Segment: Namensabgleich Ausführungsauftrag
 *
 * @see FinTS_3.0_Messages_Geschaeftsvorfaelle_VOP_1.01_2025_06_27_FV.pdf
 * Section: C.10.7.1.2 a)
 */
class HKVPAv1 extends BaseSegment
{
    public Bin $vopId;
}
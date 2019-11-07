<?php

namespace Fhp\Segment\HITANS;

use Fhp\Segment\SegmentInterface;

/**
 * Segment: Zwei-Schritt-TAN-Einreichung, Parameter
 * Bezugssegment: HKVVB
 * Sender: Kreditinstitut
 */
interface HITANS extends SegmentInterface
{
    /** @return ParameterZweiSchrittTanEinreichung */
    public function getParameterZweiSchrittTanEinreichung();
}

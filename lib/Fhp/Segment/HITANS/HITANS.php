<?php

namespace Fhp\Segment\HITANS;

use Fhp\Segment\SegmentInterface;

/**
 * Interface HITANS
 * Segment: Zwei-Schritt-TAN-Einreichung, Parameter
 * Bezugssegment: HKVVB
 * Sender: Kreditinstitut
 *
 * @package Fhp\Segment\HITANS
 */
interface HITANS extends SegmentInterface
{
    /** @return ParameterZweiSchrittTanEinreichung */
    public function getParameterZweiSchrittTanEinreichung();
}

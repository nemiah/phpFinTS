<?php


namespace Fhp\Segment\HITANS;

/**
 * Interface HITANS
 * Segment: Zwei-Schritt-TAN-Einreichung, Parameter
 * Bezugssegment: HKVVB
 * Sender: Kreditinstitut
 *
 * @package Fhp\Segment\HITANS
 */
interface HITANS
{
    /** @return ParameterZweiSchrittTanEinreichung */
    public function getParameterZweiSchrittTanEinreichung();
}

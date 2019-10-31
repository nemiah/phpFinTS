<?php


namespace Fhp\Segment\HITANS;

/**
 * Segment: Zwei-Schritt-TAN-Einreichung, Parameter
 * Bezugssegment: HKVVB
 * Sender: Kreditinstitut
 */
interface HITANS
{
    /** @return ParameterZweiSchrittTanEinreichung */
    public function getParameterZweiSchrittTanEinreichung();
}

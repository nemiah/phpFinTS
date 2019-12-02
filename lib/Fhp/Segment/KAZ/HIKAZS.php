<?php

namespace Fhp\Segment\KAZ;

use Fhp\Segment\SegmentInterface;

/**
 * Segment: Kontoumsätze/Zeitraum Parameter
 */
interface HIKAZS extends SegmentInterface
{
    /** @return ParameterKontoumsaetze */
    public function getParameter();
}

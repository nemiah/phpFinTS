<?php

namespace Fhp\Segment\WPD;

use Fhp\Segment\SegmentInterface;

/**
 * Segment: Kontoumsätze/Zeitraum Parameter
 */
interface HIWPDS extends SegmentInterface
{
    /** @return ParameterDepotaufstellung */
    public function getParameter(): ParameterDepotaufstellung;
}

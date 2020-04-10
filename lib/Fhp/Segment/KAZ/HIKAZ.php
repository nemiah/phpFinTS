<?php

namespace Fhp\Segment\KAZ;

use Fhp\DataTypes\Bin;
use Fhp\Segment\SegmentInterface;

/**
 * Segment: Kontoumsätze rückmelden/Zeitraum
 */
interface HIKAZ extends SegmentInterface
{
    /** @return Bin */
    public function getGebuchteUmsaetze(): Bin;
}

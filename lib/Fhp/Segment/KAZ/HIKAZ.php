<?php

namespace Fhp\Segment\KAZ;

use Fhp\Segment\SegmentInterface;
use Fhp\Syntax\Bin;

/**
 * Segment: Kontoumsätze rückmelden/Zeitraum
 */
interface HIKAZ extends SegmentInterface
{
    /** @return Bin */
    public function getGebuchteUmsaetze(): Bin;
    
    /** @return Bin|null */
    public function getNichtGebuchteUmsaetze(): ?Bin;
}

<?php

namespace Fhp\Segment\KAZ;

use Fhp\Segment\SegmentInterface;
use Fhp\Syntax\Bin;

/**
 * Segment: Kontoumsätze rückmelden/Zeitraum
 */
interface HIKAZ extends SegmentInterface
{
    public function getGebuchteUmsaetze(): Bin;

    public function getNichtGebuchteUmsaetze(): ?Bin;
}

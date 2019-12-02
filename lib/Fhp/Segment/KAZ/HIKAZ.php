<?php

namespace Fhp\Segment\KAZ;

use Fhp\DataTypes\Bin;

/**
 * Segment: Kontoumsätze rückmelden/Zeitraum
 */
interface HIKAZ
{
    /** @return Bin */
    public function getGebuchteUmsaetze();
}

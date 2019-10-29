<?php

namespace Fhp\Segment\TAB;

use Fhp\Segment\SegmentInterface;

/**
 * Interface HITAB
 * Segment: TAN-Generator/Liste anzeigen Bestand Rückmeldung
 *
 * @package Fhp\Segment\TAB
 */
interface HITAB extends SegmentInterface
{
    /** @return TanMediumListe[]|null */
    public function getTanMediumListe();
}

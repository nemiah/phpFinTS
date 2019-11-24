<?php

namespace Fhp\Segment\TAB;

use Fhp\Segment\SegmentInterface;

/**
 * Segment: TAN-Generator/Liste anzeigen Bestand Rückmeldung.
 */
interface HITAB extends SegmentInterface
{
    /** @return TanMediumListe[]|null */
    public function getTanMediumListe();
}

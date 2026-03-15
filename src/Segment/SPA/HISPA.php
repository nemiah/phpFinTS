<?php

namespace Fhp\Segment\SPA;

use Fhp\Segment\SegmentInterface;

/**
 * Segment: SEPA-Kontoverbindung rückmelden
 */
interface HISPA extends SegmentInterface
{
    /** @return \Fhp\Segment\Common\Ktz[] */
    public function getSepaKontoverbindung(): array;
}

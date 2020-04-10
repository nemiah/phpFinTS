<?php

namespace Fhp\Segment\SPA;

use Fhp\Segment\SegmentInterface;

/**
 * Segment: SEPA-Kontoverbindung anfordern, Parameter
 */
interface HISPAS extends SegmentInterface
{
    /** @return ParameterSepaKontoverbindungAnfordern */
    public function getParameter(): ParameterSepaKontoverbindungAnfordern;
}

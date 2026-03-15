<?php

namespace Fhp\Segment\SPA;

use Fhp\Segment\SegmentInterface;

/**
 * Segment: SEPA-Kontoverbindung anfordern, Parameter
 */
interface HISPAS extends SegmentInterface
{
    public function getParameter(): ParameterSepaKontoverbindungAnfordern;
}

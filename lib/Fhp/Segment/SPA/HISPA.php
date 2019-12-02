<?php

namespace Fhp\Segment\SPA;

use Fhp\Segment\Common\Ktz;

/**
 * Segment: SEPA-Kontoverbindung anfordern, Parameter
 */
interface HISPA
{
    /** @return Ktz[] */
    public function getSepaKontoverbindung();
}

<?php

namespace Fhp\Segment\SPA;

use Fhp\Segment\Common\Ktz;

/**
 * Segment: SEPA-Kontoverbindung rückmelden
 */
interface HISPA
{
    /** @return Ktz[] */
    public function getSepaKontoverbindung();
}

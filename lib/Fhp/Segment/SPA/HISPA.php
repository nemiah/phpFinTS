<?php

namespace Fhp\Segment\SPA;

/**
 * Segment: SEPA-Kontoverbindung rückmelden
 */
interface HISPA
{
    /** @return \Fhp\Segment\Common\Ktz[] */
    public function getSepaKontoverbindung(): array;
}

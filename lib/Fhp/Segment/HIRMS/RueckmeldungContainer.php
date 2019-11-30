<?php

namespace Fhp\Segment\HIRMS;

/**
 * Interface for segments that contain multiple {@link Rueckmeldung} instances.
 */
interface RueckmeldungContainer
{
    /**
     * @param int $code The value of Rueckmeldung.rueckmeldungscode to search for.
     * @return Rueckmeldung|null The corresponding Rueckmeldung instance, or null if not found.
     */
    public function findRueckmeldung($code);
}

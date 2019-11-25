<?php


namespace Fhp\Segment\HIRMS;

/**
 * Utility functions for segments that contain an array of {@link Rueckmeldung}.
 */
trait RueckmeldungContainer
{
    /**
     * @param int $code The value of Rueckmeldung.rueckmeldungscode to search for.
     * @return Rueckmeldung|null The corresponding Rueckmeldung instance, or null if not found.
     */
    public function findRueckmeldung($code)
    {
        $matches = array_values(array_filter($this->rueckmeldung, function ($rueckmeldung) use ($code) {
            return $rueckmeldung->rueckmeldungscode === $code;
        }));
        if (count($matches) > 1) {
            throw new \InvalidArgumentException("Unexpectedly multiple matches for Rueckmeldungscode $code");
        }
        return empty($matches) ? null : $matches[0];
    }
}

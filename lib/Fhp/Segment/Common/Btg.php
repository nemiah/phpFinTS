<?php

namespace Fhp\Segment\Common;

use Fhp\Segment\BaseDeg;

/**
 * Mehrfach verwendetes Element: Betrag (Version 1)
 */
class Btg extends BaseDeg
{
    public float $wert;
    public string $waehrung;

    public static function create(float $wert, string $waehrung = 'EUR'): Btg
    {
        $result = new Btg();
        $result->wert = $wert;
        $result->waehrung = $waehrung;
        return $result;
    }
}

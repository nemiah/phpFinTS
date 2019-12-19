<?php

namespace Fhp\Segment\Common;

use Fhp\Segment\BaseDeg;

/**
 * Mehrfach verwendetes Element: Betrag (Version 1)
 */
class Btg extends BaseDeg
{
    /** @var float */
    public $wert;
    /** @var string */
    public $waehrung;

    public static function create(float $wert, string $waehrung = 'EUR')
    {
        $result = new Btg();
        $result->wert = $wert;
        $result->waehrung = $waehrung;
    }
}

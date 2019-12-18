<?php

namespace Fhp\Segment\Common;

use Fhp\Segment\BaseDeg;

/**
 * Mehrfach verwendetes Element: Betrag (Version 1)
 */
class Btg extends BaseDeg
{
    /** @var string */
    public $wert;
    /** @var string */
    public $waehrung;

    public function __construct(float $wert, string $waehrung = 'EUR')
    {
        $this->wert = strtr($wert, '.', ',');
        $this->waehrung = $waehrung;
    }
}

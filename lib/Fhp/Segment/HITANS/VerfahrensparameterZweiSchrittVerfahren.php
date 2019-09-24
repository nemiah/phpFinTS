<?php /** @noinspection PhpUnused */

namespace Fhp\Segment\HITANS;

interface VerfahrensparameterZweiSchrittVerfahren
{
    /** @return integer */
    public function getSicherheitsfunktion();

    /** @return string */
    public function getNameDesZweiSchrittVerfahrens();
}

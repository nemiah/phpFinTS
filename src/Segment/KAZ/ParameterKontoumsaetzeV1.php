<?php
/** @noinspection PhpUnused */

namespace Fhp\Segment\KAZ;

use Fhp\Segment\BaseDeg;

/**
 * Data Element Group: Parameter Kontoumsätze (Version 1)
 *
 * @link https://www.hbci-zka.de/dokumente/spezifikation_deutsch/archiv/HBCI_V2.x_FV.zip
 * File: Gesamtdok_HBCI210.pdf
 * Section: VII.2.1.1 c)
 */
class ParameterKontoumsaetzeV1 extends BaseDeg implements ParameterKontoumsaetze
{
    /** Positive, number of days. */
    public int $speicherzeitraum;
    public bool $eingabeAnzahlEintraegeErlaubt;

    public function getAlleKontenErlaubt(): bool
    {
        return false;
    }
}

<?php /** @noinspection PhpUnused */

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
    /** @var int Positive, number of days. */
    public $speicherzeitraum;
    /** @var bool */
    public $eingabeAnzahlEintraegeErlaubt;

    /** @return bool */
    public function getAlleKontenErlaubt(): bool
    {
        return false;
    }
}

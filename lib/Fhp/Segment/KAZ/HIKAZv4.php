<?php
/** @noinspection PhpUnused */

namespace Fhp\Segment\KAZ;

use Fhp\Segment\BaseSegment;
use Fhp\Syntax\Bin;

/**
 * Segment: Kontoumsätze rückmelden/Zeitraum (Version 4)
 *
 * There will be one segment instance per account.
 *
 * @link https://www.hbci-zka.de/dokumente/spezifikation_deutsch/archiv/HBCI_V2.x_FV.zip
 * File: Gesamtdok_HBCI210.pdf
 * Section: VII.2.1.1 b)
 */
class HIKAZv4 extends BaseSegment implements HIKAZ
{
    /** @var Bin Uses SWIFT format MT940, version SRG 2001 */
    public $gebuchteUmsaetze;
    /** @var Bin|null Uses SWIFT format MT942, version SRG 2001 */
    public $nichtGebuchteUmsaetze;

    public function getGebuchteUmsaetze(): Bin
    {
        return $this->gebuchteUmsaetze;
    }
    
    public function getNichtGebuchteUmsaetze(): ?Bin
    {
        return $this->nichtGebuchteUmsaetze;
    }
}

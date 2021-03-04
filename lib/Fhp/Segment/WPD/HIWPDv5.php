<?php /** @noinspection PhpUnused */

namespace Fhp\Segment\WPD;

use Fhp\Segment\BaseSegment;
use Fhp\Syntax\Bin;

/**
 * Segment: Saldenabfrage (Version 5)
 *
 * There will be one segment instance per account.
 *
 * @link https://www.hbci-zka.de/dokumente/spezifikation_deutsch/archiv/HBCI_V2.x_FV.zip
 * File: HBCI22 Final.pdf
 * Section: VII.2.2 b)
 */
class HIWPDv5 extends BaseSegment implements HIWPD
{
    /** @var Bin Uses SWIFT format MT940, version SRG 2001 */
    public $depotaufstellung;
    
	public function getDepotaufstellung(): Bin
    {
        return $this->depotaufstellung;
    }
	
}

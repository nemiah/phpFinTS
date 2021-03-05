<?php /** @noinspection PhpUnused */

namespace Fhp\Segment\WPD;

use Fhp\Segment\BaseSegment;
use Fhp\Syntax\Bin;

/**
 * Segment: Depotaufstellung Kreditinstitutsrückmledung (Version 5)
 *
 * @link https://www.hbci-zka.de/dokumente/spezifikation_deutsch/fintsv3/FinTS_3.0_Messages_Geschaeftsvorfaelle_2015-08-07_final_version.pdf
 * Section: C.4.3.1b
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

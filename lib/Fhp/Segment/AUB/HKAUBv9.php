<?php

namespace Fhp\Segment\AUB;

use Fhp\Segment\BaseSegment;

/**
 * Segment: Auslandsüberweisung
 *
 * @link https://www.hbci-zka.de/dokumente/spezifikation_deutsch/fintsv3/FinTS_3.0_Messages_Geschaeftsvorfaelle_2015-08-07_final_version.pdf
 * Section: C.5.1.4 a)
 */
class HKAUBv9 extends BaseSegment
{
    public \Fhp\Segment\Common\Kti $kontoverbindungInternational;

    /** Max length: 4 */
    public int $DTAZVHandbuch;

    public \Fhp\Syntax\Bin $DTAZVDatensatz;
}

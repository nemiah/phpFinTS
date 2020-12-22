<?php

namespace Fhp\Segment\AUB;

use Fhp\DataTypes\Bin;
use Fhp\Segment\BaseSegment;

/**
 * Segment: Auslandsüberweisung
 *
 * @link https://www.hbci-zka.de/dokumente/spezifikation_deutsch/fintsv3/FinTS_3.0_Messages_Geschaeftsvorfaelle_2015-08-07_final_version.pdf
 * Section: C.5.1.4 a)
 */
class HKAUBv9 extends BaseSegment
{
    /** @var \Fhp\Segment\Common\Kti */
    public $kontoverbindungInternational;

    /** @var int Max length: 4 */
    public $DTAZVHandbuch;

    /** @var Bin */
    public $DTAZVDatensatz;
}

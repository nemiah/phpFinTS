<?php

namespace Fhp\Segment\WPD;

use Fhp\Segment\SegmentInterface;

/**
 * Segment: Depotaufstellung Parameter
 *
 * @link https://www.hbci-zka.de/dokumente/spezifikation_deutsch/fintsv3/FinTS_3.0_Messages_Geschaeftsvorfaelle_2015-08-07_final_version.pdf
 * Section: C.4.3.1c
 */
interface HIWPDS extends SegmentInterface
{
    /** @return ParameterDepotaufstellung */
    public function getParameter(): ParameterDepotaufstellung;
}

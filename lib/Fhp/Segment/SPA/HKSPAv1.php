<?php
/** @noinspection PhpUnused */

namespace Fhp\Segment\SPA;

use Fhp\Segment\BaseSegment;

/**
 * Segment: SEPA-Kontoverbindung anfordern (Version 1)
 *
 * @link https://www.hbci-zka.de/dokumente/spezifikation_deutsch/fintsv3/FinTS_3.0_Messages_Geschaeftsvorfaelle_2015-08-07_final_version.pdf
 * Section C.10.1.3 a)
 */
class HKSPAv1 extends BaseSegment
{
    /**
     * If left empty, all accounts will be returned.
     * @var \Fhp\Segment\Common\KtvV3[]|null @Max(999)
     */
    public $kontoverbindung;
}

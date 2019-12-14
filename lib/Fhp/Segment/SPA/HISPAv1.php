<?php

namespace Fhp\Segment\SPA;

use Fhp\Segment\BaseSegment;

/**
 * Segment: SEPA-Kontoverbindung rückmelden (Version 1)
 * Bezugssegment: HKSPA
 *
 * @link https://www.hbci-zka.de/dokumente/spezifikation_deutsch/fintsv3/FinTS_3.0_Messages_Geschaeftsvorfaelle_2015-08-07_final_version.pdf
 * Section C.10.1.3 b)
 */
class HISPAv1 extends BaseSegment implements HISPA
{
    /** @var \Fhp\Segment\Common\Ktz[]|null @Max(999) */
    public $sepaKontoverbindung;

    /** @return \Fhp\Segment\Common\Ktz[] */
    public function getSepaKontoverbindung(): array
    {
        return $this->sepaKontoverbindung ?? [];
    }
}

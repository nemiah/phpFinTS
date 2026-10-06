<?php

namespace Fhp\Segment\IPS;

use Fhp\Segment\BaseSegment;
use Fhp\Segment\Common\Kti;

/**
 * Segment: SEPA-Instant Payment Status anfordern (Version 1)
 *
 * @link https://www.hbci-zka.de/dokumente/spezifikation_deutsch/fintsv3/FinTS_3.0_Messages_Geschaeftsvorfaelle_2022-04-15_final_version.pdf
 * Section: C.10.2.9.2 a)
 */
class HKIPSv1 extends BaseSegment
{
    public Kti $kontoverbindungInternational;
    public UnterstuetzteSEPAPainMessages $unterstuetzteSEPAPainMessages;
    /** Max length: 99. As returned by the bank in HIIPZ when the instant payment was submitted. */
    public string $auftragsidentifikation;
}

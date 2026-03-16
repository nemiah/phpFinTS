<?php

namespace Fhp\Segment\IPZ;

/**
 * Segment: SEPA-Instant Payment Zahlung (Version 2)
 *
 * @link https://www.hbci-zka.de/dokumente/spezifikation_deutsch/fintsv3/FinTS_3.0_Messages_Geschaeftsvorfaelle_2022-04-15_final_version.pdf
 * Section: C.10.2.9.1.2 a)
 */
class HKIPZv2 extends HKIPZv1
{
    public bool $umwandlungNachSEPAUeberweisungZulaessig;
}

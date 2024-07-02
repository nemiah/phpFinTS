<?php

namespace Fhp\lib\Fhp\Segment\IPZ;

use Fhp\Segment\BaseDeg;

/**
 * Parameter SEPA-Instant Payment Zahlung (Version 2)
 *
 * @link https://www.hbci-zka.de/dokumente/spezifikation_deutsch/fintsv3/FinTS_3.0_Messages_Geschaeftsvorfaelle_2022-04-15_final_version.pdf
 * Section D
 */
class ParameterSEPAInstantPaymentZahlungV2 extends BaseDeg
{
    public bool $umwandlungNachSEPAUeberweisungZulaessigErlaubt;

    /** Max Length: 4096 */
    public ?string $zulaessigePurposecodes = null;

    /** @var string[]|null @Max(9) Max length: 256 */
    public ?array $unterstuetzteSEPADatenformate = null;
}
<?php

namespace Fhp\Segment\IPZ;

use Fhp\Segment\BaseDeg;
use Fhp\Segment\UnterstuetzteSEPADatenformate;
use Fhp\Segment\UnterstuetzteSEPADatenformateTrait;

/**
 * Parameter SEPA-Instant Payment Zahlung (Version 1)
 *
 * @link https://www.hbci-zka.de/dokumente/spezifikation_deutsch/fintsv3/FinTS_3.0_Messages_Geschaeftsvorfaelle_2022-04-15_final_version.pdf
 * Section D
 */
class ParameterSEPAInstantPaymentZahlungV1 extends BaseDeg implements UnterstuetzteSEPADatenformate
{
    use UnterstuetzteSEPADatenformateTrait;

    /** Max Length: 4096 */
    public ?string $zulaessigePurposecodes = null;

    /** @var string[]|null @Max(9) Max length: 256 */
    public ?array $unterstuetzteSEPADatenformate = null;
}
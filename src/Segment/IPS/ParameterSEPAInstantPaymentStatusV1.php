<?php

namespace Fhp\Segment\IPS;

use Fhp\Segment\BaseDeg;
use Fhp\Segment\UnterstuetzteSEPADatenformate;
use Fhp\Segment\UnterstuetzteSEPADatenformateTrait;

/**
 * Parameter SEPA-Instant Payment Status (Version 1)
 *
 * @link https://www.hbci-zka.de/dokumente/spezifikation_deutsch/fintsv3/FinTS_3.0_Messages_Geschaeftsvorfaelle_2022-04-15_final_version.pdf
 * Section: D ("Parameter SEPA-Instant Payment Status")
 */
class ParameterSEPAInstantPaymentStatusV1 extends BaseDeg implements UnterstuetzteSEPADatenformate
{
    use UnterstuetzteSEPADatenformateTrait;

    /** Minimum time in seconds the client should wait before requesting the status, since earlier there is likely no information available. */
    public int $mindestwartezeit;

    /** @var string[]|null @Max(9) Max length: 256 */
    public ?array $unterstuetzteSEPADatenformate = null;
}

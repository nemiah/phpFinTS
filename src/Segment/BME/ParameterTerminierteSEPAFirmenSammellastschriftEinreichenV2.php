<?php

namespace Fhp\Segment\BME;

use Fhp\Segment\BSE\ParameterTerminierteSEPAFirmenLastschriftEinreichenV2;
use Fhp\Segment\UnterstuetzteSEPADatenformate;
use Fhp\Segment\UnterstuetzteSEPADatenformateTrait;

class ParameterTerminierteSEPAFirmenSammellastschriftEinreichenV2 extends ParameterTerminierteSEPAFirmenLastschriftEinreichenV2 implements UnterstuetzteSEPADatenformate
{
    use UnterstuetzteSEPADatenformateTrait;

    public int $maximaleAnzahlDirectDebitTransferTransactionInformation;
    public bool $summenfeldBenoetigt;
    public bool $einzelbuchungErlaubt;
    /** Max Length: 4096 */
    public ?string $zulaessigePurposecodes = null;
    /** @var string[]|null @Max(9) Max Length: 256 */
    public ?array $unterstuetzteSEPADatenformate = null;
}

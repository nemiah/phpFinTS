<?php

namespace Fhp\Segment\BME;

use Fhp\Segment\BSE\ParameterTerminierteSEPAFirmenLastschriftEinreichenV2;

class ParameterTerminierteSEPAFirmenSammellastschriftEinreichenV2 extends ParameterTerminierteSEPAFirmenLastschriftEinreichenV2
{
    public int $maximaleAnzahlDirectDebitTransferTransactionInformation;
    public bool $summenfeldBenoetigt;
    public bool $einzelbuchungErlaubt;
    /** Max Length: 4096 */
    public ?string $zulaessigePurposecodes = null;
    /** @var string[]|null @Max(9) Max Length: 256 */
    public ?array $unterstuetzteSEPADatenformate = null;
}

<?php

namespace Fhp\Segment\DME;

use Fhp\Segment\DSE\ParameterTerminierteSEPALastschriftEinreichenV2;

class ParameterTerminierteSEPASammellastschriftEinreichenV2 extends ParameterTerminierteSEPALastschriftEinreichenV2
{
    public int $maximaleAnzahlDirectDebitTransferTransactionInformation;
    public bool $summenfeldBenoetigt;
    public bool $einzelbuchungErlaubt;
    /** Max Length: 4096 */
    public ?string $zulaessigePurposecodes = null;
    /** @var string[]|null @Max(9) Max Length: 256 */
    public ?array $unterstuetzteSEPADatenformate = null;
}

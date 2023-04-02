<?php

namespace Fhp\Segment\BSE;

class ParameterTerminierteSEPAFirmenEinzellastschriftEinreichenV2 extends ParameterTerminierteSEPAFirmenLastschriftEinreichenV2
{
    /** Max Length: 4096 */
    public ?string $zulaessigePurposecodes = null;

    /** @var string[]|null @Max(9) Max length: 256 */
    public ?array $unterstuetzteSEPADatenformate = null;
}

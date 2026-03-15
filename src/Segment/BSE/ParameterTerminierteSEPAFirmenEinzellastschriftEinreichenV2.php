<?php

namespace Fhp\Segment\BSE;

use Fhp\Segment\UnterstuetzteSEPADatenformate;
use Fhp\Segment\UnterstuetzteSEPADatenformateTrait;

class ParameterTerminierteSEPAFirmenEinzellastschriftEinreichenV2 extends ParameterTerminierteSEPAFirmenLastschriftEinreichenV2 implements UnterstuetzteSEPADatenformate
{
    use UnterstuetzteSEPADatenformateTrait;

    /** Max Length: 4096 */
    public ?string $zulaessigePurposecodes = null;

    /** @var string[]|null @Max(9) Max length: 256 */
    public ?array $unterstuetzteSEPADatenformate = null;
}

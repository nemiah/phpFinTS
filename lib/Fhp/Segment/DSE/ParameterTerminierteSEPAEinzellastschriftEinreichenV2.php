<?php

namespace Fhp\Segment\DSE;

use Fhp\Segment\UnterstuetzteSEPADatenformate;
use Fhp\Segment\UnterstuetzteSEPADatenformateTrait;

class ParameterTerminierteSEPAEinzellastschriftEinreichenV2 extends ParameterTerminierteSEPALastschriftEinreichenV2 implements UnterstuetzteSEPADatenformate
{
    use UnterstuetzteSEPADatenformateTrait;

    /** Max Length: 4096 */
    public ?string $zulaessigePurposecodes = null;

    /** @var string[]|null @Max(9) Max length: 256 */
    public ?array $unterstuetzteSEPADatenformate = null;
}

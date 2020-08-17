<?php

namespace Fhp\Segment\BME;

use Fhp\Segment\BSE\ParameterTerminierteSEPAFirmenLastschriftEinreichenV2;

class ParameterTerminierteSEPAFirmenSammellastschriftEinreichenV2 extends ParameterTerminierteSEPAFirmenLastschriftEinreichenV2
{
    /** @var int */
    public $maximaleAnzahlDirectDebitTransferTransactionInformation;

    /** @var bool */
    public $summenfeldBenoetigt;

    /** @var bool */
    public $einzelbuchungErlaubt;

    /** @var string|null Max Length: 4096 */
    public $zulaessigePurposecodes;

    /** @var string[]|null @Max(9) Max Length: 256 */
    public $unterstuetzteSEPADatenformate;
}

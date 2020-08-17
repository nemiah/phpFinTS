<?php

namespace Fhp\Segment\BME;

use Fhp\Segment\BSE\ParameterTerminierteSEPAFirmenEinzellastschriftEinreichenV1;

class ParameterTerminierteSEPAFirmenSammellastschriftEinreichenV1 extends ParameterTerminierteSEPAFirmenEinzellastschriftEinreichenV1
{
    /** @var int */
    public $maximaleAnzahlDirectDebitTransferTransactionInformation;

    /** @var bool */
    public $summenfeldBenoetigt;

    /** @var bool */
    public $einzelbuchungErlaubt;
}

<?php

namespace Fhp\Segment\BME;

use Fhp\Segment\BSE\ParameterTerminierteSEPAFirmenEinzellastschriftEinreichenV1;

class ParameterTerminierteSEPAFirmenSammellastschriftEinreichenV1 extends ParameterTerminierteSEPAFirmenEinzellastschriftEinreichenV1
{
    public int $maximaleAnzahlDirectDebitTransferTransactionInformation;
    public bool $summenfeldBenoetigt;
    public bool $einzelbuchungErlaubt;
}

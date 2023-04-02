<?php

namespace Fhp\Segment\DME;

use Fhp\Segment\DSE\ParameterTerminierteSEPAEinzellastschriftEinreichenV1;

class ParameterTerminierteSEPASammellastschriftEinreichenV1 extends ParameterTerminierteSEPAEinzellastschriftEinreichenV1
{
    public int $maximaleAnzahlDirectDebitTransferTransactionInformation;
    public bool $summenfeldBenoetigt;
    public bool $einzelbuchungErlaubt;
}

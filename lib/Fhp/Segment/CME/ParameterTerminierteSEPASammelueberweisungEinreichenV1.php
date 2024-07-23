<?php

namespace Fhp\Segment\CME;

use Fhp\Segment\DSE\ParameterTerminierteSEPAEinzellastschriftEinreichenV1;

class ParameterTerminierteSEPASammelueberweisungEinreichenV1 extends ParameterTerminierteSEPAEinzellastschriftEinreichenV1
{
    public int $maximaleAnzahlDirectDebitTransferTransactionInformation;
    public bool $summenfeldBenoetigt;
    public bool $einzelbuchungErlaubt;
}
